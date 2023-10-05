# Car Rental Website  
## Tech Stack  
Framework: Laravel 8  
PHP: 7.4  
MYSQL: 5.7
## User Account
For **Admin**:  

Email: **eri@email.com**  
Password: **eri_backend**  

For **Approval User**:

Email: **tes@email.com**  
Password: **12345678** 
## Instalation Process
1. Clone this repository to your local environment
2. After that, update composer package
```bash
composer install
```
3. Copy env.example file into .env and set database connection into your own
4. Generate key for encryption purposes
```bash
php artisan key:generate
```
5. Run a migration and database seed to fill the database
```bash
php artisan migrate:fresh --seed
```
6. Application is ready to use
## Instruction 
1. First you have to login as admin or approval user. Note that only admin can insert rental request
2. As an admin, you can insert a rental request by filling ***car, driver, date and approval user***. Note that system will check if the car and driver is available at given date. 
3. After insert rental request, approval user can approve or reject the request. Admin user can also override the approval process.
4. If all approval user is approved the request, rental request will be active and admin can mark it as "***Returned***" if car is returned.
