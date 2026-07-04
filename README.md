# Yuno Panel

Ein **Gameserver-Management-Panel** im Stil von [Pelican](https://pelican.dev) /
Pterodactyl, gebaut mit **Laravel 13**. Es verwaltet Gameserver-Instanzen auf
Nodes (Host-Maschinen) und steuert diese über den Node-Daemon
[Yuno Panel Wings](../Yuno-Panel-Wings) – mit User-Login, Admin-Bereich,
Egg-basierter Server-Erstellung, Live-Konsole und Dateimanager.

> Aktuelle Version: **0.1.1**

## Funktionen

### Für Nutzer (Server-Verwaltung)
- **Live-Konsole** über WebSocket (direkt zum Node-Daemon), mit Befehlseingabe
- **Power-Aktionen** (Start / Restart / Stop), zustandsabhängig aktiv
- **Installation / Reinstall** mit Live-Ausgabe direkt in der Konsole
- **Startup-Tab** – Startup-Command und Docker-Image wählen, Variablen bearbeiten,
  Live-Preview des aufgelösten Befehls
- **Dateimanager** – Tabelle mit Name/Größe/Datum, Ordner zuerst, Suche,
  Typ-Icons, Mehrfachauswahl & Löschen, Editor als Modal mit
  **Monaco (VS-Code-Editor)**; Binärdateien lassen sich nicht öffnen
- Deep-linkbare Tabs (`/servers/{id}/{tab}`) – Reload landet im selben Tab

### Admin-Bereich (`/admin`)
- **Overview** – systemweite Kennzahlen, Panel-Version, Update-Check + Changelog
  und **funktionierendes Panel-Update** per Button (oder `php artisan yuno:upgrade`)
- **Nodes** – CRUD, Allocations, automatische RAM/Disk-Erkennung, Online-Status,
  panel-eigene Tokens & **Auto-Deploy-Befehl** (`wings configure`)
- **Servers** – Egg-basierte Erstellung (Image, Startup, Variablen, Allocation)
- **Users** – CRUD, Admin-Flag
- **Eggs** – volles Pelican-Schema, **Egg-Import**, Startup-Commands & Variablen
- **Settings** und **API-Keys** (Application Keys)

### Konto & Sicherheit
- Auth via Laravel Breeze (Blade), **2FA (TOTP)** mit herunterladbaren Recovery-Codes
- Profil mit Tabs, **Theme** (Light / Dark / System), Client-**API-Keys**

## Tech-Stack

- **PHP 8.3+ / Laravel 13**, Laravel Breeze (Blade) fürs Auth-Scaffolding
- **Tailwind CSS v3** (`darkMode: 'class'`) + Vite, **Alpine.js**
- **SweetAlert2** (Dialoge/Toasts), **Monaco Editor** (per CDN, für den Datei-Editor)
- **SQLite** (Entwicklung) – MySQL/MariaDB für Produktion vorgesehen
- 2FA via `pragmarx/google2fa-qrcode`

## Datenmodell

- **User** – Panel-Nutzer, `is_admin`-Flag, Theme-Präferenz, 2FA-Felder
- **Node** – Host-Maschine (FQDN, Daemon-Port/-Token, erkanntes RAM/Disk, Online-Status)
- **Allocation** – IP/Port-Zuordnung eines Nodes, an Server bindbar
- **Server** – Gameserver-Instanz (Node, Owner, Egg, Allocation, Docker-Image,
  Startup, RAM/Disk/CPU, öffentliche UUID)
- **Egg** / **EggVariable** – Gameserver-Vorlage (Docker-Images, Startup-Commands,
  Install-Skript, Variablen) im Pelican-Format
- **ApiKey** – Client- und Application-Keys · **Setting** – Panel-Einstellungen

## Schnellinstallation (Debian/Ubuntu)

Installiert Abhängigkeiten (PHP 8.3, Composer, Node), klont das Panel und richtet
alles ein. Danach die Einrichtung im Browser über `/install` abschließen:

```bash
curl -fsSL https://raw.githubusercontent.com/Yuno-Digital/Yuno-Panel/main/install.sh | bash
# oder ein Zielverzeichnis angeben:  bash install.sh /var/www/yuno-panel
```

## Setup (manuell)

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build                 # oder: npm run dev
php artisan serve
```

Panel danach unter http://127.0.0.1:8000

> **Assets:** Nach dem Ziehen von UI-Änderungen, die neue Tailwind-Klassen
> einführen, `npm run build` ausführen (`public/build` ist gitignored). Reine
> PHP-/Routing-Änderungen brauchen das nicht.

### Login (aus dem Seeder)

Es wird nur ein Admin-Konto angelegt (keine Demo-Daten):

| E-Mail            | Passwort   | Rolle |
|-------------------|------------|-------|
| admin@yuno.local  | `password` | Admin |

## Node anbinden (Wings)

1. Im Panel unter **Admin → Nodes** einen Node anlegen (FQDN + Daemon-Port).
2. Unter **Auto Deploy** den `wings configure`-Befehl kopieren und auf dem Host
   ausführen – der Daemon holt sich Config **inkl. Token** vom Panel.
3. Daemon starten. Das Panel erkennt RAM/Disk und den Online-Status automatisch.

Details im [Wings-README](../Yuno-Panel-Wings).

## Entwicklung

```bash
vendor/bin/pint        # Code-Style
php artisan test       # Tests
```

`main` ist geschützt: Änderungen laufen über Pull Requests mit grüner CI.
