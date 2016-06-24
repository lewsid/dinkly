Dinkly v2.35
============

The humblest little MVC Framework

What makes Dinkly special?
--------------------------

Dinkly was built from the ground up to be simple, flexible, and easy to understand. You won't find any bloat here. What you will find is a handsome little framework that gives you everything you need and nothing you don't.

Features
--------

- Easy to use and flexible MVC architecture
- YAML configuration and customization
- Support for Github Flavored Markdown templating
- Full Composer support
- Restful API
- Minimal and flexible ORM
- Data fixtures
- Out-of-the-box user authentication
- Ready-to-go Twitter Bootstrap admin interface
- Internationalization (i18n) Support


Installation
------------

1. Pull down the latest release from GitHub.

2. From the command line (and inside the main project directory), run the following command to install basic dependencies using Composer:

    `php composer.phar install --no-dev`
    
3. Set the `web` folder to be web-accessible by your server software.


The Basics
----------

1. Pay close to attention to `config/bootstrap.php`, it is here that you will want to toggle between your environments, if needed. It defaults to `dev` but can be changed to match other environments found in `config/config.yml`.

2. Dinkly requires PHP 5.5 or newer

Setup Basic Admin and Authentication
------------------------------------

1. Update `config/config.yml` to match your environment's database connection.

2. Generate the basic user authentication models:

    `php tools/gen_models.php -s dinkly -i`

  This command will automatically create a new database called 'admin' if one doesn't already exist. It will also generate the necessary tables.

3. Create an admin user:

    `php tools/load_fixtures.php -s dinkly`

    Unless changed, the default admin user created will use 'admin' for the username and 'password' for the password.


Command Line Tools
------------------

Generate all Dinkly datamodel files (*will not* overwrite existing custom classes). Use the '-s' option to use the appropriate schema. To insert/update model sql, use the '-i' option:

    php tools/gen_models.php -s <schema name> [-i]

Generate a single Dinkly datamodel file. Use the '-s' option to use the appropriate schema. To insert model sql, use the '-i' option:

    php tools/gen_model.php -s <schema name> -m <model name> [-i]

Load fixtures (preloads tables with data stored in yml files under config/fixtures):

    php tools/load_fixtures.php -s <schema name>

Generate a new Dinkly application:

    php tools/gen_app.php -a <app name>

Generate a new Dinkly module for a given application:

    php tools/gen_module.php -a <app name> -m <module name>

Test database connection for a given schema (and optionally by environment):

    php tools/test_db.php -s <schema name> [-e <environment>]

Run unit tests (assuming you've installed the additional dev packages with composer). Use the -f option if you want to run unit tests in a specific file. Use the -f and -t option if you want to run a specific test in a specific file:

    php tools/run_unit_tests.php [-f <path to test file>] [-t <name of specific test>]


License
-------

Dinkly is open-sourced software licensed under the MIT License.


Contact
-------

  - lewsid@lewsid.com
  - github.com/lewsid
