# Assets Plugin for [li3](http://lithify.me)
Helper plugin for the PHP MVC Framework [Lithium](http://lithify.me)

***

> This project was my first adventure into writing a lithium plugin. While it was a great learning experiance there are a lot of things I would do differently; therefore I am hitting this project again from a [different branch](https://github.com/joseym/li3_frontender/tree/assetic), using a powerful PHP library called [Assetic](https://github.com/kriswallsmith/assetic#readme).

***

## Requirements
Lithium: <http://lithify.me>

## Features
__CSS__

* Seamlessly compile [LessCSS](http://leafo.net/lessphp) templates (requires .less files in css directory)
  * Requires [LessPHP](http://leafo.net/lessphp) (included in this package: v0.3.0)
* Automatically adds cache busting to styles when page is rendered
* Minify CSS files
  * Strips out spaces, line breaks and comments

__Images__

* Converts all absolute image paths to relative paths
* Automatically adds cache busting to all local images

## How to Use

### 1. Get the Plugin
```shell
cd your-lithium-app/libraries
git clone git@github.com:joseym/assets.git
```

### 2. Add plugin to project
Edit your-lithium-app/bootstrap/libraries.php

```php
Libraries::add('assets', array(
  'config' => array(
       'css' => array(
            'cache_busting' => true,
            'minify' => true
       ),
       'image' => array(
            'cache_busting' => true
       )
  )
));
```
The config array is optional. Should you choose to leave it out the defaults (displayed above) will be set.
This is where you can determine if you want cache busting automatically enabled or not.

### 3. Use Lithium like normal
* LessCSS - create a LessCSS stylesheet in /webroot/css (main.less)
* Link stylesheets in template
  * `<?php echo $this->html->style(array('main', 'debug', 'lithium')); ?>` - where `debug` and `lithium` are standard CSS files and `main` is a Less file
  * If `minify` => `true` is set in `::add` configuration (example above) then all stylesheets are minified
  * __Note__: link your sheets like normal and they will render as `stylesheet.min.css`.

you can optionally enable or disable cache busting regardless of plugin settings (above) like so:
```php
<?php echo $this->html->style(array('main', 'debug', 'lithium'), array('cache_busting' => false)); ?>
```

### 4. Use Image Helper like normal
```php
<?php echo $this->html->image('test.jpg', array('height' => 150)); ?>
```

_renders as_

```
<img src="/img/test.jpg?1322778444" height="150" alt="" />
```
you can optionally enable or disable cache busting regardless of plugin settings (above) like so:
```php
<?php echo $this->html->image('test.jpg', array('height' => 150), array('cache_busting' => false)); ?>
```

## Upcoming Features
* File Merging
  * Linking of multiple Stylesheets with HTML style helper will merge the stylesheets into a single cache file and render them as 1 stylesheet link
  * Linking of multiple Javascript files with HTML script helper will merge the scripts into a single cache file and render them as 1 javascript file
* JS minification


