# Laravel Article Publishing API

## 📌 Overview
This is a **Laravel REST API** for managing articles with **role-based access control**.  
It uses **Laravel Sanctum** for authentication and **Laravel Gates & Policies** for permissions.  
Admins can manage users and assign roles, while editors and authors can manage articles.

---

## 🚀 Features
- **User Authentication** (Laravel Sanctum)
- **Role-Based Access Control** (`admin`, `editor`, `author`, `guest`)
- **Article Management**
  - Create, read, update, delete articles
  - Publish workflow
- **User Management** (admin only)
- **Validation & Exception Handling**
- **JSON API Responses**

---

## 📥 Installation

### 1️⃣ Clone Repository
```bash
git clone https://github.com/your-username/your-project.git
cd your-project
```

### 2️⃣ Install Dependencies
```bash
composer install
```

### 3️⃣ Configure Environment
```bash
cp .env.example .env
php artisan key:generate
```
Edit `.env` with your database credentials:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 4️⃣ Migrate Database
```bash
php artisan migrate
```

### 5️⃣ Serve Application
```bash
php artisan serve
```
The API will be available at:
```
http://127.0.0.1:8000
```

---

## 🔑 Authentication
This API uses **Bearer Tokens** from **Laravel Sanctum**.  
Include the token in the `Authorization` header for protected routes:
```http
Authorization: Bearer your_api_token
```

---

## 📡 API Endpoints

### **Auth Routes**
| Method | Endpoint     | Description         | Auth Required |
|--------|-------------|--------------------|---------------|
| POST   | `/register` | Register new user  | ❌ |
| POST   | `/login`    | Login user         | ❌ |
| POST   | `/logout`   | Logout user        | ✅ |

#### Example: Register
**Request**
```json
POST /register
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "secret123"
}
```
**Response**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "admin"
  }
}
```

---

### **User Routes**
| Method | Endpoint     | Description            | Auth Required | Role |
|--------|-------------|-----------------------|---------------|------|
| GET    | `/profile`  | Get logged-in profile | ✅ | Any |
| GET    | `/users`    | List all users        | ✅ | Admin |
| POST   | `/users/{id}/assign-role` | Assign role to user | ✅ | Admin |

---

### **Article Routes**
| Method  | Endpoint | Description | Auth Required | Role |
|---------|----------|-------------|---------------|------|
| GET     | `/articles`        | List published articles | ✅ | Any |
| GET     | `/articles/mine`   | List user’s own articles | ✅ | Any |
| POST    | `/articles`        | Create article | ✅ | Author, Editor, Admin |
| PUT     | `/articles/{id}` | Update article | ✅ | Owner / Editor / Admin |
| DELETE  | `/articles/{id}` | Delete article | ✅ | Owner / Editor / Admin |
| PATCH   | `/articles/{id}/publish` | Publish article | ✅ | Editor / Admin |

---

## 🗂 Models

### **User**
- Fields: `id`, `name`, `email`, `role`, `password`
- Relationships:
  - `articles()` → `hasMany(Article)`
- Helper Methods:
  - `isAdmin()`, `isEditor()`, `isAuthor()`, `isGuest()`

### **Article**
- Fields: `id`, `user_id`, `title`, `content`, `is_published`
- Relationships:
  - `user()` → `belongsTo(User)`

---

## 🔒 Authorization

### **Policies**
- `ArticlePolicy` handles:
  - `create`, `update`, `delete`, `publish` permissions
- Bound in `AuthServiceProvider`

### **Gates**
Defined in `AuthServiceProvider`:
- `view-users` → Only Admins
- `assign-roles` → Only Admins
- `publish-article` → Admins & Editors
- `isAdmin` → Returns `true` (used in middleware)

---

## 🛠 Development Notes
- **First registered user** becomes **admin** automatically.
- Uses **ArticleRepositoryInterface** binding in `AppServiceProvider` for flexibility.

---

## 📄 License
This project is open-source and available under the [MIT License](LICENSE).
