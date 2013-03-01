Dinkly v0.2.31
==============

A humble little PHP Framework.

Features
========

- Easy and flexible MVC architecture
- YAML configuration and customization
- Restful API
- Bare-bones ORM
- Data fixtures
- Closure depenedency support
- Authentication
- Ready-to-go admin interface featuring Datatables
- Twitter Bootstrap

Setup
=====

1. Update composer:

    `php composer.phar update`

2. Update models, create basic admin table:

    `php tools/gen_models.php -i`

3. Create a basic admin user (which can be changed in config/fixtures/AdminUser.yml):

    `php tools/load_fixtures.php`

4. Update base href value in config/config.yml as needed.

Dinkly CLI Tools
================

Generate all Dinkly datamodel files (*will not* overwrite existing custom classes). Use the '-s' option to use the appropriate schema. To insert model sql, use the '-i' option.

	php tools/gen_models.php -s=<schema name> [-i]

Generate a single Dinkly datamodel file. Use the '-s' option to use the appropriate schema. To insert model sql, use the '-i' option.

	php tools/gen_model.php -s=<schema name> -m=<model name> [-i]

Generate a new Dinkly module.

	php tools/gen_module.php -m=<module name>

Load fixtures (preloads tables with data stored in yml files under config/fixtures)

	php tools/load_fixtures.php

License
=======

Dinkly is open-sourced software licensed under the MIT License.