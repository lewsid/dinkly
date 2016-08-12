UptimeResponder v1.00
=====================

This little plugin exists only as an endpoint for uptime monitoring. In addition to confirming Apache is running
and the site is returning a 200 OK, this also confirms that MySQL is running by connecting to the database and 
writing to a log. By default, it will respond with json indicating the disposition of the response.


Installation
------------

  1. If it's not already present in config.yml, add the following lines under the 'plugins' section:

    ```
    uptime_responder:
            apps:
                responder:
                    app_name: UptimeResponder
                    is_plugin: true
                    base_href: /responder
                    enabled: true
                    default_module: response
    ```

  2. Make sure to give it a database to talk to as well. Place these lines under the 'databases' section and tweak as needed.

    ```
    uptime_responder:
            host: localhost
            user: root
            pass: root
            name: dinkly_app
    ```

  3. At the CLI: `php tools/gen_models.php -s uptime_responder -p uptime_responder -i`


Usage
-----

Just hit one of the following links:

  - http://example.com/responder (outputs json)
  
  - http://example.com/responder/response/default/format/xml (outputs xml)

  - http://example.com/responder/response/default/format/string (outputs simple string)


License
-------

The UptimeResponder plugin is open-sourced software licensed under the MIT License.


Contact
-------

  - lewsid@lewsid.com
  - github.com/lewsid
