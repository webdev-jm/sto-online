## Introduction
STO ONLINE offers a robust solution for efficiently managing sales and inventory through an online platform. This web application specializes in creating and overseeing order entries.

## Requirements

- PHP 8.0
- MySQLversion 5.7 or higher
- Laravel 8

## Packages
STO ONLINE leverages various Laravel packages to enhance its functionality:

-   jeroennoten/laravel-adminlte ^3.8
-   laravelcollective/html ^6.4
-   livewire/livewire ^2.12
-   maatwebsite/excel ^3.1
-   phpoffice/phpspreadsheet ^1.29
-   rap2hpoutre/laravel-log-viewer ^2.3
-   silviolleite/laravelpwa ^2.0
-   spatie/laravel-activitylog ^3.17
-   spatie/laravel-permission ^5.10

## Installation
To set up STO ONLINE on your local environment, follow these steps:

1. Download the project using git bash:
```bash
git clone https://github.com/webdev-jm/sto-online.git
```
Alternatively, you can directly download the files.

2. Navigate to the newly created folder and run the following commands in the console:
```bash
composer update
npm install
```

3. Generate a new .env file:

```bash
cp .env.example .env
php artisan key:generate
```

4. Update the database configurations in the generated .env file:
```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=(database_name)
DB_USERNAME=root
DB_PASSWORD=
```

5. Run the migration
```bash
php artisan migrate
```

6. Seed the database with permissions, roles, and a superadmin user:
```bash
php artisan db:seed
```

7. View the system by running the following command:
```bash
php artisan serve
```
Then, access the system through http://127.0.0.1:8000 in your browser.

## Database Structure
For a comprehensive understanding of the database structure, refer to the [STO ONLINE ERD](https://dbdiagram.io/d/STO-Online-6466d222dca9fb07c45ca6de) provided for your reference. This ERD illustrates the relationships and entities within the database, aiding in the effective utilization of STO ONLINE's features.
