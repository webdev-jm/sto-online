## Introduction
STO ONLINE - for uploading sales and inventory online.

This web application is for the creation and management of order entries.

## Requirements

- PHP 8.0
- MySQL >= 5.7
- Laravel 8

## Packages
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
1. Download this project
- via git bash
```bash
git clone https://github.com/webdev-jm/sto-online.git
```
- or directly download files

2. go to the newly created folder then run.
```bash
composer update
```
and
```bash
npm install
```

3. Generate new .env file run the ff. in the console

```bash
cp .env.example .env
php artisan key:generate
```

4. Update database configurations at generated .env file

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

6. Run database seeders to setup permissions, roles and superadmin user

```bash
php artisan db:seed
```

7. Run this command to view the system.

```bash
php artisan serve
```
then go to http://127.0.0.1:8000 on the browser.

## Database Structure
Check the database Structure at [Sales Order Entry ERD](https://dbdiagram.io/d/STO-Online-6466d222dca9fb07c45ca6de) for your reference.
