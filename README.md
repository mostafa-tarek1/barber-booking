<p align="center">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="300" alt="Laravel Logo">
</p>

<h2 align="center">💈 Barber Booking System</h2>
<p align="center">A web-based barber shop booking system built with Laravel.</p>

---

## 🚀 Features

-   Create and manage barber shop services (💇‍♂️ Haircut, 🧔 Beard, ✨ Skin care...).
-   Customers can book appointments online.
-   Barber/Admin can manage bookings via dashboard.
-   Authentication system for both admin and customers.
-   Email/WhatsApp notifications for bookings (optional).

---

## 🛠️ Tech Stack

-   **Backend:** Laravel 11 (PHP 8.1+)
-   **Database:** MySQL
-   **Frontend:** Blade + TailwindCSS (or Vue/React if needed)
-   **Auth:** Laravel Breeze / Jetstream / JWT

---

## ⚙️ Installation

1. Clone the repo:
    ```bash
    git clone https://github.com/yourusername/barber-booking.git
    cd barber-booking
    ```
2. Install dependencies:

    ```bash
    composer install
    npm install && npm run dev
    ```

3. Configure environment:
    ```bash
    cp .env.example .env
    ```
4. Run migrations:
    ```bash
    php artisan migrate --seed
    ```
5. Generate an API token:
    ```bash
    php artisan passport:install
    ```
6. Start the server:
    ```bash
    php artisan serve
    ```
