cobalt.php-demo
==========

[PHP 7.3 recommended for better performance](https://www.phoronix.com/scan.php?page=news_item&px=PHP-7.3-Performance-Benchmarks)

For non Docker setup:
Please, configure [Apache HTTPd](https://httpd.apache.org/) with the following rules [see .htaccess](.htaccess) (or equivalent in case of any other web server):

````
RewriteEngine on
RewriteCond %{REQUEST_URI} ^(.*)/cobalt.php-demo/(.*)$
RewriteRule ^(.*)$ %1/cobalt.php-demo/public/%2 [R=301,NC,L]
````

After [PHP 7.3](http://php.net/) and [composer](https://getcomposer.org/) installation:

````
$ cd <Cobalt PHP DEMO>
$ composer install
````

At this point, the environment should be ready for testing.
