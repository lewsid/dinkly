Dinkly v0.2
===========

A humble little PHP Framework that takes minimal to a whole new level.

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

1. Create a MySQL database and update db.yml
2. Update composer:

    `php composer.phar update`

3. Update models, create basic admin table:

    `php tools/gen_models.php`

4. Create a basic admin user (which can be changed in config/fixtures/AdminUser.yml):

    `php tools/load_fixtures.php`

5. Update base href value in config/config.yml as needed.

Dinkly CLI Tools
================

Generate Dinkly datamodel files (*will not* overwrite existing custom classes, but *will* drop and recreate tables):

	php tools/gen_models.php

Generate a new Dinkly module:

	php tools/gen_module.php -m <module name>

Load fixtures (preloads tables with data stored in yml files under config/fixtures)

	php tools/load_fixtures.php

License
=======

Dinkly is open-sourced software licensed under the MIT License.