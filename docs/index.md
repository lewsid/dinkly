# I. Introduction

Dinkly is a modest little MVC (model, view, controller) PHP framework inspired by Ruby on Rails and earlier (simpler) incarnations of Symfony. It was designed from the ground-up to be fast, easy to understand, highly customizable, and have a small footprint. The Dinkly framework comes with everything you need to build a basic web application, and excels when used for rapid prototyping, though it is perfectly capable of handling much larger, more sophisticated, use-cases. 

# II. Features

* Easy-to-use and flexible MVC architecture
* Consistent naming conventions and architectural organization
* YAML configuration and customization
* Support for Github Flavored Markdown templating
* Composer support
* Extensible ORM
* Data fixtures for easy data loading
* Ready-to-go templating with Twitter Bootstrap
* Internationalization (i18n) Support
* Testing utilities
* Minimal memory footprint

# III. Getting Started

## 1. Server Requirements

 * MySQL >= 5.2
 * PHP >= 5.5

## 2. Installation

  1. Pull down the latest release from GitHub: https://github.com/lewsid/dinkly

  2. Fetch and install needed packages via composer: `php composer.phar install --no-dev`
  
  This will create a new folder called `vendor` in the project root and populated with composer-provided packages.

## 3. Configuration

  1. Ensure that Dinkly's `web` folder is web-accessible by your server software. This is typically achieved via a symbolic link.
  
  2. Create a new file called `config.yml` under the `config` directory and copy the contents of `demo-config.yml` into it.
  
  3. Note that `config/bootstrap.php` is where that you will want to toggle between your environments, if needed. The default is `dev` but change this as needed match other environments as specified in the primary configuration found in `config/config.yml`.

## 4. Build Commands

- Generate all Dinkly datamodel files (this *does not* overwrite existing custom classes). Use the '-s' option to use the appropriate schema. To insert/update model sql, use the '-i' option:

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

# IV. Understanding the Architecture

Something about MVC.

## 1. Directory Organization

## 2. Routing

## 3. Controllers

### a. Modules

### b. Components

## 4. Views

## 5. Models

### a. Schemas

### b. Fixtures

### c. Collections

### d. Model Customization

# Contributors

# License
