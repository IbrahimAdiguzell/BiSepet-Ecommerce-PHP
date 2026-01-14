# 🛒 BiSepet - Full Stack E-Commerce Web Application

![Project Status](https://img.shields.io/badge/status-completed-success)
![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5-7952B3?logo=bootstrap&logoColor=white)

BiSepet is a robust, secure, and modern e-commerce platform built from scratch using native PHP and MySQL. It features a comprehensive customer frontend and a powerful admin dashboard for inventory and order management.

## 🌟 Features

### 🛍️ Customer Frontend
* **User Authentication:** Secure Login/Register system with password hashing.
* **Product Catalog:** Browse products by categories with dynamic filtering.
* **Shopping Cart:** Session-based cart management (Add, Remove, Update Quantity).
* **Checkout System:** Multi-step checkout process with order validation.
* **Responsive Design:** Fully mobile-compatible UI using Bootstrap 5.

### 🛡️ Admin Dashboard (CMS)
* **Dashboard Analytics:** Real-time stats (Total Revenue, Orders, Low Stock Alerts).
* **Product Management (CRUD):** Add, Edit, and Delete products with image upload support.
* **Order Fulfillment:** View order details, print invoices, and update order status (Preparing -> Shipped -> Delivered).
* **Vendor Management:** Approve or reject seller applications.
* **Security:** Protected by Role-Based Access Control (RBAC) and SQL Injection protection.

## 🛠️ Tech Stack

* **Backend:** PHP (Native, Object-Oriented style)
* **Database:** MySQL (Relational Design)
* **Frontend:** HTML5, CSS3, JavaScript, Bootstrap 5
* **Security:** Prepared Statements (MySQLi), XSS Protection, Bcrypt Hashing, Session Management
* **Tools:** Chart.js (for Analytics), JSON API (for Data Export)

## 🚀 Installation & Setup

1.  **Clone the Repo**
    ```bash
    git clone [https://github.com/YOUR_USERNAME/BiSepet-Ecommerce-PHP.git](https://github.com/YOUR_USERNAME/BiSepet-Ecommerce-PHP.git)
    ```

2.  **Database Setup**
    * Create a database named `bisepet` in phpMyAdmin.
    * Import the `bisepet.sql` file provided in the root directory.

3.  **Configure Connection**
    * Open `db.php` and update your database credentials if necessary:
    ```php
    $servername = "localhost";
    $username = "root";
    $password = ""; // Your DB Password
    $dbname = "bisepet";
    ```

4.  **Run**
    * Start your local server (Apache/XAMPP).
    * Visit `http://localhost/BiSepet-Ecommerce-PHP` in your browser.

## 📸 Screenshots

*(Buraya projenin ekran görüntülerini eklersen harika olur. GitHub'da Issues kısmına resim sürükleyip linkini buraya yapıştırabilirsin.)*

## 📄 License

This project is open-source and available under the [MIT License](LICENSE).

---
*Developed by [Ibo](https://github.com/YOUR_USERNAME)*
