# Assets Plugin for [li3](http://lithify.me)
Helper plugin for the PHP MVC Framework [Lithium](http://lithify.me)

## Requirements
Lithium: <http://lithify.me>

## Features
* Seamlessly compile [LessCSS](http://leafo.net/lessphp) templates (requires .less files in css directory)
  * Requires [LessPHP](http://leafo.net/lessphp)

## How to Use

### 1. Get the Plugin
```shell
cd your-lithium-app/libraries
git clone git@github.com:joseym/assets.git
```

### 2. Add plugin to project
Edit your-lithium-app/bootstrap/libraries.php

```php
Libraries::add('assets');
```

### 3. Use Lithium like normal
* LessCSS - create a LessCSS stylesheet in /webroot/css (main.less)
* Link stylesheets in template
  * `<?php echo $this->html->style(array('main', 'debug', 'lithium')); ?>` - where `debug` and `lithium` are standard CSS files and `main` is a Less file
  

## Upcoming Features
* Seamless Cache Busting
* CSS/JS minification
* CSS Tidying

