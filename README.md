## Bank Api

A REST API to transfer funds from the sender's account to receiver's account using PHP framework lumen - https://lumen.laravel.com/

## Pre-requisities
1. PHP >= 7.1.3
2. Install composer https://getcomposer.org/
3. composer global require "laravel/lumen-installer"

Check the laravel 5.6 installation https://lumen.laravel.com/docs/5.6

## How to setup?
1. clone the code to your machine
    ```
    git clone git@github.com:jaaic/bank-api.git
    ```
2. Add correct MYSQL settings to .env file

3. Install dependencies
    ```
    composer install
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

## Invoke api
Pick any 2 account numbers from 'balances' table,(these will be randomly generated during seeding), 
example - A5b96316020e01, A5b96316025196 and the amount to transfer
        

```
curl -X POST \
  http://localhost:8080/transfer \
  -H 'Cache-Control: no-cache' \
  -H 'Content-Type: application/json' \
  -d '{
    "from": "A5b96316020e01",
    "to": "A5b96316025196",
    "amount": 10
}'
```
Check the response

## Assumptions/Additions
1. There is a base currency for the bank and all transactions are made in the same currency
2. balances and transactions tables have 'created_at', 'updated_at' columns which are timestamps to keep track of the changes.
3. 'details' column has been added to transactions table to records details of transfer.
4. The value of 'id' in response is the reference number of the sender logged in the transactions table upon success.
5. Transactions for both sender (outgoing) and receiver (incoming) are logged in the transactions table.
6. Negative value for amount is not allowed in the request.
7. Balance in the balances table cannot be negative (it has been created as unsigned decimal data type).
8. User authentication/ authorization have not been addressed.
9. Maximum transfer limit for 1 transaction is assumed to be 5000.
10.Raw database queries have been used instead of ORM.

## Considerations
1. To accidentally prevent the user from clicking pay/transfer button twice, the transaction reference number is generated 
   as a hash of the account numbers, amount and current time stamp accurate to the second. If the same reference number exists
   in the transactions table, it means the same request was attempted within the single second and chances were it was
   accidental. Hence transaction is prevented.
   Memcached or redis could be used for better performance.

2. Under high concurrency, there would be multiple threads trying to update the same rows in the balance table. To prevent 
   other queries from reading uncommitted dirty data and doing the transfer calculations, row level locks are obtained 
   in the select statements. The locks are released after a rollback or commit. This could lead to many processes waiting 
   but considering the sensitive nature of the data, this is choice I have made.
   
3. Before the transaction, I have set autocommit = 0. In case of database outage/unavailability, the uncommitted data would 
   not be persisted in the database and system would stay in the state before the transaction started.
   Various exceptions have been handled and rollback has been initiated in case of any exception.
   
4. If 2 users(A,B) transfer data to C at the same time, one would have to wait until C's balance as been updated by other 
   due to row locking in select statement. This would prevent reading the wrong balance, balance calculations
   on the amount that would be immediately updated by another process.

## Running tests
1. Specify value for test database in phpunit.xml as -
   ```
   <env name="MYSQL_DB" value="bank_db"/>
   ```
2. Create test database 
    ```
    CREATE DATABASE bank_db;
    
    ```  
3. Ensure database migrations and seeding has been done exactly once by running
   ```
   php artisan migrate
   php artisan db:seed --class=BalancesTableSeeder
   ```
3. Run tests -
Execute tests
    ```
    bin/phpunit tests
    ```
## System specifications used for development/ testing
1. Relational database : Mariadb 10.1.26
2. Storage engine: InnoDB
3. Web server nginx:1.15.2-alpine
