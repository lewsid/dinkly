dinkly
======

Just a humble little PHP MVC Framework.


Dinkly CLI Tools
================

	Generate Dinkly datamodel files (consumes all yml files under config/schema + will not overwrite existing custom classes):

		php tools/gen_models.php

	Generate a new Dinkly module:

		php tools/gen_module.php -m <module name>

	Load fixtures (preloads tables with data stored in yml files under config/fixtures)

		php tools/load_fixtures.php