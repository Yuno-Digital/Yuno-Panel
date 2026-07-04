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
ask()  { local p="$1" v=""; printf '%s' "$p" >/dev/tty; read -r v </dev/tty || v=""; printf '%s' "$v"; }

# Whether a domain resolves (A or AAAA) to one of this server's public IPs.
domain_points_here() {
    local domain="$1"
    if [ -n "$SERVER_IP4" ] && dig +short A "$domain" 2>/dev/null | grep -qxF "$SERVER_IP4"; then return 0; fi
    if [ -n "$SERVER_IP6" ] && dig +short AAAA "$domain" 2>/dev/null | grep -qxF "$SERVER_IP6"; then return 0; fi
    return 1
}

# Write and enable an nginx site for the panel, listening on IPv4 + IPv6.
write_nginx_site() {
    local domain="$1"
    cat >/tmp/yuno-site.conf <<'NGINX'
server {
    listen 80;
    listen [::]:80;
    server_name __DOMAIN__;
    root __ROOT__;
    index index.php;

    location / { try_files $uri $uri/ /index.php?$query_string; }
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:__FPM__;
    }
    location ~ /\.(?!well-known).* { deny all; }
}
NGINX
    sed -i "s#__DOMAIN__#${domain}#g; s#__ROOT__#${DIR}/public#g; s#__FPM__#/run/php/php${PHP}-fpm.sock#g" /tmp/yuno-site.conf
    $SUDO mv /tmp/yuno-site.conf "/etc/nginx/sites-available/${domain}.conf"
    $SUDO ln -sf "/etc/nginx/sites-available/${domain}.conf" "/etc/nginx/sites-enabled/${domain}.conf"
    [ -e /etc/nginx/sites-enabled/default ] && $SUDO rm -f /etc/nginx/sites-enabled/default
}

# Interactive nginx + Let's Encrypt setup: ask for a domain, verify DNS, then
# configure nginx and issue a certificate. Re-prompts on a mismatch.
setup_nginx() {
    if [ ! -e /dev/tty ]; then
        warn "No terminal available — skipping nginx/SSL setup (run 'bash install.sh' interactively for it)."
        return 0
    fi

    log "Installing nginx, PHP-FPM and certbot"
    $SUDO apt-get install -y nginx "php${PHP}-fpm" certbot python3-certbot-nginx dnsutils

    SERVER_IP4="$(curl -4 -fsSL --max-time 6 https://api.ipify.org 2>/dev/null || true)"
    SERVER_IP6="$(curl -6 -fsSL --max-time 6 https://api6.ipify.org 2>/dev/null || true)"
    log "This server's public IP — IPv4: ${SERVER_IP4:-none} · IPv6: ${SERVER_IP6:-none}"

    local domain=""
    while true; do
        domain="$(ask 'Domain for the panel (empty to skip nginx setup): ')"
        [ -z "$domain" ] && { warn "Skipping nginx/SSL setup."; return 0; }

        if domain_points_here "$domain"; then
            log "$domain points to this server — continuing."
            break
        fi

        warn "$domain does not resolve to this server."
        warn "  $domain → A: $(dig +short A "$domain" 2>/dev/null | tr '\n' ' ')AAAA: $(dig +short AAAA "$domain" 2>/dev/null | tr '\n' ' ')"
        warn "  Point the DNS record at this server, then try again."
        case "$(ask 'Enter a different domain? [Y/n]: ')" in [Nn]*) return 0 ;; esac
    done

    local email
    email="$(ask "Email for Let's Encrypt (empty = register without email): ")"

    write_nginx_site "$domain"
    if ! $SUDO nginx -t; then
        warn "nginx config test failed — skipping SSL."
        return 0
    fi
    $SUDO systemctl reload nginx

    log "Requesting a certificate for $domain"
    if [ -n "$email" ]; then
        $SUDO certbot --nginx -d "$domain" --non-interactive --agree-tos -m "$email" --redirect || warn "certbot failed — is port 80 open and DNS correct?"
    else
        $SUDO certbot --nginx -d "$domain" --non-interactive --agree-tos --register-unsafely-without-email --redirect || warn "certbot failed — is port 80 open and DNS correct?"
    fi

    # Point the app at the domain.
    if grep -q '^APP_URL=' .env; then
        sed -i "s#^APP_URL=.*#APP_URL=https://${domain}#" .env
    else
        echo "APP_URL=https://${domain}" >> .env
    fi
    php artisan config:clear >/dev/null 2>&1 || true

    PANEL_URL="https://${domain}"
}

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

# Optional: configure nginx + a Let's Encrypt certificate for a domain.
PANEL_URL=""
SERVER_IP4=""
SERVER_IP6=""
setup_nginx

log "Done!"
if [ -n "$PANEL_URL" ]; then
cat <<EOF

  Yuno Panel is installed in: $DIR
  nginx is configured and serving:  $PANEL_URL

  Open  $PANEL_URL/install  to run the migrations and create your admin account.
EOF
else
cat <<EOF

  Yuno Panel is installed in: $DIR

  Finish setup in your browser via the web installer:

    Quick test:   php artisan serve --host '[::]' --port 8000
                  then open  http://<server-ip>:8000/install

    ('[::]' listens on IPv6 and IPv4 — use it on IPv6-only servers;
     'http://[<ipv6>]:8000/install' works too.)

  For production, re-run and enter a domain when asked, or serve public/ with
  nginx + php-fpm yourself ('listen 80; listen [::]:80;').

  The installer runs the migrations and creates your admin account.
EOF
fi
