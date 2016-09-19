CSRF Protect v1.00
==================

This little plugin exists only as an endpoint for uptime monitoring. In addition to confirming Apache is running
and the site is returning a 200 OK, this also confirms that MySQL is running by connecting to the database and 
writing to a log. By default, it will respond with json indicating the disposition of the response.


Installation
------------

  1. If it's not already present in config.yml, add the following lines under the 'plugins' section:

    ```yaml
    csrf_protect:
            apps:
                csrf_protect:
                    app_name: CsrfProtect
                    is_plugin: true
                    base_href: /protect
                    enabled: true
                    default_module: tokenizer
    ```

  2. Move `plugins/csrf_protect/web/js/csrf_protect.js` into `web/js`

  3. Include the js in the header.php file of each app you wish to lock down: `<script type="text/javascript" src="/js/bootstrap.min.js"></script>`

  4. Enable CSRF protect at the app or module level by calling the static enforce function: 

  ```php
  class FrontendController extends Dinkly
  {
    /**
     * Default Constructor
     * 
     * @return bool: always returns true on successful construction of view
     * 
     */
    public function __construct()
    {
      CsrfProtect::enforce();

      return true;
    }
  }
  ```

Usage
-----

Ensure that all forms are being sent via POST. Assuming no one is trying anything funny, everything should work without issue.

If an invalid token is detected, an exception will be thrown. Override the CsrfProtect::enforce() function to customize behavior.


License
-------

The CsrfProtect plugin is open-sourced software licensed under the MIT License.


Contact
-------

  - lewsid@lewsid.com
  - github.com/lewsid
