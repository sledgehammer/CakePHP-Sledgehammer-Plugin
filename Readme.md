# SledgeHammer as CakePHP 2.x plugin #

Intergrates the SledgeHammer Framework into an CakePHP 2.x project

## Installation ##

### 1. Add the plugin  ###

Place this plugin into the "app/Plugin/SledgeHammer/" folder.
``` git submodule add https://bfanger@github.com/bfanger/cakeplugin_sledgehammer.git app/Plugin/SledgeHammer ```


### 2. Add SledgeHammer  ###
Place the "sledgehammer" folder at the same level as the "app" folder:
``` git submodule add https://github.com/bfanger/sledgehammer_core.git sledgehammer/core ```

Your project folder should look like this

 - APP/
    - Plugin/
       - SledgeHammer/
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
CakePlugin::load('SledgeHammer', array('bootstrap' => true));
```
### 4. Activate goodies ###

#### SledgeHammer statusbar ####

Add the statusbar element just before the `</body>` tag.

```php
<?php echo $this->element('statusbar', array(), array('plugin' => 'SledgeHammer')); ?>
```

#### SledgeHammer Database ####

Upgrade your datasource in `APP/Config/database.php` from `Database/Mysql` into `SledgeHammer.Database/SledgeHammerMysql`