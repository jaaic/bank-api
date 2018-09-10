## Bank Api

A REST API to transfer funds from the sender's account to receiver's account using lumen

## Pre-requisities
1. PHP >= 7.1.3
2. Install composer https://getcomposer.org/
3. Composer global require "laravel/lumen-installer"

Check the laravel 5.6 installtion https://lumen.laravel.com/docs/5.6

### How to setup?
1. clone the code to your machine
```
$ git clone git@github.com:jaaic/bank-api.git
```
2. Add correct MYSQL settings to .env file
3. Install dependencies
```
$ composer install
```
4. Create MYSQL database 
```
CREATE DATABASE bank_db;

```  
5. Create tables and seed data
```
php artisan migrate

php artisan db:seed --class=BalancesTableSeeder

``` 

### Invoke api
From the account numbers seeded into 'balances' table, take any 2 account numbers, example - A5b96316020e01, A5b96316025196 and the amount to transfer

Curl
```
curl -X POST \
  http://bank-api.tajawal.local:8080/transfer \
  -H 'Cache-Control: no-cache' \
  -H 'Content-Type: application/json' \
  -H 'Postman-Token: a0b84d38-5898-4dbc-8754-97a9c899b5df' \
  -d '{
	"from": "A5b96316020e01",
	"to": "A5b96316025196",
	"amount": 10
}'
```
Check the response

### Assumptions/Additions
1. There is a base currency for the bank and all transactions are made in the same currency
2. balances and transactions tables have created_at and updated_at timestamps to keep track of the changes
3. The value of 'id' in response is the reference number of the sender in the transactions table
4. Transactions for both sender (outgoing) and receiver (incoming) are logged in the transactions table
5. Negative tansfer amount is not allowed
6. Balance in the balances table cannot be negative

### Running tests
1. Specify value for test database in phpunit.xml as -
   <env name="MYSQL_DB" value="bank_db_test"/>
2. Create test database 
```
CREATE DATABASE bank_db_test;

```  
3. Run tests -
```
bin/phpunit tests
```
### System specifications used for development
1. Relational database : Mariadb 10.1.26
2. Stograge engine InnoDB
