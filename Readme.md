# Sledgehammer as CakePHP 2.x plugin #

Intergrates the Sledgehammer Framework into an CakePHP 2.x project

## Installation ##

### 1. Add the plugin  ###

Place this plugin into the "app/Plugin/Sledgehammer/" folder.
``` git submodule add git://github.com/sledgehammer/CakePHP-Sledgehammer-Plugin.git app/Plugin/Sledgehammer ```


### 2. Add Sledgehammer  ###
Place the "sledgehammer" folder at the same level as the "app" folder:
``` git submodule add git://github.com/sledgehammer/core.git sledgehammer/core ```

Your project folder should look like this

 - app/
   - Plugin/
     - Sledgehammer/
       - Readme.md (this file)
       - ...
     - ...
  - ...
 - lib/
   - Cake/
     - ...
 - sledgehammer/
    - core/
    - ...


### 3. Activate plugin ###

Modify your app/Config/bootstrap.php to include:

```php
// define current environent of add "SetEnv APPLICATION_ENV development" to your httpd.conf or .htaccess
echo define('ENVIRONMENT', 'development');
// Override the e-mailaddres to whom the error-reports are sent in production mode.
$_SERVER['SERVER_ADMIN'] = 'your@email.com';
CakePlugin::load('Sledgehammer', array('bootstrap' => true));
```
### 4. Activate goodies ###

#### Sledgehammer statusbar ####

Add the statusbar element just before the `</body>` tag.

```php
<?php echo $this->element('statusbar', array(), array('plugin' => 'Sledgehammer')); ?>
```

#### Sledgehammer Database ####

Upgrade your datasource in `APP/Config/database.php` from `Database/Mysql` into `Sledgehammer.Database/SledgehammerMysql`

Default to UTF-8 encoding and reports sql warnings & notices.