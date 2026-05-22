# GitHub Actions Deploy Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Wire up four manually-triggered GitHub Actions workflows (bootstrap, deploy, db-push, rollback) plus an idempotent server bootstrap, so a fresh Ubuntu droplet at `157.245.45.208` becomes a hardened Docker host running the existing Shobjiwala WordPress stack, and `docker compose` deploys are atomic.

**Architecture:** Composer runs in the CI runner, files rsync into `/opt/shobjiwala/releases/<ts>/` on the server, a symlink flip is the atomic switch, and `docker compose -f docker-compose.yml -f docker-compose.prod.yml` brings the stack up. Server-specific values are all workflow inputs / GitHub Secrets so the entire pipeline survives a server swap.

**Tech Stack:** GitHub Actions (`workflow_dispatch`), bash, OpenSSH, rsync, Docker + compose plugin, MariaDB 10.11, WP-CLI, UFW, fail2ban, Let's Encrypt (deferred).

---

## File Structure

**Create:**

| Path                                          | Responsibility                                              |
|-----------------------------------------------|-------------------------------------------------------------|
| `.github/workflows/server-bootstrap.yml`      | One-time manual workflow; invokes `bootstrap.sh` via SSH    |
| `.github/workflows/deploy.yml`                | Manual deploy; runs composer in CI, rsync, calls `release.sh` |
| `.github/workflows/db-push.yml`               | Manual DB push; scp dump, calls `db-import.sh`              |
| `.github/workflows/rollback.yml`              | Manual rollback; calls `rollback.sh`                        |
| `deploy/bootstrap.sh`                         | Idempotent server bootstrap (UFW, SSH, Docker, deploy user) |
| `deploy/release.sh`                           | On-server atomic release (symlink flip, `docker compose up`) |
| `deploy/db-import.sh`                         | On-server DB import + URL search-replace                    |
| `deploy/rollback.sh`                          | On-server release symlink revert                            |
| `deploy/snapshots/.gitkeep`                   | Keep `deploy/snapshots/` in git (dumps themselves ignored)  |
| `Makefile`                                    | Local helpers: `db-dump`                                    |
| `docs/deployment.md`                          | Runbook for each workflow + server-migration steps          |

**Modify:**

| Path           | Change                                              |
|----------------|-----------------------------------------------------|
| `.gitignore`   | Add `deploy/snapshots/*.sql.gz` (dumps stay local)  |

**Not changed (intentionally):**

- `docker/nginx/conf.d/site.conf` — `/healthz` location already exists at lines 11–15.
- `docker-compose.yml` / `docker-compose.prod.yml` — already wires Nginx ⇄ Apache.
- `wp-config.php`, mu-plugins — security hardening already done in M4–M8.

## GitHub Secrets required (set manually before running workflows)

| Secret                | Used by                       | Value                                                              |
|-----------------------|-------------------------------|--------------------------------------------------------------------|
| `SERVER_HOST`         | all workflows                 | `157.245.45.208` (today)                                           |
| `SSH_USER`            | deploy / db-push / rollback   | `deploy`                                                           |
| `SSH_PRIVATE_KEY`     | deploy / db-push / rollback   | CI keypair private half (PEM)                                      |
| `BOOTSTRAP_SSH_KEY`   | server-bootstrap **only**     | DigitalOcean-planted root key (PEM); delete after first successful run |
| `KNOWN_HOSTS`         | all workflows                 | output of `ssh-keyscan -H $SERVER_HOST` (avoid TOFU on every run) |

## Local verification tools (install before running plan)

```bash
# macOS
brew install shellcheck actionlint
```

Both are also available via apt on Linux runners. Each script and workflow task includes a lint step that **must pass** before committing.

---

## Task 1: Repo scaffolding (gitignore, Makefile, snapshots dir)

**Files:**
- Create: `deploy/snapshots/.gitkeep`
- Create: `Makefile`
- Modify: `.gitignore`

- [ ] **Step 1: Verify lint tools installed**

Run: `command -v shellcheck && command -v actionlint`

Expected: both print a path. If either is missing, install via `brew install shellcheck actionlint` (macOS) or apt (Linux).

- [ ] **Step 2: Create `deploy/snapshots/.gitkeep`**

```bash
mkdir -p deploy/snapshots
touch deploy/snapshots/.gitkeep
```

- [ ] **Step 3: Append snapshot-dump ignore to `.gitignore`**

Append to `.gitignore` (end of file):

```gitignore

# DB snapshots — actual dumps live on snapshot/<date> branches, not main.
deploy/snapshots/*.sql.gz
```

- [ ] **Step 4: Create `Makefile`**

Create `Makefile` with:

```makefile
# Shobjiwala local helpers. All targets assume `docker compose` is running.

.PHONY: db-dump
db-dump: ## Dump the running local MariaDB to deploy/snapshots/latest.sql.gz
	@mkdir -p deploy/snapshots
	@docker compose exec -T db sh -c \
	  'mysqldump --single-transaction --routines --triggers --no-tablespaces \
	    -u root -p"$$MARIADB_ROOT_PASSWORD" "$$MARIADB_DATABASE"' \
	  | gzip -9 > deploy/snapshots/latest.sql.gz
	@ls -lh deploy/snapshots/latest.sql.gz
	@echo "Dump ready. Next: commit to snapshot/<date> branch, then run db-push workflow."

.PHONY: db-dump-verify
db-dump-verify: ## Sanity-check the last dump (decompress + grep for table count)
	@test -s deploy/snapshots/latest.sql.gz || { echo "No dump found. Run 'make db-dump' first."; exit 1; }
	@echo "Decompressed size:"
	@gunzip -c deploy/snapshots/latest.sql.gz | wc -c
	@echo "CREATE TABLE count:"
	@gunzip -c deploy/snapshots/latest.sql.gz | grep -c '^CREATE TABLE'
```

- [ ] **Step 5: Verify the Makefile target lists**

Run: `make -n db-dump`

Expected: prints the docker compose mysqldump command without executing it.

- [ ] **Step 6: Verify the dump target actually runs**

Run: `make db-dump`

Expected: produces `deploy/snapshots/latest.sql.gz`. File size is non-zero. (This file is gitignored — confirm with `git status` showing it untracked, while `deploy/snapshots/.gitkeep` is staged.)

- [ ] **Step 7: Commit**

```bash
git add deploy/snapshots/.gitkeep Makefile .gitignore
git commit -m "build: add deploy/snapshots scaffold + Makefile db-dump target"
```

---

## Task 2: Server bootstrap script (`deploy/bootstrap.sh`)

**Files:**
- Create: `deploy/bootstrap.sh`

- [ ] **Step 1: Create `deploy/bootstrap.sh`**

```bash
#!/usr/bin/env bash
# Idempotent bootstrap for a fresh Ubuntu 24.04 droplet.
# Runs as root (during first-time SSH); subsequent steps run scripts as `deploy`.
# Inputs (env vars passed via SSH ENV):
#   CI_PUBKEY     — public half of the CI deploy key (single line, ssh-format)
#   LAPTOP_PUBKEY — public half of the operator's laptop key (single line)
# Side effects: creates /opt/shobjiwala/{releases,shared/uploads}, deploy user,
# UFW rules, fail2ban jail, Docker, swapfile. Writes /opt/shobjiwala/.bootstrap-version.

set -euo pipefail

: "${CI_PUBKEY:?CI_PUBKEY env var required}"
: "${LAPTOP_PUBKEY:?LAPTOP_PUBKEY env var required}"

APP_DIR=/opt/shobjiwala
STATE_FILE="$APP_DIR/.bootstrap-version"
BOOTSTRAP_VERSION=1

log() { printf '[bootstrap] %s\n' "$*"; }

# --- 1. apt update + security upgrades ----------------------------------------
log "Updating apt cache and applying security upgrades"
export DEBIAN_FRONTEND=noninteractive
apt-get update -y
apt-get full-upgrade -y
apt-get install -y --no-install-recommends \
    ca-certificates curl gnupg lsb-release \
    ufw fail2ban rsync unattended-upgrades

# --- 2. unattended-upgrades enabled --------------------------------------------
log "Enabling unattended security upgrades"
dpkg-reconfigure -f noninteractive unattended-upgrades
systemctl enable --now unattended-upgrades

# --- 3. Docker + compose plugin (official repo) --------------------------------
if ! command -v docker >/dev/null 2>&1; then
    log "Installing Docker engine"
    install -m 0755 -d /etc/apt/keyrings
    curl -fsSL https://download.docker.com/linux/ubuntu/gpg \
        | gpg --dearmor -o /etc/apt/keyrings/docker.gpg
    chmod a+r /etc/apt/keyrings/docker.gpg
    . /etc/os-release
    echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu $VERSION_CODENAME stable" \
        > /etc/apt/sources.list.d/docker.list
    apt-get update -y
    apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
    systemctl enable --now docker
else
    log "Docker already installed — skipping engine install"
fi

# --- 4. Swapfile (2 GB) --------------------------------------------------------
if [ ! -f /swapfile ]; then
    log "Creating 2 GB swapfile"
    fallocate -l 2G /swapfile
    chmod 600 /swapfile
    mkswap /swapfile
    swapon /swapfile
    grep -q '^/swapfile' /etc/fstab || echo '/swapfile none swap sw 0 0' >> /etc/fstab
else
    log "Swapfile already present — skipping"
fi

# --- 5. deploy user ------------------------------------------------------------
if ! id -u deploy >/dev/null 2>&1; then
    log "Creating deploy user"
    adduser --disabled-password --gecos '' --shell /bin/bash deploy
fi
usermod -aG docker deploy

# --- 6. App directory structure ------------------------------------------------
log "Ensuring $APP_DIR layout exists"
mkdir -p "$APP_DIR/releases" "$APP_DIR/shared/uploads"
chown -R deploy:deploy "$APP_DIR"
chmod 750 "$APP_DIR"

# --- 7. SSH keys --------------------------------------------------------------
install_pubkey() {
    local user="$1" home="$2" pubkey="$3"
    install -d -m 700 -o "$user" -g "$user" "$home/.ssh"
    local auth="$home/.ssh/authorized_keys"
    touch "$auth"
    chown "$user:$user" "$auth"
    chmod 600 "$auth"
    grep -qxF "$pubkey" "$auth" || echo "$pubkey" >> "$auth"
}
log "Installing CI pubkey for deploy@ and laptop pubkey for root@"
install_pubkey deploy /home/deploy "$CI_PUBKEY"
install_pubkey root   /root        "$LAPTOP_PUBKEY"

# --- 8. SSH lockout-protection: verify deploy can log in BEFORE hardening ------
# Caller (the workflow) is responsible for the actual login check — bootstrap
# only enforces the precondition that the deploy authorized_keys file exists
# and is owned correctly. The workflow's own "smoke test" SSH connection as
# deploy@ MUST succeed before the hardening section below runs.
if [ ! -s /home/deploy/.ssh/authorized_keys ]; then
    log "FATAL: deploy authorized_keys missing or empty — refusing to harden SSH"
    exit 1
fi

# --- 9. SSH hardening ----------------------------------------------------------
log "Hardening /etc/ssh/sshd_config"
sshd_set() {
    local key="$1" val="$2" file=/etc/ssh/sshd_config
    if grep -qE "^[#[:space:]]*${key}[[:space:]]" "$file"; then
        sed -ri "s|^[#[:space:]]*(${key})[[:space:]].*|\1 ${val}|" "$file"
    else
        echo "${key} ${val}" >> "$file"
    fi
}
sshd_set PasswordAuthentication no
sshd_set PermitRootLogin prohibit-password
sshd_set KbdInteractiveAuthentication no
sshd_set ChallengeResponseAuthentication no
sshd -t  # validate before reload
systemctl reload ssh

# --- 10. UFW -------------------------------------------------------------------
log "Configuring UFW"
ufw --force reset
ufw default deny incoming
ufw default allow outgoing
ufw allow 22/tcp
ufw allow 80/tcp
ufw allow 443/tcp
ufw --force enable

# --- 11. fail2ban --------------------------------------------------------------
log "Enabling fail2ban sshd jail"
cat >/etc/fail2ban/jail.d/sshd.local <<'EOF'
[sshd]
enabled = true
EOF
systemctl enable --now fail2ban

# --- 12. Timezone --------------------------------------------------------------
timedatectl set-timezone UTC

# --- 13. Docker smoke ----------------------------------------------------------
docker info >/dev/null

# --- 14. State -----------------------------------------------------------------
echo "$BOOTSTRAP_VERSION" > "$STATE_FILE"
chown deploy:deploy "$STATE_FILE"

log "Bootstrap complete (version $BOOTSTRAP_VERSION)."
log "REMINDER: delete the BOOTSTRAP_SSH_KEY GitHub Secret now."
```

- [ ] **Step 2: Make executable**

Run: `chmod +x deploy/bootstrap.sh`

- [ ] **Step 3: Lint with shellcheck**

Run: `shellcheck deploy/bootstrap.sh`

Expected: exits 0, no output (or only style warnings — fix anything ≥ `warning` level).

- [ ] **Step 4: Syntax check with bash**

Run: `bash -n deploy/bootstrap.sh`

Expected: exits 0, no output.

- [ ] **Step 5: Commit**

```bash
git add deploy/bootstrap.sh
git commit -m "feat(deploy): add idempotent server bootstrap script"
```

---

## Task 3: Server bootstrap workflow (`.github/workflows/server-bootstrap.yml`)

**Files:**
- Create: `.github/workflows/server-bootstrap.yml`

- [ ] **Step 1: Create the workflow**

```yaml
name: Server Bootstrap (one-time)

on:
  workflow_dispatch:
    inputs:
      laptop_pubkey:
        description: "Operator laptop SSH public key (single line, ssh-ed25519/ssh-rsa)"
        required: true
        type: string
      ci_pubkey:
        description: "CI deploy key public half (single line, ssh-ed25519/ssh-rsa)"
        required: true
        type: string

permissions:
  contents: read

jobs:
  bootstrap:
    name: Bootstrap droplet
    runs-on: ubuntu-latest
    timeout-minutes: 15
    steps:
      - name: Checkout
        uses: actions/checkout@v4

      - name: Validate inputs look like SSH pubkeys
        run: |
          for k in "${{ inputs.laptop_pubkey }}" "${{ inputs.ci_pubkey }}"; do
            case "$k" in
              ssh-ed25519\ *|ssh-rsa\ *|ecdsa-sha2-*\ *) ;;
              *) echo "Input does not look like an SSH public key: $k" >&2; exit 1;;
            esac
          done

      - name: Install bootstrap SSH key
        env:
          BOOTSTRAP_SSH_KEY: ${{ secrets.BOOTSTRAP_SSH_KEY }}
          KNOWN_HOSTS: ${{ secrets.KNOWN_HOSTS }}
        run: |
          mkdir -p ~/.ssh
          printf '%s' "$BOOTSTRAP_SSH_KEY" > ~/.ssh/id_bootstrap
          chmod 600 ~/.ssh/id_bootstrap
          printf '%s\n' "$KNOWN_HOSTS" > ~/.ssh/known_hosts
          chmod 644 ~/.ssh/known_hosts

      - name: Copy bootstrap.sh to server
        env:
          SERVER_HOST: ${{ secrets.SERVER_HOST }}
        run: |
          scp -i ~/.ssh/id_bootstrap -o IdentitiesOnly=yes \
            deploy/bootstrap.sh "root@${SERVER_HOST}:/root/bootstrap.sh"

      - name: Run bootstrap as root
        env:
          SERVER_HOST: ${{ secrets.SERVER_HOST }}
          CI_PUBKEY: ${{ inputs.ci_pubkey }}
          LAPTOP_PUBKEY: ${{ inputs.laptop_pubkey }}
        run: |
          ssh -i ~/.ssh/id_bootstrap -o IdentitiesOnly=yes \
            "root@${SERVER_HOST}" \
            "CI_PUBKEY='${CI_PUBKEY}' LAPTOP_PUBKEY='${LAPTOP_PUBKEY}' bash /root/bootstrap.sh"

      - name: Verify deploy@ SSH works (post-hardening)
        env:
          SSH_PRIVATE_KEY: ${{ secrets.SSH_PRIVATE_KEY }}
          SERVER_HOST: ${{ secrets.SERVER_HOST }}
        run: |
          printf '%s' "$SSH_PRIVATE_KEY" > ~/.ssh/id_deploy
          chmod 600 ~/.ssh/id_deploy
          ssh -i ~/.ssh/id_deploy -o IdentitiesOnly=yes \
            "deploy@${SERVER_HOST}" \
            "id && docker info >/dev/null && echo 'deploy user OK'"

      - name: Print reminder
        run: |
          echo "::warning ::Bootstrap done. DELETE the BOOTSTRAP_SSH_KEY GitHub Secret now."
```

- [ ] **Step 2: Lint with actionlint**

Run: `actionlint .github/workflows/server-bootstrap.yml`

Expected: exits 0, no output.

- [ ] **Step 3: Commit**

```bash
git add .github/workflows/server-bootstrap.yml
git commit -m "feat(ci): server-bootstrap workflow (manual, one-time)"
```

---

## Task 4: Release script (`deploy/release.sh`)

**Files:**
- Create: `deploy/release.sh`

- [ ] **Step 1: Create the script**

```bash
#!/usr/bin/env bash
# Atomic on-server release: link shared bits, flip symlink, docker compose up.
# Arg: $1 = RELEASE_TS (timestamp dir under /opt/shobjiwala/releases/)
# Runs as `deploy` user.
set -euo pipefail

RELEASE_TS="${1:?RELEASE_TS arg required}"
APP_DIR=/opt/shobjiwala
NEW="$APP_DIR/releases/$RELEASE_TS"
SHARED="$APP_DIR/shared"

log() { printf '[release] %s\n' "$*"; }

[ -d "$NEW" ] || { log "FATAL: $NEW does not exist"; exit 1; }
[ -f "$SHARED/.env" ] || { log "FATAL: $SHARED/.env missing — populate it before deploy"; exit 1; }

# --- 1. Link shared state into the new release --------------------------------
log "Linking shared .env and uploads into $NEW"
ln -sfn "$SHARED/.env" "$NEW/.env"
mkdir -p "$NEW/wp-content"
ln -sfn "$SHARED/uploads" "$NEW/wp-content/uploads"

# --- 2. Atomic symlink swap ---------------------------------------------------
log "Swapping current → $RELEASE_TS"
ln -sfn "$NEW" "$APP_DIR/current.new"
mv -Tf "$APP_DIR/current.new" "$APP_DIR/current"

# --- 3. docker compose up -----------------------------------------------------
cd "$APP_DIR/current"
log "docker compose build wordpress"
docker compose -f docker-compose.yml -f docker-compose.prod.yml \
    --env-file .env build wordpress
log "docker compose up -d"
docker compose -f docker-compose.yml -f docker-compose.prod.yml \
    --env-file .env up -d --remove-orphans

# --- 4. Prune old releases ----------------------------------------------------
log "Pruning old releases (keeping last 5)"
ls -1dt "$APP_DIR/releases/"*/ 2>/dev/null | tail -n +6 | xargs -r rm -rf

log "Release $RELEASE_TS active."
```

- [ ] **Step 2: Make executable**

Run: `chmod +x deploy/release.sh`

- [ ] **Step 3: Lint**

Run: `shellcheck deploy/release.sh && bash -n deploy/release.sh`

Expected: exits 0, no output.

- [ ] **Step 4: Commit**

```bash
git add deploy/release.sh
git commit -m "feat(deploy): atomic on-server release script"
```

---

## Task 5: Deploy workflow (`.github/workflows/deploy.yml`)

**Files:**
- Create: `.github/workflows/deploy.yml`

- [ ] **Step 1: Create the workflow**

```yaml
name: Deploy

on:
  workflow_dispatch:
    inputs:
      ref:
        description: "Branch, tag, or SHA to deploy"
        required: false
        default: main
        type: string

permissions:
  contents: read

concurrency:
  group: deploy-prod
  cancel-in-progress: false

jobs:
  deploy:
    name: Build + ship
    runs-on: ubuntu-latest
    timeout-minutes: 20
    env:
      SERVER_HOST: ${{ secrets.SERVER_HOST }}
      SSH_USER: ${{ secrets.SSH_USER }}
    steps:
      - name: Checkout
        uses: actions/checkout@v4
        with:
          ref: ${{ inputs.ref }}

      - name: Set up PHP 8.3
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          tools: composer:v2
          coverage: none

      - name: Composer install (no dev)
        run: composer install --no-dev --prefer-dist --no-progress --no-interaction --optimize-autoloader

      - name: Stage build artifact
        run: |
          set -euo pipefail
          rm -rf build
          mkdir -p build
          rsync -a --delete \
            --exclude='.git' \
            --exclude='node_modules' \
            --exclude='tests' \
            --exclude='.env*' \
            --exclude='*.sql' \
            --exclude='*.sql.gz' \
            --exclude='data/' \
            --exclude='.organic-ai/' \
            --exclude='wp-content/uploads' \
            wp-content/ build/wp-content/
          rsync -a vendor/ build/vendor/
          cp wp-config.php build/
          cp composer.json composer.lock build/
          cp docker-compose.yml docker-compose.prod.yml build/
          cp -r docker build/docker
          cp -r deploy build/deploy
          ls -la build

      - name: Compute release timestamp
        id: ts
        run: echo "release_ts=$(date -u +%Y-%m-%dT%H-%M-%SZ)" >> "$GITHUB_OUTPUT"

      - name: Install SSH key + known_hosts
        env:
          SSH_PRIVATE_KEY: ${{ secrets.SSH_PRIVATE_KEY }}
          KNOWN_HOSTS:     ${{ secrets.KNOWN_HOSTS }}
        run: |
          mkdir -p ~/.ssh
          printf '%s' "$SSH_PRIVATE_KEY" > ~/.ssh/id_deploy
          chmod 600 ~/.ssh/id_deploy
          printf '%s\n' "$KNOWN_HOSTS" > ~/.ssh/known_hosts
          chmod 644 ~/.ssh/known_hosts

      - name: Rsync build → server
        env:
          RELEASE_TS: ${{ steps.ts.outputs.release_ts }}
        run: |
          rsync -az --delete \
            -e "ssh -i ~/.ssh/id_deploy -o IdentitiesOnly=yes" \
            build/ \
            "${SSH_USER}@${SERVER_HOST}:/opt/shobjiwala/releases/${RELEASE_TS}/"

      - name: Run release.sh on server
        env:
          RELEASE_TS: ${{ steps.ts.outputs.release_ts }}
        run: |
          ssh -i ~/.ssh/id_deploy -o IdentitiesOnly=yes \
            "${SSH_USER}@${SERVER_HOST}" \
            "bash /opt/shobjiwala/releases/${RELEASE_TS}/deploy/release.sh '${RELEASE_TS}'"

      - name: Health check
        run: |
          set -e
          for i in $(seq 1 10); do
            if curl -fsS "http://${SERVER_HOST}/healthz" >/dev/null; then
              echo "Health check passed on attempt $i"
              exit 0
            fi
            echo "Attempt $i failed, sleeping 6s"
            sleep 6
          done
          echo "Health check FAILED — triggering auto-rollback" >&2
          exit 1

      - name: Auto-rollback on failure
        if: failure() && steps.ts.outputs.release_ts != ''
        run: |
          ssh -i ~/.ssh/id_deploy -o IdentitiesOnly=yes \
            "${SSH_USER}@${SERVER_HOST}" \
            "bash /opt/shobjiwala/current/deploy/rollback.sh ''"
```

- [ ] **Step 2: Lint**

Run: `actionlint .github/workflows/deploy.yml`

Expected: exits 0, no output.

- [ ] **Step 3: Commit**

```bash
git add .github/workflows/deploy.yml
git commit -m "feat(ci): deploy workflow (manual, atomic release + health check)"
```

---

## Task 6: DB import script (`deploy/db-import.sh`)

**Files:**
- Create: `deploy/db-import.sh`

- [ ] **Step 1: Create the script**

```bash
#!/usr/bin/env bash
# On-server DB import + URL search-replace.
# Args:
#   $1 = absolute path to .sql.gz dump file on the server
#   $2 = OLD_URL (e.g. http://localhost:8080)
#   $3 = NEW_URL (e.g. http://157.245.45.208)
#   $4 = "--apply" to actually run search-replace; otherwise dry-run only.
# Runs as `deploy` user.
set -euo pipefail

DUMP="${1:?dump file path required}"
OLD_URL="${2:?OLD_URL required}"
NEW_URL="${3:?NEW_URL required}"
MODE="${4:---dry-run}"

APP_DIR=/opt/shobjiwala
log() { printf '[db-import] %s\n' "$*"; }

[ -f "$DUMP" ] || { log "FATAL: dump $DUMP not found"; exit 1; }
cd "$APP_DIR/current"

# --- 1. Insurance dump --------------------------------------------------------
log "Triggering an insurance backup via db-backup container"
docker compose -f docker-compose.yml -f docker-compose.prod.yml \
    --env-file .env exec -T db-backup /usr/local/bin/backup.sh || \
    log "WARN: insurance backup failed — proceeding anyway"

# --- 2. Import ----------------------------------------------------------------
log "Importing $DUMP into the db container"
# shellcheck disable=SC2016  # $-vars are intentionally shell-expanded inside the container
gunzip -c "$DUMP" | docker compose -f docker-compose.yml -f docker-compose.prod.yml \
    --env-file .env exec -T db sh -c \
    'mysql -uroot -p"$MARIADB_ROOT_PASSWORD" "$MARIADB_DATABASE"'

# --- 3. URL search-replace ----------------------------------------------------
log "Search-replace: $OLD_URL → $NEW_URL (mode: $MODE)"
SR_ARGS=(
    "$OLD_URL" "$NEW_URL"
    --all-tables-with-prefix
    --precise
    --skip-columns=guid
    --allow-root
)
if [ "$MODE" = "--apply" ]; then
    docker compose -f docker-compose.yml -f docker-compose.prod.yml \
        --env-file .env exec -T wordpress wp search-replace "${SR_ARGS[@]}"
    log "Flushing object cache"
    docker compose -f docker-compose.yml -f docker-compose.prod.yml \
        --env-file .env exec -T wordpress wp cache flush --allow-root
else
    log "Dry-run only — pass --apply to commit"
    docker compose -f docker-compose.yml -f docker-compose.prod.yml \
        --env-file .env exec -T wordpress wp search-replace "${SR_ARGS[@]}" --dry-run
fi

log "Done."
```

- [ ] **Step 2: Make executable**

Run: `chmod +x deploy/db-import.sh`

- [ ] **Step 3: Lint**

Run: `shellcheck deploy/db-import.sh && bash -n deploy/db-import.sh`

Expected: exits 0. (The `# shellcheck disable=SC2016` line is intentional — those `$` vars expand inside the `db` container, not on the host.)

- [ ] **Step 4: Commit**

```bash
git add deploy/db-import.sh
git commit -m "feat(deploy): DB import + URL search-replace script"
```

---

## Task 7: DB push workflow (`.github/workflows/db-push.yml`)

**Files:**
- Create: `.github/workflows/db-push.yml`

- [ ] **Step 1: Create the workflow**

```yaml
name: DB Push (DESTRUCTIVE)

on:
  workflow_dispatch:
    inputs:
      confirm:
        description: 'Type literally: OVERWRITE-PROD-DB'
        required: true
        type: string
      old_url:
        description: "URL currently stored in the dump"
        required: false
        default: "http://localhost:8080"
        type: string
      new_url:
        description: "URL to rewrite to on the server"
        required: false
        default: "http://157.245.45.208"
        type: string
      ref:
        description: "Branch holding the snapshot (e.g. snapshot/2026-05-22)"
        required: true
        type: string

permissions:
  contents: read

concurrency:
  group: db-push-prod
  cancel-in-progress: false

jobs:
  push:
    name: Ship + import DB dump
    runs-on: ubuntu-latest
    timeout-minutes: 20
    env:
      SERVER_HOST: ${{ secrets.SERVER_HOST }}
      SSH_USER:    ${{ secrets.SSH_USER }}
    steps:
      - name: Hard gate — verify confirm string
        run: |
          if [ "${{ inputs.confirm }}" != "OVERWRITE-PROD-DB" ]; then
            echo "Refusing to run — confirm field must equal OVERWRITE-PROD-DB" >&2
            exit 1
          fi

      - name: Checkout snapshot branch
        uses: actions/checkout@v4
        with:
          ref: ${{ inputs.ref }}

      - name: Verify dump exists in checked-out tree
        run: |
          test -s deploy/snapshots/latest.sql.gz \
            || { echo "deploy/snapshots/latest.sql.gz missing on branch ${{ inputs.ref }}"; exit 1; }
          ls -lh deploy/snapshots/latest.sql.gz

      - name: Install SSH key + known_hosts
        env:
          SSH_PRIVATE_KEY: ${{ secrets.SSH_PRIVATE_KEY }}
          KNOWN_HOSTS:     ${{ secrets.KNOWN_HOSTS }}
        run: |
          mkdir -p ~/.ssh
          printf '%s' "$SSH_PRIVATE_KEY" > ~/.ssh/id_deploy
          chmod 600 ~/.ssh/id_deploy
          printf '%s\n' "$KNOWN_HOSTS" > ~/.ssh/known_hosts
          chmod 644 ~/.ssh/known_hosts

      - name: SCP dump to server
        run: |
          ssh -i ~/.ssh/id_deploy -o IdentitiesOnly=yes \
            "${SSH_USER}@${SERVER_HOST}" "mkdir -p /opt/shobjiwala/shared/db-pushes"
          scp -i ~/.ssh/id_deploy -o IdentitiesOnly=yes \
            deploy/snapshots/latest.sql.gz \
            "${SSH_USER}@${SERVER_HOST}:/opt/shobjiwala/shared/db-pushes/latest.sql.gz"

      - name: Dry-run search-replace
        run: |
          ssh -i ~/.ssh/id_deploy -o IdentitiesOnly=yes \
            "${SSH_USER}@${SERVER_HOST}" \
            "bash /opt/shobjiwala/current/deploy/db-import.sh \
              /opt/shobjiwala/shared/db-pushes/latest.sql.gz \
              '${{ inputs.old_url }}' '${{ inputs.new_url }}'"

      - name: Apply search-replace for real
        run: |
          ssh -i ~/.ssh/id_deploy -o IdentitiesOnly=yes \
            "${SSH_USER}@${SERVER_HOST}" \
            "bash /opt/shobjiwala/current/deploy/db-import.sh \
              /opt/shobjiwala/shared/db-pushes/latest.sql.gz \
              '${{ inputs.old_url }}' '${{ inputs.new_url }}' --apply"

      - name: Health check
        run: |
          for i in $(seq 1 5); do
            if curl -fsS "http://${SERVER_HOST}/healthz" >/dev/null; then
              echo "OK"; exit 0
            fi
            sleep 5
          done
          echo "Health check failed after DB push" >&2; exit 1
```

- [ ] **Step 2: Lint**

Run: `actionlint .github/workflows/db-push.yml`

Expected: exits 0.

- [ ] **Step 3: Commit**

```bash
git add .github/workflows/db-push.yml
git commit -m "feat(ci): db-push workflow (manual, gated by confirm string)"
```

---

## Task 8: Rollback script (`deploy/rollback.sh`)

**Files:**
- Create: `deploy/rollback.sh`

- [ ] **Step 1: Create the script**

```bash
#!/usr/bin/env bash
# Re-symlink `current` to a prior release and bounce docker compose.
# Arg: $1 = target release dir name (e.g. 2026-05-22T14-30-00Z).
#         If empty, picks the second-newest release.
# Runs as `deploy` user.
set -euo pipefail

TARGET="${1:-}"
APP_DIR=/opt/shobjiwala
log() { printf '[rollback] %s\n' "$*"; }

if [ -z "$TARGET" ]; then
    TARGET="$(ls -1dt "$APP_DIR/releases/"*/ 2>/dev/null | sed -n '2p' | xargs -r basename)"
    [ -n "$TARGET" ] || { log "FATAL: no prior release found"; exit 1; }
    log "No release specified — using previous: $TARGET"
fi

PREV="$APP_DIR/releases/$TARGET"
[ -d "$PREV" ] || { log "FATAL: $PREV does not exist"; exit 1; }

# Re-link shared bits (defensive — release.sh does this on initial deploy but
# the prev release may have lost its symlinks during a partial deploy).
ln -sfn "$APP_DIR/shared/.env" "$PREV/.env"
mkdir -p "$PREV/wp-content"
ln -sfn "$APP_DIR/shared/uploads" "$PREV/wp-content/uploads"

log "Swapping current → $TARGET"
ln -sfn "$PREV" "$APP_DIR/current.new"
mv -Tf "$APP_DIR/current.new" "$APP_DIR/current"

cd "$APP_DIR/current"
log "docker compose up -d (post-rollback)"
docker compose -f docker-compose.yml -f docker-compose.prod.yml \
    --env-file .env up -d --remove-orphans

log "Rollback to $TARGET complete."
```

- [ ] **Step 2: Make executable**

Run: `chmod +x deploy/rollback.sh`

- [ ] **Step 3: Lint**

Run: `shellcheck deploy/rollback.sh && bash -n deploy/rollback.sh`

Expected: exits 0.

- [ ] **Step 4: Commit**

```bash
git add deploy/rollback.sh
git commit -m "feat(deploy): rollback script (symlink revert + compose up)"
```

---

## Task 9: Rollback workflow (`.github/workflows/rollback.yml`)

**Files:**
- Create: `.github/workflows/rollback.yml`

- [ ] **Step 1: Create the workflow**

```yaml
name: Rollback

on:
  workflow_dispatch:
    inputs:
      release:
        description: "Release timestamp dir (empty = previous release)"
        required: false
        default: ""
        type: string

permissions:
  contents: read

concurrency:
  group: deploy-prod
  cancel-in-progress: false

jobs:
  rollback:
    name: Revert symlink + bounce compose
    runs-on: ubuntu-latest
    timeout-minutes: 10
    env:
      SERVER_HOST: ${{ secrets.SERVER_HOST }}
      SSH_USER:    ${{ secrets.SSH_USER }}
    steps:
      - name: Install SSH key + known_hosts
        env:
          SSH_PRIVATE_KEY: ${{ secrets.SSH_PRIVATE_KEY }}
          KNOWN_HOSTS:     ${{ secrets.KNOWN_HOSTS }}
        run: |
          mkdir -p ~/.ssh
          printf '%s' "$SSH_PRIVATE_KEY" > ~/.ssh/id_deploy
          chmod 600 ~/.ssh/id_deploy
          printf '%s\n' "$KNOWN_HOSTS" > ~/.ssh/known_hosts
          chmod 644 ~/.ssh/known_hosts

      - name: List available releases (for log visibility)
        run: |
          ssh -i ~/.ssh/id_deploy -o IdentitiesOnly=yes \
            "${SSH_USER}@${SERVER_HOST}" \
            "ls -1dt /opt/shobjiwala/releases/*/ | head -10"

      - name: Run rollback.sh
        run: |
          ssh -i ~/.ssh/id_deploy -o IdentitiesOnly=yes \
            "${SSH_USER}@${SERVER_HOST}" \
            "bash /opt/shobjiwala/current/deploy/rollback.sh '${{ inputs.release }}'"

      - name: Health check
        run: |
          for i in $(seq 1 5); do
            if curl -fsS "http://${SERVER_HOST}/healthz" >/dev/null; then
              echo "OK"; exit 0
            fi
            sleep 5
          done
          echo "Health check failed after rollback" >&2; exit 1
```

- [ ] **Step 2: Lint**

Run: `actionlint .github/workflows/rollback.yml`

Expected: exits 0.

- [ ] **Step 3: Commit**

```bash
git add .github/workflows/rollback.yml
git commit -m "feat(ci): rollback workflow (manual symlink revert)"
```

---

## Task 10: Deployment runbook (`docs/deployment.md`)

**Files:**
- Create: `docs/deployment.md`

- [ ] **Step 1: Create `docs/deployment.md`**

```markdown
# Deployment Runbook

This repo ships to `157.245.45.208` via four manually-triggered GitHub Actions
workflows. Everything is `workflow_dispatch` — nothing deploys on push.

## One-time setup

### 1. Generate the CI keypair (on your laptop)

```bash
ssh-keygen -t ed25519 -f ~/.ssh/shobjiwala_ci -C "shobjiwala-ci" -N ""
cat ~/.ssh/shobjiwala_ci.pub   # paste as ci_pubkey input
cat ~/.ssh/shobjiwala_ci       # paste as SSH_PRIVATE_KEY secret
```

### 2. Set GitHub repo secrets

| Secret              | Value                                                  |
|---------------------|--------------------------------------------------------|
| `SERVER_HOST`       | `157.245.45.208`                                       |
| `SSH_USER`          | `deploy`                                               |
| `SSH_PRIVATE_KEY`   | contents of `~/.ssh/shobjiwala_ci`                     |
| `BOOTSTRAP_SSH_KEY` | private key DigitalOcean lets you SSH in as `root` with |
| `KNOWN_HOSTS`       | `ssh-keyscan -H 157.245.45.208` output                 |

### 3. Populate `/opt/shobjiwala/shared/.env` on the server

Before running deploy, the env file must exist on the server. From your laptop:

```bash
scp -i ~/.ssh/<your-root-key> .env.prod.example root@157.245.45.208:/opt/shobjiwala/shared/.env
ssh root@157.245.45.208 'chown deploy:deploy /opt/shobjiwala/shared/.env && chmod 600 /opt/shobjiwala/shared/.env'
```

Then edit it on the server (filling in real secrets — see `.env.prod.example`
for the full variable list).

### 4. Run `Server Bootstrap` workflow

GitHub Actions → "Server Bootstrap (one-time)" → Run workflow.

Inputs:
- `laptop_pubkey` — your own `~/.ssh/id_*.pub` (single line)
- `ci_pubkey` — contents of `~/.ssh/shobjiwala_ci.pub`

Wait for green. Then **delete the `BOOTSTRAP_SSH_KEY` secret** from the repo.

## Regular deploy

```text
GitHub Actions → "Deploy" → Run workflow → (optional ref) → Run
```

- Composer runs in CI
- Files rsync to `/opt/shobjiwala/releases/<UTC-timestamp>/`
- Symlink `current` flips atomically
- `docker compose up -d`
- Health check on `http://$SERVER_HOST/healthz`
- Auto-rollback on health-check failure

## First-time DB push

1. Locally: `make db-dump` (creates `deploy/snapshots/latest.sql.gz`)
2. Verify: `make db-dump-verify`
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
   - `new_url` = `http://157.245.45.208` (default)

The workflow runs a dry-run search-replace before the real one — read the log.

## Rollback

```text
GitHub Actions → "Rollback" → Run workflow → (optional release) → Run
```

- Empty `release` → flips to second-newest release dir.
- Specific release → use the timestamp dir name (e.g. `2026-05-22T14-30-00Z`).

The workflow logs the available releases at the top so you can copy/paste.

## DB restore from `db-backup` (manual, when DB rollback is needed)

`docker-compose.prod.yml` runs `db-backup` hourly into a docker volume.

```bash
ssh deploy@$SERVER_HOST
cd /opt/shobjiwala/current
docker compose exec db-backup ls /backups        # find the dump you want
docker compose exec db-backup sh -c \
    'gunzip -c /backups/<file>.sql.gz | mysql -h db -uroot -p"$DB_PASSWORD" "$DB_NAME"'
```

## Server migration (new IP / new droplet)

1. Provision new Ubuntu droplet.
2. Update GH secrets: `SERVER_HOST` (new IP), `BOOTSTRAP_SSH_KEY` (DO root key), `KNOWN_HOSTS` (new `ssh-keyscan`).
3. Run `Server Bootstrap` workflow with the same `laptop_pubkey` / `ci_pubkey`.
4. Scp a fresh `.env` into `/opt/shobjiwala/shared/.env` (see step 3 of "One-time setup").
5. Run `Deploy`.
6. Run `DB Push` with `new_url` = new IP (or domain).
7. Once verified, flip DNS / decommission the old droplet.

## Break-glass SSH

Hardening keeps `PermitRootLogin prohibit-password` — root can still log in
with the laptop pubkey you supplied during bootstrap. Use it if the `deploy`
key is lost or the CI workflow is broken.
```

- [ ] **Step 2: Commit**

```bash
git add docs/deployment.md
git commit -m "docs: deployment runbook (bootstrap, deploy, db-push, rollback)"
```

---

## Task 11: Plan-completion verification checklist (no code, just runs)

This is the engineer's smoke-test list before they hand back the work. None of these are auto-runnable in CI — they require the deployment workflows themselves to execute.

- [ ] **Step 1: All lint passes locally**

Run from repo root:

```bash
shellcheck deploy/*.sh
actionlint .github/workflows/server-bootstrap.yml \
           .github/workflows/deploy.yml \
           .github/workflows/db-push.yml \
           .github/workflows/rollback.yml
```

Expected: both exit 0.

- [ ] **Step 2: Verify all expected files exist**

```bash
ls -la \
  .github/workflows/server-bootstrap.yml \
  .github/workflows/deploy.yml \
  .github/workflows/db-push.yml \
  .github/workflows/rollback.yml \
  deploy/bootstrap.sh \
  deploy/release.sh \
  deploy/db-import.sh \
  deploy/rollback.sh \
  deploy/snapshots/.gitkeep \
  Makefile \
  docs/deployment.md
```

Expected: all listed with `-rwxr-xr-x` for `.sh` files, `-rw-r--r--` for the rest.

- [ ] **Step 3: Verify `.gitignore` covers snapshot dumps**

Run: `grep -F 'deploy/snapshots/*.sql.gz' .gitignore`

Expected: prints the line.

- [ ] **Step 4: Confirm secrets list is documented**

Run: `grep -E 'SERVER_HOST|SSH_USER|SSH_PRIVATE_KEY|BOOTSTRAP_SSH_KEY|KNOWN_HOSTS' docs/deployment.md | wc -l`

Expected: ≥ 5.

- [ ] **Step 5: Hand off the runbook**

Print the path so the user can read it next:

```bash
echo "Runbook: $(pwd)/docs/deployment.md"
```

- [ ] **Step 6: Final commit (only if any lint fixes were needed)**

If steps 1–4 surfaced any drift, fix and commit; otherwise skip.

```bash
git status
# If clean, no action.
# If dirty:
git add -A && git commit -m "chore(deploy): post-plan lint fixes"
```

---

## Plan self-review (notes left for the implementer)

1. **Spec coverage** — every section of the spec maps to a task:
   - § 3 Architecture → Tasks 4+5 (deploy), 6+7 (db-push), 8+9 (rollback), 2+3 (bootstrap)
   - § 4 Strategy B → Task 5 (composer in CI, rsync release dir)
   - § 5.1 Bootstrap → Tasks 2 + 3
   - § 5.2 Deploy + `/healthz` → Task 5 (uses existing `/healthz` in `site.conf`)
   - § 5.3 DB push + search-replace → Tasks 6 + 7
   - § 5.4 Rollback → Tasks 8 + 9
   - § 6 Files added → Task 1 (Makefile, snapshots dir, gitignore) + Task 10 (runbook)
   - § 7 Security recap → Task 2 (UFW, fail2ban, SSH hardening, etc.)
   - § 8 Server-migration runbook → Task 10 ("Server migration" section)
2. **Placeholders** — no `TODO`, `TBD`, or "implement later" remain.
3. **Type consistency** — script function names (`log`, `install_pubkey`, `sshd_set`) are scoped per-file (local bash functions); flag names match (`--apply`, `--dry-run`); paths are all `/opt/shobjiwala/...` everywhere.
4. **Open follow-ups** (deliberately deferred, not part of this plan):
   - TLS / Let's Encrypt — needs a domain first.
   - Off-host backups to S3 / DO Spaces.
   - Reducing WP replicas to 1 if 4 GB RAM proves too tight (one env override, no code change).
