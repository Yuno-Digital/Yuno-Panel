# Yuno Panel

Ein Gameserver-Management-Panel (im Stil von [Pelican](https://pelican.dev) /
Pterodactyl), gebaut mit **Laravel 13**. Verwaltet Gameserver-Instanzen auf
Nodes (Host-Maschinen), mit User-Login und Dashboard.

> Status: **Grundgerüst** – Login, Dashboard mit Kennzahlen und Server-Übersicht
> laufen. Daemon-Integration, Server-Steuerung (Start/Stop/Konsole), Dateimanager
> und Admin-CRUD folgen.

## Tech-Stack

- PHP 8.5 / Laravel 13
- Laravel Breeze (Blade) für Auth-Scaffolding
- Tailwind CSS + Vite
- SQLite (Entwicklung) – MySQL/MariaDB für Produktion vorgesehen

## Datenmodell

- **User** – Panel-Nutzer, `is_admin`-Flag für Admin-Rechte
- **Node** – Host-Maschine, auf der Gameserver laufen (FQDN, RAM, Disk, Daemon-Port)
- **Server** – einzelne Gameserver-Instanz; gehört zu einem Node und einem User
  (Owner), mit Status, RAM/Disk-Limits, Port und öffentlicher UUID

## Setup

```bash
composer install
npm install
php artisan key:generate
php artisan migrate --seed
npm run build                 # oder: npm run dev
php artisan serve
```

Panel danach unter http://127.0.0.1:8000

### Demo-Login (aus dem Seeder)

| E-Mail            | Passwort   | Rolle |
|-------------------|------------|-------|
| admin@yuno.local  | `password` | Admin |

## Admin-Bereich

Unter `/admin` (nur für User mit `is_admin`, abgesichert per `admin`-Middleware):

- **Overview** – systemweite Kennzahlen (User, Nodes, Server)
- **Nodes / Servers / Users** – vollständiges CRUD (anlegen, bearbeiten, löschen)

## Nächste Schritte (Roadmap)

1. ~~Admin-Bereich: CRUD für Nodes, Server und User~~ ✅
2. Server-Detailseite mit Aktionen (Start / Stop / Restart)
3. Daemon/Agent auf den Nodes (Wings-Äquivalent) + API-Kommunikation
4. Live-Konsole & Ressourcen-Statistiken (WebSockets)
5. Dateimanager, Backups, geplante Tasks
6. Rollen-/Rechtesystem (Permissions), Subuser
