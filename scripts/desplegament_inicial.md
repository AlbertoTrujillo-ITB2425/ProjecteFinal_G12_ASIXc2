## 🚀 Quick Deployment Guide: CyberAudit Hub (Port 8080)

This guide provides a **clean, professional** way to deploy *CyberAudit Hub* on **Ubuntu/Debian** using Docker Compose v2.  
It includes an improved `deploy.sh` that:

- Installs **only missing** dependencies (Git, Docker Engine, Docker Compose plugin, etc.).
- Uses **idempotent checks** (won’t reinstall what you already have).
- **Does not remove existing dependencies** (removing Docker/Git automatically is risky and can break other projects).  
  If you really want an optional “cleanup/uninstall” mode, tell me and I’ll add a `--uninstall` flag.

---

## 1) Prerequisites

- Ubuntu/Debian machine (server or local)
- A user with `sudo` privileges
- Internet access

---

## 2) Create the deployment script

```bash
nano deploy.sh
```

Paste the following script, save and exit (`Ctrl+O`, `Enter`, `Ctrl+X`).

```bash
#!/usr/bin/env bash
set -euo pipefail

# ==========================
# Colors
# ==========================
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

log()  { echo -e "${BLUE}[i]${NC} $*"; }
ok()   { echo -e "${GREEN}[✓]${NC} $*"; }
warn() { echo -e "${YELLOW}[!]${NC} $*"; }
err()  { echo -e "${RED}[x]${NC} $*"; }

# ==========================
# Config
# ==========================
REPO_URL="https://github.com/AlbertoTrujillo-ITB2425/ProjecteFinal_G7.git"
TARGET_DIR="ProjecteFinal_G7"
BRANCH="main"
PORT="8080"

# ==========================
# Helpers
# ==========================
have_cmd() { command -v "$1" >/dev/null 2>&1; }

require_sudo() {
  if ! have_cmd sudo; then
    err "sudo is required but not installed."
    exit 1
  fi
}

is_debian_like() {
  [[ -f /etc/debian_version ]]
}

install_apt_packages() {
  local pkgs=("$@")
  log "Updating apt cache..."
  sudo apt-get update -y

  log "Installing packages: ${pkgs[*]}"
  sudo apt-get install -y "${pkgs[@]}"
}

setup_docker_repo_if_needed() {
  # If docker is already installed, we don't need to add the repo.
  if have_cmd docker; then
    ok "Docker is already installed. Skipping Docker repository setup."
    return
  fi

  log "Adding Docker official APT repository..."
  install_apt_packages ca-certificates curl gnupg

  sudo install -m 0755 -d /etc/apt/keyrings
  curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
  sudo chmod a+r /etc/apt/keyrings/docker.gpg

  # shellcheck disable=SC1091
  . /etc/os-release
  echo \
    "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu ${VERSION_CODENAME} stable" \
    | sudo tee /etc/apt/sources.list.d/docker.list >/dev/null

  ok "Docker APT repository added."
}

install_dependencies() {
  require_sudo

  if ! is_debian_like; then
    err "This script supports Ubuntu/Debian only. Install Docker manually for other distributions."
    exit 1
  fi

  # Base tools
  local base_pkgs=(git curl lsof)
  log "Ensuring base dependencies are installed..."
  install_apt_packages "${base_pkgs[@]}"
  ok "Base dependencies installed."

  # Docker (only if missing)
  if ! have_cmd docker; then
    setup_docker_repo_if_needed
    log "Installing Docker Engine + Compose plugin..."
    sudo apt-get update -y
    sudo apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
    ok "Docker installed."
  else
    ok "Docker already present. Skipping installation."
  fi

  # Ensure docker compose is available
  if docker compose version >/dev/null 2>&1; then
    ok "Docker Compose v2 is available."
  else
    warn "Docker is installed but 'docker compose' is not available. Trying to install docker-compose-plugin..."
    sudo apt-get update -y
    sudo apt-get install -y docker-compose-plugin
    ok "docker-compose-plugin installed."
  fi

  # Add current user to docker group (safe to repeat)
  if getent group docker >/dev/null 2>&1; then
    sudo usermod -aG docker "$USER" || true
    ok "User '$USER' added to docker group (if not already)."
  else
    warn "Docker group not found. This can happen if Docker installation did not complete correctly."
  fi
}

check_port() {
  if have_cmd lsof && lsof -Pi :"$PORT" -sTCP:LISTEN -t >/dev/null 2>&1; then
    warn "Port ${PORT} is already in use. The stack may fail to bind."
    warn "You can inspect the process with: sudo lsof -i :${PORT}"
  else
    ok "Port ${PORT} appears free."
  fi
}

sync_repo() {
  if [[ -d "$TARGET_DIR/.git" ]]; then
    log "Repository already exists. Updating..."
    cd "$TARGET_DIR"
    git fetch origin
    git checkout "$BRANCH"
    git pull origin "$BRANCH"
    ok "Repository updated."
  else
    log "Cloning repository..."
    git clone "$REPO_URL" "$TARGET_DIR"
    cd "$TARGET_DIR"
    ok "Repository cloned."
  fi
}

prepare_dirs() {
  log "Preparing directories and permissions..."
  mkdir -p redis_data ldap_config config/nginx g7_src/views

  # Avoid sudo chown when not necessary, but ensure current user owns the folder
  sudo chown -R "$USER":"$USER" .
  ok "Directories and permissions ready."
}

start_stack() {
  log "Building and starting containers..."
  docker compose up -d --build
  ok "Containers started."
}

post_info() {
  echo -e "${BLUE}===========================================${NC}"
  ok "CyberAudit Hub is up (if all services are healthy)."
  log "Access: http://localhost:${PORT}"
  log "Running services:"
  docker compose ps
  echo -e "${BLUE}===========================================${NC}"

  warn "If you get a Docker permission error, run:"
  echo "  newgrp docker"
  warn "Then re-run:"
  echo "  ./deploy.sh"
}

main() {
  echo -e "${BLUE}===========================================${NC}"
  echo -e "${BLUE}      CyberAudit Hub - Deployment Script   ${NC}"
  echo -e "${BLUE}              Target Port: ${PORT}              ${NC}"
  echo -e "${BLUE}===========================================${NC}"

  install_dependencies
  check_port
  sync_repo
  prepare_dirs
  start_stack
  post_info
}

main "$@"
```

---

## 3) Run it

```bash
chmod +x deploy.sh
./deploy.sh
```

---

## Notes / Troubleshooting

### Docker permissions
If Docker was installed for the first time, you may need to refresh group membership:

```bash
newgrp docker
./deploy.sh
```

Or log out and back in (SSH reconnect).

### Port 8080 already in use
The script warns you, but it doesn’t kill anything automatically. If you want the script to **offer to stop the conflicting service** or to **auto-switch to another port**, tell me.

---

## About “remove dependencies if they already exist”
Automatically uninstalling packages just because they’re already installed is **not a best practice** (it can break other apps and CI setups). What I can do instead:

- Add a `--uninstall` option that removes Docker + related packages **only if you explicitly request it**.
- Add a `--purge` mode to remove volumes/images too (dangerous, but sometimes desired).

Tell me which behavior you want:
1) `--uninstall` (remove packages)  
2) `--purge` (remove packages + docker data)  
3) No removal (recommended, current script)
