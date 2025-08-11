# AP — Laravel 10 Backend (Sanctum + Roles + Policies)

A clean Laravel 10 API with authentication, role-based access control (RBAC) via **policies**, and an Articles module (CRUD + publish). Includes a full **Postman collection & environment** and a comprehensive **documentation.md**.

## Requirements
- PHP **>= 8.1** (developed on 8.3)
- Composer **2.x**
- MySQL **8.x** (tested on 8.0.42)
- Node **20.x** (only if you later add front-end assets)
- Ubuntu-friendly (works anywhere, but steps below show Ubuntu examples)

## Quick Start
```bash
# 1) Install dependencies
composer install

# 2) Environment
cp .env.example .env
php artisan key:generate

# 3) Configure DB (edit .env)
# Example:
# DB_CONNECTION=mysql
# DB_HOST=localhost
# DB_PORT=3306
# DB_DATABASE=laravel10
# DB_USERNAME=nymul
# DB_PASSWORD=12345678

# 4) Migrate & seed (creates roles + an admin user)
php artisan migrate --force
php artisan db:seed --force

# 5) Run the dev server
php artisan serve
# → http://127.0.0.1:8000
```

### Seeded Admin
- Email: `admin@example.com`
- Password: `12345678`

## Authentication
This project uses **Laravel Sanctum**.
- `POST /api/login` → returns a token.
- Include the token in subsequent requests:
  - Header: `Authorization: Bearer <token>`

### Quick login (curl)
```bash
curl -s {{base_url:-http://127.0.0.1:8000}}/api/login   -H "Content-Type: application/json"   -d '{"email":"admin@example.com","password":"12345678"}'
```

## RBAC Overview (roles: `admin`, `editor`, `author`)
- **Admin**: full access (policy `before()` shortcut).
- **Editor**: manage any article; can publish.
- **Author**: can create; can update/delete **own drafts**; cannot publish.

For the full **RBAC truth table** and sample request/response bodies, see **documentation.md**.

## API Endpoints
All endpoints, sample payloads, and expected responses are listed in **documentation.md**.

## Postman
Import both files:
- `AP.postman_collection.json`
- `AP.postman_environment.json`

Set the **AP** environment active, send **Auth — Login (admin)** to populate `{{token}}`, then use the rest of the requests.

## Repository Pattern
Articles are implemented using the Repository pattern.
- Interface: `app/Repositories/Contracts/ArticleRepository.php`
- Implementation: `app/Repositories/Eloquent/EloquentArticleRepository.php`
- Binding: `app/Providers/RepositoryServiceProvider.php`
- Controller DI: `app/Http/Controllers/Api/ArticleController.php`

## Code Map
- Roles & users: `roles`, `role_user` tables; relations in `User` & `Role` models.
- Middleware: `role` alias for admin-only routes.
- Policies: `app/Policies/ArticlePolicy.php` (create/update/delete/publish rules).
- Seeders: `database/seeders/RoleSeeder.php` (creates roles and the admin user).
- Routes: `routes/api.php` (auth, users, articles).

## Troubleshooting
**1) PDO driver: "could not find driver"**
```bash
sudo apt install -y php8.3-mysql
php -m | grep -Ei 'pdo|mysql'   # expect: PDO, pdo_mysql, mysqli
php artisan config:clear
```

**2) MySQL login 1045**
```bash
# Create user bound to localhost and grant DB
sudo mysql -e "CREATE USER IF NOT EXISTS 'nymul'@'localhost' IDENTIFIED BY '12345678'; GRANT ALL PRIVILEGES ON laravel10.* TO 'nymul'@'localhost'; FLUSH PRIVILEGES;"
```

**3) Token not applied in Postman**
Set the **AP** environment active, then run **Auth — Login (admin)**. The test script saves `{{token}}`; use `Authorization: Bearer {{token}}` on protected requests.

## Contributing
- Create a feature branch, run `php artisan test` (if/when tests are added), then open a PR.
- Please keep `documentation.md` and the Postman files in sync with any API changes.

## License
MIT (or your organization’s preferred license).
