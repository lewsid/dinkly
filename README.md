Dinkly v3.23
============

The biggest little PHP framework


What makes Dinkly special?
--------------------------

Dinkly was built from the ground up to be simple, flexible, and easy to understand. You won't find any bloat here. What you will find is a handsome little framework that gives you everything you need and nothing you don't.


Features
--------

- Easy to use and flexible MVC architecture
- YAML configuration and customization
- Support for Github Flavored Markdown templating
- Composer support
- Minimal and flexible ORM
- Data fixtures
- Ready-to-go templating with Twitter Bootstrap
- Internationalization (i18n) Support


Installation
------------

  1. Pull down the latest release from GitHub.

  2. From the command line (and inside the main project directory), run the following command to install basic dependencies using Composer:

      `php composer.phar install --no-dev`
      
  3. Set the `web` folder to be web-accessible by your server software.

  4. Create a new file called `config.yml` under the `config` directory and copy the contents of `demo-config.yml` into it.


The Basics
----------

  1. Pay close to attention to `config/bootstrap.php`, it is here that you will want to toggle between your environments, if needed. It defaults to `dev` but can be changed to match other environments found in `config/config.yml`.

  2. Dinkly requires PHP 5.5 or newer


Command Line Tools
------------------

  - Generate all Dinkly datamodel files (*will not* overwrite existing custom classes). Use the '-s' option to use the appropriate schema. To insert/update model sql, use the '-i' option:

    `php tools/gen_models.php -s <schema name> [-i]`

  - Generate a single Dinkly datamodel file. Use the '-s' option to use the appropriate schema. To insert model sql, use the '-i' option:

    `php tools/gen_model.php -s <schema name> -m <model name> [-i]`

  - Load fixtures (preloads tables with data stored in yml files under config/fixtures):

    `php tools/load_fixtures.php -s <schema name>`

  - Generate a new Dinkly application:

    `php tools/gen_app.php -a <app name>`

  - Generate a new Dinkly module for a given application:

    `php tools/gen_module.php -a <app name> -m <module name>`

  - Test database connection for a given schema (and optionally by environment):

    `php tools/test_db.php -s <schema name> [-e <environment>]`

  - Run unit tests (assuming you've installed the additional dev packages with composer). Use the -f option if you want to run unit tests in a specific file. Use the -f and -t option if you want to run a specific test in a specific file:

    `php tools/run_unit_tests.php [-f <path to test file>] [-t <name of specific test>]`


Fetching GET and POST parameters
================================

In any controller class function, you may use `$this->fetchGetParams()` to grab GET parameters, and `$this->fetchPostParams()` to grab POST parameters.

You may overload `filterGetParameters()` and `filterPostParameters()` as needed in the Dinkly class to do post-processing of these arrays.


Upgrading a Dinkly project from 2.x to 3.x (Using git)
======================================================

1. Back up your project.

2. If Dinkly isn't currently configured as a remote, run this command at the project root to do so: `git remote add dinkly https://github.com/lewsid/dinkly.git`.
 
3. Pull down the most recent version of Dinkly: `git pull dinkly master`.

4. Run `git status` and pay special attention to files that are listed as `both modified`. Things will probably look pretty messy. It's okay.

5. Copy and paste (and replace) the following folders from your backup into your project:

  - `apps/admin`
  - `apps/frontend`
  - `apps/api`
  - `apps/error`
  - `classes/models/custom`
  - `config/fixtures`
  - `config/schemas`
  - `plugins/*` (only if you use or have overriden anything prior to the upgrade)

5. Add and then commit your changes to your project's repository.


License
-------

Dinkly is open-sourced software licensed under the MIT License.


Contact
-------

  - lewsid@lewsid.com
  - github.com/lewsid
