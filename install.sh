#!/usr/bin/env bash
#
# Yuno Panel installer for Debian/Ubuntu.
#
#   curl -fsSL https://raw.githubusercontent.com/Yuno-Digital/Yuno-Panel/main/install.sh | bash
#   # or:  bash install.sh [install-dir]
#
set -euo pipefail

REPO="https://github.com/Yuno-Digital/Yuno-Panel.git"
DIR="${1:-/var/www/yuno-panel}"
PHP="8.4"
NODE_MAJOR="22"

log()  { printf '\033[1;36m==>\033[0m %s\n' "$*"; }
warn() { printf '\033[1;33m!  \033[0m %s\n' "$*"; }
die()  { printf '\033[1;31mx  \033[0m %s\n' "$*" >&2; exit 1; }

# Run privileged commands with sudo unless we are already root.
SUDO=""
if [ "$(id -u)" -ne 0 ]; then
    command -v sudo >/dev/null 2>&1 || die "Please run as root or install sudo."
    SUDO="sudo"
fi

command -v apt-get >/dev/null 2>&1 || die "This installer supports Debian/Ubuntu (apt) only."

log "Installing system dependencies"
$SUDO apt-get update -y
$SUDO apt-get install -y ca-certificates curl git unzip gnupg lsb-release software-properties-common

# --- PHP ---
if ! command -v "php${PHP}" >/dev/null 2>&1 && ! php -v 2>/dev/null | grep -q "PHP ${PHP}"; then
    if grep -qi ubuntu /etc/os-release; then
        log "Adding ondrej/php PPA"
        $SUDO add-apt-repository -y ppa:ondrej/php
        $SUDO apt-get update -y
    fi
fi
log "Installing PHP ${PHP} + extensions"
$SUDO apt-get install -y \
    "php${PHP}-cli" "php${PHP}-common" "php${PHP}-mbstring" "php${PHP}-xml" \
    "php${PHP}-sqlite3" "php${PHP}-mysql" "php${PHP}-curl" "php${PHP}-bcmath" \
    "php${PHP}-gd" "php${PHP}-zip" "php${PHP}-intl" || \
    $SUDO apt-get install -y php-cli php-common php-mbstring php-xml php-sqlite3 php-mysql php-curl php-bcmath php-gd php-zip php-intl

# Make php ${PHP} the default `php` (the app's dependencies need >= 8.4).
if [ -x "/usr/bin/php${PHP}" ]; then
    $SUDO update-alternatives --set php "/usr/bin/php${PHP}" >/dev/null 2>&1 || true
fi

# --- Composer ---
if ! command -v composer >/dev/null 2>&1; then
    log "Installing Composer"
    EXPECTED="$(curl -fsSL https://composer.github.io/installer.sig)"
    curl -fsSL https://getcomposer.org/installer -o /tmp/composer-setup.php
    ACTUAL="$(php -r "echo hash_file('sha384','/tmp/composer-setup.php');")"
    [ "$EXPECTED" = "$ACTUAL" ] || die "Composer installer checksum mismatch."
    php /tmp/composer-setup.php --install-dir=/tmp --filename=composer
    $SUDO mv /tmp/composer /usr/local/bin/composer
    rm -f /tmp/composer-setup.php
fi

# --- Node.js ---
if ! command -v node >/dev/null 2>&1 || [ "$(node -v | sed 's/v\([0-9]*\).*/\1/')" -lt "$NODE_MAJOR" ]; then
    log "Installing Node.js ${NODE_MAJOR}"
    curl -fsSL "https://deb.nodesource.com/setup_${NODE_MAJOR}.x" | $SUDO bash -
    $SUDO apt-get install -y nodejs
fi

# --- Panel ---
if [ -d "$DIR/.git" ]; then
    log "Updating existing panel in $DIR"
    git -C "$DIR" pull --ff-only
else
    log "Cloning panel into $DIR"
    $SUDO mkdir -p "$DIR"
    $SUDO chown -R "$(id -u):$(id -g)" "$DIR"
    git clone "$REPO" "$DIR"
fi
cd "$DIR"

log "Installing PHP dependencies"
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

log "Building front-end assets"
npm ci
npm run build

if [ ! -f .env ]; then
    log "Creating .env"
    cp .env.example .env
    php artisan key:generate --force
fi

# Default to a SQLite database if that's the configured connection.
if grep -q '^DB_CONNECTION=sqlite' .env || ! grep -q '^DB_CONNECTION=' .env; then
    touch database/database.sqlite
fi

log "Setting writable permissions"
chmod -R ug+rw storage bootstrap/cache
if command -v id >/dev/null 2>&1 && getent passwd www-data >/dev/null 2>&1; then
    $SUDO chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
fi

php artisan storage:link >/dev/null 2>&1 || true

log "Done!"
cat <<EOF

  Yuno Panel is installed in: $DIR

  Finish setup in your browser via the web installer:

    Quick test:   php artisan serve --host '[::]' --port 8000
                  then open  http://<server-ip>:8000/install

    ('[::]' listens on IPv6 and IPv4 — use it on IPv6-only servers;
     'http://[<ipv6>]:8000/install' works too.)

  For production, serve public/ with nginx + php-fpm (point the web root at
  $DIR/public). Make nginx listen on both stacks:

    listen 80;
    listen [::]:80;

  Then open  https://your-domain/install  to create the admin.

  The installer runs the migrations and creates your admin account.
EOF
