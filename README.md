WebFW
=========

  - A lightweight object-oriented PHP framework!
  - __Author:__ [Marko Vodanović]



Sources
----

  - [GitHub]
  - [GitList]



Version
----

  - still in development, no stable release :(



Installation (for Ubuntu users)
----

  1. `mkdir ~/www/example && cd ~/www/example` (create an empty directory & enter it)
  2. `mkdir WebFW && git clone https://github.com/mvodanovic/WebFW.git WebFW/Framework` (checkout the framework in the webfw/ directory)
  3. `cp -R WebFW/Framework/.install/* .` (copy everything from the install directory to the current directory; don't forget the dot)
  4. `sudo vim /etc/apache/sites_available/example.conf` (setup the web server with the public/ directory as document root)
  5. `sudo a2ensite example && sudo service apache2 reload` (activate the new web site)
  6. in browser -> [example.com] -> A hello world page



Apache2 VirtualHost configuration example
----

```
<VirtualHost *:80>
    ServerName mysite.com
    DocumentRoot /var/www/mysite/Public
    <Directory /var/www/mysite/Public>
        AllowOverride All
    </Directory>
    ErrorLog ${APACHE_LOG_DIR}/mysite.error.log
    LogLevel warn
    CustomLog ${APACHE_LOG_DIR}/mysite.access.log combined
</VirtualHost>
```

  - __Note:__ This is only an example configuration, please take care to adapt it accordingly to suit your needs!



License
----

  - [MIT License]



[Marko Vodanović]:http://vodanovic.net/
[GitHub]:https://github.com/mvodanovic/WebFW
[GitList]:http://gitlist.vodanovic.net/webfw.git/
[example.com]:http://example.com/
[MIT License]:https://raw.github.com/mvodanovic/WebFW/master/LICENSE.txt
