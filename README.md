## ProjetWeb2A35 (EduMatch) — Setup Instructions

Quick steps to get this project running on a teammate's machine.

Requirements
- PHP 8.2
- MySQL (or MariaDB)
- Composer
- XAMPP (Windows) or equivalent LAMP stack
- Recommended PHP extensions: `pdo_mysql`, `mbstring`, `ctype`, `openssl`, `fileinfo`
- (Optional) Poppler `pdftotext` if you use CV PDF extraction

Setup
1. Clone the repository and go to the project folder:

```bash
git clone <repo-url>
cd ProjetWeb2A35-git
```

2. Copy environment example and fill values (do not commit `.env`):

```bash
cp .env.example .env
# Edit .env and set DB credentials, API keys, etc.
```

3. Install PHP dependencies:

```bash
composer install
```

Notes:
- `vendor/` is git-ignored. Dependencies are installed by `composer install`.
- `composer.lock` is committed to the repo to ensure reproducible installs.

Database
- Create the database named in `.env` (`DB_NAME`). If you have a SQL dump, import it with:

```bash
mysql -u root -p DB_NAME < path/to/dump.sql
```

- If no dump is available, create an empty database and run any internal setup scripts.

Permissions
- Ensure `uploads/` (especially `uploads/cv/`) is writable by the web server user.

Running locally (XAMPP)
- Start Apache and MySQL in XAMPP.
- Place the project folder under `htdocs` and visit:

http://localhost/ProjetWeb2A35-git/  (or adjust to match your virtual host)

Extras
- If the project uses PDF CV extraction, install Poppler and ensure `pdftotext` is on PATH.
- If you remove `vlucas/phpdotenv` (not recommended), update code that reads env values; otherwise keep it and run `composer install`.

If you want, I can add an SQL dump (if you provide it) or implement a small installer script to create tables automatically.
