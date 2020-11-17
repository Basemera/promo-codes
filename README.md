# PromoCodes

## Requirements

[`PHP 7.2`](http://php.net/manual/en/install.php) - This version of Laravel uses PHP 7.2

[`Composer`](https://getcomposer.org/) - Composer is required for the libraries and dependencies

## Clone 
```git clone https://github.com/Basemera/promo-codes.git```

## Installation

Install all the required libraries from Composer while in the promocodes folder
```
composer install
```
For the app to connect to you local database, you need to create a `.env` file on the root of your project.

To do that, copy the `.env.example` and rename it to `.env`, and then fill in the
necessary configurations as shown below
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=8889
DB_DATABASE=<database name>
DB_USERNAME=<database username>
DB_PASSWORD=<database password>

```

Generate an Artisan Key
```
php artisan key:generate
```

Run migrations to create tables.
```
php artisan migrate
```


Start the server.
```
php artisan serve
```


Run tests.
```
php artisan test
```

# Endpoints

| Method | Endpoint                                              | Description                  | Access          |
|--------|-------------------------------------------------------|------------------------------|-----------------|
| POST   | /api/promocodes/create                                | Creates a promo code         | all users       |
| POST   | /api/promocodes/deactivate                            | Deactivates a promocode      | all users       |
| POST   | /api/promocodes/activate                              | Activates a promo code       | all users       |
| POST    | /api/promocodes/valid                                 | Returns details of valid promo code|all users        |
| GET    | /api/promocodes/promocodes                            | Returns all promocodes       | all user s   |
| GET    | /api/promocodes/promocodes?q=active                   | Returns all active promocodes| all user s   |

