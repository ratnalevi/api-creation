# Electric miles
This is test assignment for electric miles

Guidelines:
-------------------
1. Clone the repo
2. Install symfony CLI
3. Install packages using composer install in the root folder
4. symfony server:start - To start the server (will be started at http://localhost:8000)

Assumptions:
----------------
1. Status can only be 'NEW', 'PROCESSING', 'DELAYED', 'DELIVERED'
2. Customer ID validation is not done
3. Product ID and quantity availability is not done and expected to be valid
4. Expected delivery time is randomly picked within 10 days from order date

API endpoints: 
---------------------
1. POST: http://127.0.0.1:8000/orders/v1 - To create an order
2. PATCH: http://127.0.0.1:8000/orders/v1/1 - To update the status of order
3. GET: http://127.0.0.1:8000/orders/v1 - To list all orders
4. GET: http://127.0.0.1:8000/orders/v1?status=NEW - To list orders based on status
5. GET: http://127.0.0.1:8000/orders/v1?orderId=1 - To list order by ID

Migration: I have also created migration scripts so that the deployment on any new system will be easier.

Console: I have creaeted a console command which checks for delayed orders and inserts into delayed orders table.

1. To list commands: php bin/console
2. To execute: php bin/console app:delayed-orders

Tables
----------
I have created multiple tables to store the data received.

1. Orders
2. OrderItems
3. DelayedOrders
