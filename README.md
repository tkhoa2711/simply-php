# Simply PHP
============

This is a simple web-based shipping system called _ShipOnline_ that demonstrates
the basic usage of PHP for simple tasks such as database connection, logging in,
sending email, and handling forms.

# Files
- db.sql
- shiponline.php
- db.php
- admin.php
- login.php
- register.php
- request.php

# Running Instruction
- Create the necessary MySQL database and tables using the following command:

```
mysql < db.sql
```

Place the code in your configured web directory.

Otherwise, to quickly test the website, the built-in web server of PHP could
be used instead. Simply run the below command in the current directory (of
this source code):

```
php -S localhost:8080
```

The landing page will be http://localhost:8080/shiponline.php.