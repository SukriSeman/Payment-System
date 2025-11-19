# Payment & Order Processing System

A Laravel-based backend system that provides authentication, order management, payment processing, refunds, daily settlement reconciliation, and Horizon-powered queues.  
The project uses a clean architecture with **Repositories + Services**, **Laravel Sanctum**, **API resources**, and **Docker** for consistent local development.

---

## Features

### **Authentication**
- Login (token-based via Laravel Sanctum)
- Logout
- Protected API routes

### **Orders**
- Create order
- Update order status
- Create order items

### **Payments**
- Create payment
- Capture, void, refund operations
- Daily settlement generation

### **Refunds**
- Full & partial refunds
- Queue-based asynchronous processing

### **Daily Settlement**
- Summary aggregation of payments by status (Authorized, Captured, Voided, Refunded)

### **Architecture**
- Repository Pattern
- Service Layer
- Resource Collections
- Docker-based setup
- PHP 8.3 compatible

---

## Local Development (Docker)

> **Important**: The `vendor/` folder is excluded from mounting because the host OS (Windows/macOS/Linux) may conflict with container permissions.  
Composer MUST be run locally, not inside Docker.

---

## Docker Port Requirements

This project uses the following ports:

| Service | Port |
|---------|------|
| Nginx / App | **80** |
| MySQL | **3306** |

> **Important:** Before running Docker, ensure ports **80** and **3306** are not used by other applications on your machine (e.g., IIS, XAMPP, WAMP, MySQL Workbench services, Skype, etc.).


---

## Installation Steps

### **1. Clone the repo**
```sh
git clone https://github.com/SukriSeman/Payment-System.git
cd Payment-System
```

### **2. Clone the repo**
```sh
cp .env.example .env
```

### **3. Install Composer dependencies**
#### Windows
- Windows cannot use ext-pcntl and ext-posix, so ignore them:
```sh
composer install --ignore-platform-req=ext-pcntl --ignore-platform-req=ext-posix
```

#### Linux/macOS
```sh
composer install
```

### **4. Generate application key**
```sh
php artisan key:generate
```

### **5. Start Docker containers**
```sh
docker compose up -d --build
```

### **6. Run database migrations**
```sh
docker compose exec app php artisan migrate
```

### **7. Seed the database**
```sh
docker compose exec app php artisan db::seed
```
--- 

## Tests
This project includes:
- Unit Tests
    - PaymentServiceTest
    - OrderServiceTest
    - RefundServiceTest
    - DailySettlementServiceTest
    - ApiTest

Run all tests:
```sh
docker compose exec app php artisan test
```

--- 

## API Documentation (Postman)
API documentation is available in:
```
/docs/api/
```

Generated via Postman Documentation Export.

You may import the JSON file into Postman.

--- 

# **Architectural Decisions**

---

### **1. Sanctum for Authentication**

I selected **Laravel Sanctum** because:

* it provides simple token-based authentication
* lightweight compared to Passport
* fits API-only backend use cases

---

### **2. Horizon for Queue Processing**

Refunds are processed asynchronously using **Laravel Horizon**.

**Reason:**
Refund operations can be long-running or involve external systems in real-world usage.
Horizon also gives a clean dashboard and retry logic out of the box.

---

### **3. Docker for Local Development**

Docker ensures:

* consistent PHP version (8.3)
* consistent MySQL version
* same environment regardless of host OS (Windows/Linux/macOS)

Vendor folder is not mounted because different host systems cause permission conflicts.

---

---

# **Trade-offs**

Here are the trade-offs you realistically made:

### **1. No external payment gateway integration**

Instead of integrating with Stripe/PayPal, payment status transitions are internal.
**Trade-off:** Reduced realism but faster development and testability.

---

### **2. No UI for Horizon & Admin**

Only API responses are provided.
**Trade-off:** Faster backend development, UI not required for assignment.

---

### **3. Using Sanctum instead of Passport**

Sanctum is simpler but:

* lacks built-in OAuth flows
* no refresh tokens

---

### **4. Some processes (refund success/failure) are simulated**

A real system involves external API callbacks.
Here, the job updates the status automatically.

---

### **5. Tests focus on core logic, not full E2E**

Unit + Integration tests done, but not full browser-based E2E tests.
**Trade-off:** Faster delivery, assignment does not require browser-level tests.

---

---

# **Time Spent**

| Task                                        | Duration                 |
| ------------------------------------------- | ------------------------ |
| Project setup (Docker, Sanctum, migrations) | ~2 hours                 |
| Repositories + Services                     | ~1 hours                 |
| Payments, Refunds, Settlement logic         | ~4 hours                 |
| Writing tests                               | ~3 hours                 |
| Documentation & cleanup                     | ~1 hour                  |
| **Total time spent**                        | **~1 day (10–12 hours)** |
