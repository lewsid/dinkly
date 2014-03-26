Dinkly v1.25
============

The biggest little PHP framework

What makes Dinkly special?
--------------------------

Dinkly was built from the ground up to be simple, flexible, and easy to understand. You won't find any bloat here. What you will find is a handsome little framework that gives you everything you need and nothing you don't.

Features
--------

- Easy to use and flexible MVC architecture
- YAML configuration and customization
- Full Composer support
- Restful API
- Bare-bones and flexible ORM
- Data fixtures
- Out-of-the-box user authentication
- Ready-to-go Twitter Bootstrap admin interface


Installation
------------

1. Pull down the latest release from GitHub.

2. From the command line (and inside the main project directory), run the following command to install basic dependencies using Composer:

    `php composer.phar install`
    
3. Set the `web` folder to be web-accessible by your server software.   


Setup Basic Admin and Authentication
------------------------------------

1. Update `config/db.yml` to match your environment's database connection.

2. Generate the basic admin models:

    `php tools/gen_models.php -s=admin -i`

  This command will automatically create a new database called 'admin' if one doesn't already exist. It will also generate the necessary tables.

3. Create a basic admin user:

    `php tools/load_fixtures.php -s=admin`

    Unless changed, the default admin user created will use 'bfett' for the username and 'password' for the password. It's pretty lame... we encourage you to change it. This can be easily accomplished by updating the fixture file: `config/fixtures/dinkly/AdminUser.yml`  


Command Line Tools
------------------

Generate all Dinkly datamodel files (*will not* overwrite existing custom classes). Use the '-s' option to use the appropriate schema. To insert/update model sql, use the '-i' option:

    php tools/gen_models.php -s=<schema name> [-i]

Generate a single Dinkly datamodel file. Use the '-s' option to use the appropriate schema. To insert model sql, use the '-i' option:

    php tools/gen_model.php -s=<schema name> -m=<model name> [-i]

Load fixtures (preloads tables with data stored in yml files under config/fixtures):

    php tools/load_fixtures.php -s=<schema name>

Generate a new Dinkly application:

    php tools/gen_app.php -a=<app name>

Generate a new Dinkly module for a given application:

    php tools/gen_module.php -a=<app name> -m=<module name>

Execute unit tests (example):

    php vendor/phpunit/phpunit/phpunit --bootstrap config/bootstrap.php tools/unit_tests/classes/core/DinklyDataModel.php


License
-------

Dinkly is open-sourced software licensed under the MIT License.


Contact
-------

  - lewsid@lewsid.com
  - github.com/lewsid
