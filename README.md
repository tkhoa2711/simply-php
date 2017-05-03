# Simply PHP

This is a simple web-based shipping system called _ShipOnline_ that demonstrates
the basic usage of PHP for simple tasks such as database connection, logging in,
sending email, and handling forms.

## Running Instruction
Create the necessary MySQL database and tables using the following command:

```
mysql < db.sql
```

Configure the database connection by creating a file named `config.ini` in
the current directory:

```conf
[database]
host = localhost
username = root
password = 
dbname = php_db
```

Place the code in your configured web directory.

Otherwise, to quickly test the website, the built-in web server of PHP could
be used instead. Simply run the below command in the current directory (of
this source code):

```
php -S localhost:8080
```

The landing page will be http://localhost:8080/shiponline.php.

## Usage
The landing page is a simple sitemap of the system. It contains a registration
page for new customers to register. After successful registration, the customer
will receive a corresponding customer number which is used for logging in.
After login, the customer is redirected to the request page where he or she
can make a shipping request. An email will be sent to customer's email address
upon completion of the request.