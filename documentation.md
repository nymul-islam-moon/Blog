# AP — Laravel 10 Backend (Sanctum + Roles + Policies)

**Minimum versions enforced**
- PHP: **>= 8.1** (project developed on 8.3)
- Laravel: **>= 10.x**
- DB: MySQL 8.x (tested on 8.0.42)

`composer.json` (key parts):
```json
{
  "require": {
    "php": "^8.1",
    "laravel/framework": "^10.0"
  }
}
```

## Quick start

```bash
# clone your repo, then:
cp .env.example .env
php artisan key:generate

# set DB in .env (example)
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=laravel10
DB_USERNAME=nymul
DB_PASSWORD=12345678

php artisan migrate --force
php artisan db:seed --force
php artisan serve
```

Auth is handled by **Laravel Sanctum**. Roles are stored in `roles` (many-to-many with users via `role_user`). Access is enforced using **Policies** (`ArticlePolicy`) + a thin `role` middleware for admin-only routes.

---

## RBAC Truth Table

| Capability / Endpoint                                       | Admin | Editor | Author |
|---|:--:|:--:|:--:|
| Auth — Register (`POST /api/register`)                       | ✅ | ✅ | ✅ |
| Auth — Login/Logout/Me                                       | ✅ | ✅ | ✅ |
| Users — All Users (`GET /api/users`)                         | ✅ | ❌ | ❌ |
| Users — Assign Role (`POST /api/users/{id}/assign-role`)   | ✅ | ❌ | ❌ |
| Articles — List (`GET /api/articles`) *(scope varies)*       | ✅ | ✅ (all) | ✅ (published + own) |
| Articles — Show (`GET /api/articles/{id}`)                 | ✅ | ✅ (any) | ✅ (published or own) |
| Articles — Create (`POST /api/articles`)                     | ✅ | ✅ | ✅ |
| Articles — Update (`PUT/PATCH /api/articles/{id}`)         | ✅ | ✅ (any) | ✅ (own **draft** only) |
| Articles — Publish (`POST /api/articles/{id}/publish`)     | ✅ | ✅ | ❌ |
| Articles — Delete (`DELETE /api/articles/{id}`)            | ✅ | ✅ (any) | ✅ (own **draft** only) |

**Seeded users & roles (db:seed):**
- Admin: `admin@example.com` / `12345678` → role: **admin**

**Proof of concept (sample)**
- *Users — All Users* (admin only): `GET /api/users` → `200 OK` (others `403`)
- *Users — Assign Role* (admin only): `POST /api/users/{user_id}/assign-role` body `{ "role": "editor" }` → `200 OK`
- *Articles — Publish* (editor/admin): `POST /api/articles/{id}/publish` → `200 OK` (`author` → `403`)
- *Articles — Update* (author, own **draft**): `PUT /api/articles/{id}` → `200 OK` (own **published** → `403`)

---

## API Endpoints & Sample Payloads

### Auth (Public)
**Request name:** Auth — Register  
**POST** `/api/register`  
Body:
```json
{
  "name": "Alice Author",
  "email": "alice@example.com",
  "password": "secret1234",
  "password_confirmation": "secret1234"
}
```
Response `201`:
```json
{
  "user": { "id": 2, "name": "Alice Author", "email": "alice@example.com" },
  "token": "SANCTUM_TOKEN_HERE"
}
```

**Request name:** Auth — Login  
**POST** `/api/login`  
Body:
```json
{ "email": "admin@example.com", "password": "12345678" }
```
Response `200`:
```json
{
  "user": { "id": 1, "name": "Admin", "email": "admin@example.com" },
  "token": "SANCTUM_TOKEN_HERE"
}
```

**Request name:** Auth — Logout  
**POST** `/api/logout` *(Bearer token required)*  
Response `200`:
```json
{ "message": "Logged out" }
```

**Request name:** Auth — Me  
**GET** `/api/me` *(Bearer token required)*  
Response `200`: current user JSON.

---

### Users (Admin-only, `role:admin`)

**Request name:** Users — All Users  
**GET** `/api/users` *(Bearer token required)*  
Response `200` (paginated): users with `roles`.

**Request name:** Users — Assign Role  
**POST** `/api/users/{user_id}/assign-role` *(Bearer token required)*  
Body:
```json
{ "role": "admin|editor|author" }
```
Response `200` (updated user with roles).

---

### Articles (Authenticated, policy-enforced)

**Request name:** Articles — List  
**GET** `/api/articles?per_page=10` *(Bearer token required)*  
- Admin/Editor: all; Author: published + own.

**Request name:** Articles — Show  
**GET** `/api/articles/{id}` *(Bearer token required)*

**Request name:** Articles — Create  
**POST** `/api/articles` *(Bearer token required)*  
Body:
```json
{ "title": "My first article", "body": "Draft body" }
```
Response `201`: article with `status: "draft"`.

**Request name:** Articles — Update  
**PUT** `/api/articles/{id}` *(Bearer token required)*  
Body (any subset):
```json
{ "title": "Updated title", "body": "Updated body" }
```
- Author: only own **draft**; Editor/Admin: any.

**Request name:** Articles — Publish  
**POST** `/api/articles/{id}/publish` *(Bearer token required)*  
- Editor/Admin only. Response `200` with `status: "published"`.

**Request name:** Articles — Delete  
**DELETE** `/api/articles/{id}` *(Bearer token required)*  
- Author: own **draft** only; Editor/Admin: any. Response `200`.

---

## Postman

Import the two files provided with this repo:

- Collection: `AP.postman_collection.json`
- Environment: `AP.postman_environment.json`

Set **AP** as the active environment. Variables:
- `base_url` → `http://127.0.0.1:8000`
- `token` → filled by the *Auth — Login* test script after you log in
- `new_user_id` → optional (filled by *Auth — Register* test script)
- `article_id` → optional (filled by *Articles — Create* test script)

### Order of testing (suggested)
1. **Auth — Login** (admin) → saves `{token}`
2. **Users — All Users**, **Users — Assign Role** (admin-only)
3. **Articles — Create** → saves `{article_id}`
4. **Articles — Publish** (with admin/editor)
5. **Articles — Update/Delete** (role rules apply)

---

## Seeding / Roles

- Seeder: `RoleSeeder` creates roles (`admin`, `editor`, `author`) and an initial admin user (`admin@example.com` / `12345678`).  
- Models: `User` has `roles()` and helpers `hasRole()` and `assignRole()`; `Role` has `users()`.
- Middleware: `role` (for admin-only routes).
- Policy: `ArticlePolicy` encapsulates all permissions; `before()` allows admin to bypass checks.

---

## GitHub — files to commit

- `documentation.md` (this file)
- `AP.postman_collection.json`
- `AP.postman_environment.json`

```bash
git add documentation.md AP.postman_collection.json AP.postman_environment.json
git commit -m "Docs: RBAC truth table, API list, Postman collection & environment"
git push origin <your-branch>
```
---

## Repository Pattern

This project uses the Repository pattern for the Articles module.

**Contracts & Implementation**
- `app/Repositories/Contracts/ArticleRepository.php` — interface defining the Article data operations.
- `app/Repositories/Eloquent/EloquentArticleRepository.php` — Eloquent-based implementation.

**Service Container Binding**
- `app/Providers/RepositoryServiceProvider.php`
  ```php
  public function register(): void
  {
      $this->app->bind(
          \App\Repositories\Contracts\ArticleRepository::class,
          \App\Repositories\Eloquent\EloquentArticleRepository::class
      );
  }
  ```
- Registered in `config/app.php` providers array:
  ```php
  App\Providers\RepositoryServiceProvider::class,
  ```

**Controller Dependency Injection**
- `app/Http/Controllers/Api/ArticleController.php`
  ```php
  public function __construct(private ArticleRepository $articles) {}
  ```
  The controller delegates all data operations to the repository while **policies** still enforce permissions.

**Why this matters**
- Clear separation of concerns: controllers stay thin, business rules live in policies, data access in repositories.
- Easy to swap implementations (e.g., cache, external service) without changing controllers.
