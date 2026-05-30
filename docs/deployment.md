# Deployment Runbook

This repo ships to a production server via four manually-triggered GitHub
Actions workflows. Everything is `workflow_dispatch` — nothing deploys on
push.

**Current target:** `root@84.247.167.97` (Ubuntu 24.04, 6 vCPU / 11 GB RAM /
193 GB disk, IPv4-only). Migrated from `157.245.45.208` on 2026-05-30.

**Uploads:** `wp-content/uploads/` is a **host bind mount** to
`/opt/shobjiwala/shared/uploads` (the bootstrap script creates this dir).
Earlier versions used a Docker named volume `shobjiwala_wp_uploads`; that
was dropped so `uploads.zip`-style seeds and `rsync` backups work against
the host filesystem directly.

## One-time setup

### 1. Generate the CI keypair (on your laptop)

```bash
ssh-keygen -t ed25519 -f ~/.ssh/shobjiwala_ci -C "shobjiwala-ci" -N ""
cat ~/.ssh/shobjiwala_ci.pub   # paste as ci_pubkey input
cat ~/.ssh/shobjiwala_ci       # paste as SSH_PRIVATE_KEY secret
```

### 2. Set GitHub repo secrets

| Secret              | Value                                                            |
|---------------------|------------------------------------------------------------------|
| `SERVER_HOST`       | `84.247.167.97` (today; updated on server migration)            |
| `SSH_USER`          | `deploy`                                                         |
| `SSH_PRIVATE_KEY`   | contents of `~/.ssh/shobjiwala_ci`                               |
| `BOOTSTRAP_SSH_KEY` | private key DigitalOcean lets you SSH in as `root` with          |
| `KNOWN_HOSTS`       | output of `ssh-keyscan -H 84.247.167.97`                        |

### 3. Populate `/opt/shobjiwala/shared/.env` on the server

Before running deploy, the env file must exist on the server. From your laptop
(using the DO root key):

```bash
scp -i ~/.ssh/<your-root-key> .env.prod.example \
    root@84.247.167.97:/opt/shobjiwala/shared/.env
ssh root@84.247.167.97 \
    'chown deploy:deploy /opt/shobjiwala/shared/.env \
     && chmod 600 /opt/shobjiwala/shared/.env'
```

Then edit it on the server, filling in real secrets. See `.env.prod.example`
for the full variable list (DB creds, WP keys, REDIS_PASSWORD, etc.).

### 4. Run the `Server Bootstrap` workflow

GitHub Actions → "Server Bootstrap (one-time)" → Run workflow.

Inputs:
- `laptop_pubkey` — your own `~/.ssh/id_*.pub` (single line)
- `ci_pubkey` — contents of `~/.ssh/shobjiwala_ci.pub`

Wait for green. Then **delete the `BOOTSTRAP_SSH_KEY` secret** from the repo.

> **REVIEWER NOTE (root SSH posture):** Bootstrap sets `PermitRootLogin
> prohibit-password`, keeping key-based root SSH open for your laptop key as
> a break-glass route. This is a deliberate trade-off. If the laptop key is
> ever compromised, the attacker gets a direct root shell. Mitigations: keep
> the laptop key hardware-backed (YubiKey, Secretive) and rotate it if the
> laptop is ever out of your sight.

## Regular deploy

```text
GitHub Actions → "Deploy" → Run workflow → (optional ref) → Run
```

- Composer runs in CI
- Files rsync to `/opt/shobjiwala/releases/<UTC-timestamp>/`
- Symlink `current` flips atomically
- `docker compose up -d`
- Health check on `http://$SERVER_HOST/healthz` (15 attempts × 6s = 90s)
- Auto-rollback on health-check failure

> **REVIEWER NOTE (auto-rollback self-reference):** The auto-rollback step
> invokes `rollback.sh` via `/opt/shobjiwala/current/deploy/rollback.sh`.
> If `release.sh` flipped the symlink before the health check failed,
> `current` now points at the new (broken) release, and rollback.sh is read
> from that release. This is intentional and works because rollback.sh
> only needs to rewrite a symlink and run `docker compose up -d` — its own
> integrity does not depend on the release being "good". If a future release
> ever ships a broken rollback.sh, manual recovery is: SSH in as deploy,
> `ls -1dt /opt/shobjiwala/releases/`, pick a known-good release, manually
> `ln -sfn /opt/shobjiwala/releases/<ts> /opt/shobjiwala/current.new &&
> mv -Tf /opt/shobjiwala/current.new /opt/shobjiwala/current && docker
> compose -f docker-compose.yml -f docker-compose.prod.yml --env-file .env
> up -d`.

## First-time DB push

1. Locally: `make db-dump` (creates `deploy/snapshots/latest.sql.gz`).
2. Verify: `make db-dump-verify` — sanity check decompressed size and CREATE
   TABLE count.
3. Create a snapshot branch and commit the dump:

   ```bash
   git checkout -b snapshot/$(date -u +%Y-%m-%d)
   git add -f deploy/snapshots/latest.sql.gz
   git commit -m "snapshot: db dump $(date -u +%Y-%m-%d)"
   git push -u origin "$(git branch --show-current)"
   ```

4. GitHub Actions → "DB Push (DESTRUCTIVE)" → Run workflow.
   - `confirm` = `OVERWRITE-PROD-DB`
   - `ref` = the snapshot branch
   - `old_url` = `http://localhost:8080` (default)
   - `new_url` = `http://84.247.167.97` (default)

> **REVIEWER NOTE (no checkpoint between dry-run and apply):** The workflow
> runs the dry-run search-replace, then immediately runs the apply step.
> There is NO interactive checkpoint between them. If the dry-run output
> shows unexpected matches, you must manually **cancel the GitHub Actions
> run** before the "Apply search-replace for real" step starts. The window
> between dry-run output appearing in the log and apply starting is roughly
> the duration of one SSH command — be ready to click Cancel.

## Rollback

```text
GitHub Actions → "Rollback" → Run workflow → (optional release) → Run
```

- Empty `release` → flips to second-newest release directory.
- Specific release → use the timestamp directory name (e.g.
  `2026-05-22T14-30-00Z`).

The workflow logs the most recent 10 release directories at the top of the
run so you can copy/paste a target.

## DB restore from `db-backup` (manual, when DB rollback is needed)

`docker-compose.prod.yml` runs `db-backup` hourly into a docker volume.
Rollback workflows only restore code, not the database. To restore DB:

```bash
ssh deploy@$SERVER_HOST
cd /opt/shobjiwala/current
docker compose -f docker-compose.yml -f docker-compose.prod.yml \
    --env-file .env exec db-backup ls /backups        # list dumps
docker compose -f docker-compose.yml -f docker-compose.prod.yml \
    --env-file .env exec db-backup sh -c \
    'gunzip -c /backups/<dump>.sql.gz | mysql -h db -uroot -p"$DB_PASSWORD" "$DB_NAME"'
```

(Replace `<dump>` with the chosen filename from the `ls` step.)

## Server migration (new IP or new droplet)

1. Provision new Ubuntu 24.04 droplet.
2. Update GitHub secrets:
   - `SERVER_HOST` → new IP
   - `BOOTSTRAP_SSH_KEY` → new DO root key (delete after step 4)
   - `KNOWN_HOSTS` → output of `ssh-keyscan -H <new-ip>`
3. Run `Server Bootstrap` workflow with the same `laptop_pubkey` /
   `ci_pubkey` you used originally.
4. Delete `BOOTSTRAP_SSH_KEY` secret.
5. SCP a fresh `.env` into `/opt/shobjiwala/shared/.env` on the new
   server (see step 3 of "One-time setup").
6. Run `Deploy` workflow.
7. Run `DB Push` workflow with `new_url` set to the new IP (or domain).
8. Once verified, flip DNS / decommission old droplet.

## Break-glass SSH

Hardening keeps `PermitRootLogin prohibit-password` — root can still log in
with the laptop pubkey you supplied during bootstrap. Use it if the CI
`deploy` key is lost or the GH workflows are broken:

```bash
ssh -i ~/.ssh/<your-root-key> root@$SERVER_HOST
```

From there you can `su - deploy` to operate as the deploy user, or manually
restart docker compose, etc.

## When TLS / a domain arrives

Out of scope for this runbook — see the design spec at
`docs/superpowers/specs/2026-05-22-github-actions-deploy-design.md` § 9
"Open follow-ups" for the Let's Encrypt path.
