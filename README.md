# LoanApp 

This is a demo project where any user can apply for a loan. Once the admin approves the loan, the loan will be approved, and the repayment schedule will be created in a weekly manner.

The borrowe then can repay the loan. The borrower can repay only one repayment, or he can pay more than the repayment amount, and that will result in fully pay at least one loan and partially pay at most 1 account.

So If I have taken a loan of 50000 over 5 weeks, and I pay 25000, it will fully pay the first 2 loan repayment, and partially pay the 3rd one. 

So at the week 3, the use can choose to pay the remaining amount (in this example 5000)

Once all the payments are completed, that particular loan will be marked as paid.

This project has been made in laravel 8

You need to run composer install command to run the installation.

I have used FireBase JWT instead of passport for auth since JWT gives me more freedom to create customer middleware for multiple role.

## Project Setup
- Take a pull of the project from [Github]
- Go inside the project folder and run ``` composer install ```
- Configure the database, and email (if you want to test the rest password feature) in the .env
- Set up the JWT_KEY in .env that will be used to encode the authentication token. You can put any string as JWT_KEY
- Run php artisan migrate to set up the DB  ``` php artisan migrate ``` 
- Run the Role Seeder  ```  php artisan db:seed --class="RoleSeeder"  ``` 
- Run the User Seeder  ```  php artisan db:seed --class="UserSeeder"  ``` 

## Links
- Watch the demo video on [Youtube]
- Download the [Postman Collection]
- Download the [Postman Environment]



  [Github]: <https://github.com/pallabmandal/demo-loan-appr>
  [Youtube]: <https://youtu.be/jTsdCRnX2eI>
  [Postman Collection]: <https://drive.google.com/file/d/1Q5BQaZitqFcSZE808gOGcDJfen5fw8hd/view?usp=sharing>
  [Postman Environment]: <https://drive.google.com/file/d/15YKo7fyHuoafNHQW0yl_ctIi95MSzO0_/view?usp=sharing>