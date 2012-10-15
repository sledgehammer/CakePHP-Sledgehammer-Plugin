# Sledgehammer as CakePHP 2.x plugin #

Intergrates the Sledgehammer Framework into an CakePHP 2.x project

## 1. Installation with [Composer](http://getcomposer.org/) ##

```
composer.phar require sledgehammer/cakephp-plugin
```

###  Manual installation ###

#### Add the plugin  ####

Place this plugin into the "app/Plugin/Sledgehammer/" folder.
``` git submodule add git://github.com/sledgehammer/CakePHP-Sledgehammer-Plugin.git app/Plugin/Sledgehammer ```


#### Add Sledgehammer  ####
Place the "sledgehammer" folder at the same level as the "app" folder:
``` git submodule add git://github.com/sledgehammer/core.git Vendor/sledgehammer/core ```

Your project folder should look like this:

 - app/
   - Plugin/
     - Sledgehammer/
       - Readme.md (this file)
       - ...
 - lib/
   - Cake/
 - Vendor/
   - sledgehammer/
     - core/


## 2. Activate plugin ##

Modify your app/Config/bootstrap.php to include:

```php
// define current environent in code or add "SetEnv APPLICATION_ENV development" to your httpd.conf or .htaccess
define('ENVIRONMENT', 'development');
// Override the e-mailaddres to whom the error-reports are sent in production mode or rely on the SERVER_ADMIN in httpd.conf/.htaccess
$_SERVER['SERVER_ADMIN'] = 'you@example.com';
CakePlugin::load('Sledgehammer', array('bootstrap' => true));
```
## 3. Activate goodies ##

### Sledgehammer Database ###

Upgrade your datasource in `APP/Config/database.php` from `Database/Mysql` into `Sledgehammer.Database/SledgehammerMysql`

Default to UTF-8 encoding and reports sql warnings & notices.

### Sledgehammer statusbar ###

Add the statusbar element just before the `</body>` tag in your /Layout/default.ctp.

```php
<?php echo $this->element('statusbar', array(), array('plugin' => 'Sledgehammer')); ?>
```
