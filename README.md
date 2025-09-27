# Migration Guru

A Laravel 12 package to manage migrations from a simple **web UI**.  
It allows you to create, run, rollback, and delete migrations without touching the terminal.

---

## 📦 Installation

Require the package via Composer:

```bash
composer require nikelioum/migration-guru


Laravel will auto-discover the service provider.

🚀 Usage

Once installed, visit the following routes in your browser:

Dashboard (list migrations):
/migration-guru

Create new migration:
/migration-guru/create

Run a migration:
Trigger from the UI (button inside dashboard).

Rollback / Delete:
Trigger from the UI (buttons inside dashboard).

✨ Features

Web UI to manage migrations

Create migration scaffolds with fields

Run single or bulk migrations

Rollback or delete migrations

Supports auto-increment, nullable, varchar length, etc.

🔖 Versioning

The package follows Semantic Versioning
.
For example:

v1.0.0 – first stable release

v1.0.1 – bug fixes

v1.1.0 – new features