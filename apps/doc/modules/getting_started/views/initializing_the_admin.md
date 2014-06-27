Getting Started: Initializing the Admin
=======================================

Dinkly comes with a basic Admin interface for managing users, groups, and permissions. If you'd like to use it, follow these steps.

  1. Enter the following at the command line to generate the models and insert tables into the database:

    `php tools/gen_models.php -s=dinkly -i`

  2. Create an admin user account:

    `php tools/load_fixtures.php -s=dinkly`

    Unless changed, the username and password for the new account will be 'admin' and 'password'. **You will definitely want to change it before deploying your project to a production environment.**

  3. Point your browser to `/admin` and enter the username and password and you're good to go!