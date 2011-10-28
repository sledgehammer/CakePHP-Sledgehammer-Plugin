# SledgeHammer as CakePHP 2.x plugin #

Intergrates the SledgeHammer Framework into an CakePHP 2.x project

## Installation ##
1. Copy this plugin into the "app/Plugin/SledgeHammer folder"

2. Place the "sledgehammer" folder at the same level as the "app" folder:  
``` git submodule add https://github.com/bfanger/sledgehammer_core.git sledgehammer/core ```

 Project folder/  
 |- app/  
 | |- Plugin/  
 | | |- SledgeHammer/  
 | |    |- Readme.md (this file)  
 | |    |- ...  
 | |-...  
 |- lib/  
 | |- Cake/  
 |   |-...  
 |- sledgehammer/  
   |- core/  
   |- ...   
 

3. Modify your app/Config/bootstrap.php to include:

```php
// define current environent of add "SetEnv APPLICATION_ENV development" to your httpd.conf or .htaccess
define('ENVIRONMENT', 'development');
// Override the e-mailaddres to whom the error-reports are sent in production mode.
$_SERVER['SERVER_ADMIN'] = 'your@email.com';
CakePlugin::load('SledgeHammer', array('bootstrap' => true));
```

