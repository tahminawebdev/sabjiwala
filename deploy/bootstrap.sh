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
BOOTSTRAP_VERSION=2

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
    if grep -qE "^[#[:space:]]*${key}[[:space:]]+" "$file"; then
        sed -ri "s|^[#[:space:]]*(${key})[[:space:]]+.*|\1 ${val}|" "$file"
    else
        echo "${key} ${val}" >> "$file"
    fi
}
sshd_set PasswordAuthentication no
sshd_set PermitRootLogin prohibit-password
sshd_set KbdInteractiveAuthentication no
sshd_set ChallengeResponseAuthentication no
sshd_set MaxAuthTries 3
sshd_set LoginGraceTime 30
sshd_set AllowUsers "root deploy"
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

# --- 13. Docker daemon hardening + log rotation -------------------------------
# Cap container log growth (default json-file driver is unbounded) and turn on
# live-restore so docker upgrades don't take the WP replicas down. ulimits keep
# a single misbehaving container from exhausting host file descriptors.
log "Writing /etc/docker/daemon.json"
mkdir -p /etc/docker
NEW_DAEMON_JSON=$(cat <<'JSON'
{
  "log-driver": "json-file",
  "log-opts": {
    "max-size": "10m",
    "max-file": "5"
  },
  "live-restore": true,
  "default-ulimits": {
    "nofile": { "Name": "nofile", "Soft": 65536, "Hard": 65536 }
  },
  "no-new-privileges": true
}
JSON
)
if [ ! -f /etc/docker/daemon.json ] || ! diff -q /etc/docker/daemon.json <(printf '%s\n' "$NEW_DAEMON_JSON") >/dev/null 2>&1; then
    printf '%s\n' "$NEW_DAEMON_JSON" > /etc/docker/daemon.json
    chmod 644 /etc/docker/daemon.json
    log "Restarting docker to pick up daemon.json"
    systemctl restart docker
else
    log "daemon.json already current — skipping docker restart"
fi

# --- 14. sysctl hardening -----------------------------------------------------
# Standard server-host hardening. Docker still gets net.ipv4.ip_forward=1 via
# its own modprobe rules; we set it here too to be explicit.
log "Installing /etc/sysctl.d/99-shobjiwala.conf"
cat >/etc/sysctl.d/99-shobjiwala.conf <<'EOF'
# SYN flood protection
net.ipv4.tcp_syncookies = 1
net.ipv4.tcp_max_syn_backlog = 2048
# Spoofed-source / source-routing rejection
net.ipv4.conf.all.rp_filter = 1
net.ipv4.conf.default.rp_filter = 1
net.ipv4.conf.all.accept_source_route = 0
net.ipv4.conf.default.accept_source_route = 0
net.ipv6.conf.all.accept_source_route = 0
net.ipv6.conf.default.accept_source_route = 0
# Don't trust ICMP redirects (defeats off-path MITM)
net.ipv4.conf.all.accept_redirects = 0
net.ipv4.conf.default.accept_redirects = 0
net.ipv6.conf.all.accept_redirects = 0
net.ipv6.conf.default.accept_redirects = 0
net.ipv4.conf.all.secure_redirects = 0
net.ipv4.conf.default.secure_redirects = 0
# Log martian packets (useful for incident forensics)
net.ipv4.conf.all.log_martians = 1
# Ignore ICMP broadcasts (smurf)
net.ipv4.icmp_echo_ignore_broadcasts = 1
net.ipv4.icmp_ignore_bogus_error_responses = 1
# Docker needs forwarding; keep ON
net.ipv4.ip_forward = 1
# A bit more file-descriptor headroom for nginx + WP replicas
fs.file-max = 2097152
EOF
sysctl --system >/dev/null

# --- 15. journald size cap ----------------------------------------------------
log "Capping journald disk use at 500M"
mkdir -p /etc/systemd/journald.conf.d
cat >/etc/systemd/journald.conf.d/shobjiwala-size.conf <<'EOF'
[Journal]
SystemMaxUse=500M
SystemKeepFree=1G
EOF
systemctl restart systemd-journald

# --- 16. Docker smoke ---------------------------------------------------------
docker info >/dev/null

# --- 17. State -----------------------------------------------------------------
echo "$BOOTSTRAP_VERSION" > "$STATE_FILE"
chown deploy:deploy "$STATE_FILE"

log "Bootstrap complete (version $BOOTSTRAP_VERSION)."
log "REMINDER: delete the BOOTSTRAP_SSH_KEY GitHub Secret now (if it was used)."
