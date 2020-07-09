

## About this Mini Transfer Api

 This is a mini transfer Api that for a demo mobile app
 
 
 HOW TO USE
 - Clone this application and install all dependencies using composer install
 - Then create a database with the same name as that in the env file, make sure the username and password of your database are correct
 - The run php artisan migrate to run the migration files
 - Then start the application using this command (php -S localhost:9097 -t public)
 
 HOW THE API WORKS
 
 First you need to register via this endpoint - http://localhost:9097/api/register 
 <br/>
 You have to pass the follow parameters to the register endpoint
 - firstname
 - lastname
 - password
 - email
 
 On the process of registration an unique account number is generated for you with a free account balance of 100 credits, After which you will be automatically logged in and a token will be generated for you
 <br/>
 If you have registered before you can log in via this endpoint - http://localhost:9097/api/login
 <br/>
 
  You have to pass the follow parameters to the login endpoint
  - password
  - email
  
  This mini transfer Api allows you to perform the following
  - Check your account balance
  -  Transfer Funds
  -  Check transaction history
  -  Fund your account  
  
  To check your account balance you make a get request to this end point passing your token generated during login via this url
  http://localhost:9097/api/checkAccountBalance?token=enter_token_generated_during_login
  
  <br/>
  
  To transfer funds you need to make a post request to this url http://localhost:9097/api/transferFunds
  <br/>
  
   You have to pass the follow parameters to the transfer funds endpoint
   - amount - an integer or double value
   - token -  token generated during login
   - to   - the account number you want to send fund into - note the account number must exist on the platform
   
   
   To check transaction history you have to make a get request to this end point passing the token generated at login
   http://localhost:9097/api/transactionHistory?token=token_generated_during_login
   
   To fund your account you have to make a post request to this url
   http://localhost:9097/api/fundAccount
   
   You have to pass the follow parameters to the transfer funds endpoint
   - amount - an integer or double value
   - token -  token generated during login
      
   
 
