# homework-management-system

This is a homework managment system with only api for the following modules only.
1. Student
2. Teacher

# Setup
Clone the repo or download the zip file and extract the code
```
git clone https://github.com/MarcusLEK/homework-management-system.git
```
Create a database on your local called `laravel` and connect to it

CD into the code root directory and run the following command:

<b>Note: If you are not using virtual host, please ensure `php artisan serve` is running at all times during development/testing on a seperate terminal</b>
```
composer install
npm install
php artisan migrate:fresh
php artisan db:seed
php artisan serve
```

Optionally, you can run the seeder for the homework with the following command:
```
php artisan db:seed --class=HomeworkSeeder
```