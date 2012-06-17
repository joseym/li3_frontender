# Assets Plugin for [li3](http://lithify.me)

***

> This is my second draft of an Assets plugin for Lithium PHP. The first was a great learning experiance but I've decided there are a number of things I would change.

***

This Plugin now uses the awesome [Assetic](https://github.com/kriswallsmith/assetic) library to power many of it's features.

## Original Project

> This is a branch off of my [original assets plugin ("li3_frontender")](https://github.com/joseym/li3_frontender/tree/legacy)(legacy branch)
> If you would like to use the old version, you can clone it from there.

## Installation
There are several ways to grab and use this project:

### Use Composer
__Best Option (default)__

> Other install options require a configuration parameter be set in `Libraries::add()`
> [More on that later](https://github.com/joseym/li3_frontender#configuration-options).

Modify your projects `composer.json` file

~~~ json
{
    "require": {
    	...
        "joseym/li3_frontender": "master"
        ...
    }
}
~~~

Run `php composer.phar install` (or `php composer.phar update`) and, aside from adding it to your Libraries, you should be good to go.

> __Notice:__ LessPHP does not currently provide a composer package, therefore I have branched the project and have submitted my own package until such time as [Leafo decides to adopt composer](https://github.com/leafo/lessphp/issues/216).
> I will do my best to keep it up to date with the primary project.

### Load via Submodule
__More manual, bleh. Seriously, Composer is awesome__

> This option requires that you tell the plugin you are not using `composer`.
> [See library option (`source`)](https://github.com/joseym/li3_frontender#configuration-options)

1. Clone/Download the plugin into your app's ``libraries`` directory.
2. Tell your app to load the plugin by adding the following to your app's ``config/bootstrap/libraries.php``:

	Libraries::add('li3_frontender', array('source' => 'submodule'));

	> Important to set the source to something else as 'composer'.
	> Configuration options are available, standby

3. Pull in the the project dependencies.

> Currently dependancies include [Assetic](https://github.com/kriswallsmith/assetic#readme), [Symfony/Process](https://github.com/symfony/Process#readme) and [LessPHP](https://github.com/leafo/lessphp#readme).

	$ cd app/libraries/li3_frontender
	$ git submodule init
	$ git submodule update


## Usage

> If you use coffee script you will have to ensure [Node.JS](http://nodejs.org/) and [CoffeeScript](http://http://coffeescript.org) are running on your server.

This project also comes packaged with [YUI Compressor](http://yuilibrary.com/download/yuicompressor/), which Assetic uses for compression of JS and CSS assets.

Currently this project supports the following frontend tools:

1. LessCSS compiling
2. CoffeeScript compiling
3. Instant cache busting thru unique filenames
4. CSS/JS Compression

The project comes bundled with it's own [Helper](https://github.com/joseym/li3_frontender/blob/assetic/extensions/helper/Assets.php), here's how use use it.

### Linking Stylesheets
You assign page styles much like you would with the out-of-the-box Html helper

~~~ php
<?php $this->assets->style(array('main', 'menu', 'magic.less')); ?>
~~~

> You may have noticed the `.less` file in there. Adding the file extension is required for `.less` files to ensure they are compiled, you may include the `.css` extension for standard stylesheets or just leave it off.

### Linking Scripts
Like the style helper, the script helper also takes an array.

~~~ php
<?php $this->assets->script(array('plugins', 'common', 'niftythings.coffee'); ?>
~~~

> Just like the `.less` file in the last example, if you pass a `.coffee` file to the script helper the plugin will compile it and serve up the proper, compiled, js. All other files are assumed `.js`. Feel free to add `.js` to these extensions if you would like.

## Production vs Development

> The backend of this plugin will do its best to determine if you're in a dev environment or production, if you're in a production environment this plugin will automatically compress your stylesheets and scripts and merge them into a single file and serve __that__ file up to your layout or view.

This option, and several others are overwriteable from the `Libraries::add()` configuration. Here's an example

~~~ php
<?php
	Libraries::add('li3_frontender', array(
		'compress' => false,
		'production' => true,
		'assets_root' => LITHIUM_APP_PATH . "/webroot/assets",
		'locations' => array(
			'coffee' => '/usr/bin/libs/coffee',
			'node' => '/usr/bin/libs/node'
		),
		'source' => 'submodule',
		'cacheOnly' => true
	));
?>
~~~

### Configuration options

<table>
	<tr>
		<th>Name</th>
		<th>Options</th>
		<th>Defaults</th>
		<th>Description</th>
	</tr>
	<tr>
		<td><strong>compress</strong></td>
		<td><code>bool</code> (true/false)</td>
		<td><code>false<strong></td>
		<td>Force assets to be compressed, if production this defaults to <code>true</code>, otherwise <code>false</code>.</td>
	</tr>
	<tr>
		<td><strong>production</strong></td>
		<td><code>bool</code> (true/false)</td>
		<td>attempts to read from Lithium Environments class</td>
		<td>Force assets to render in production or not, if this isn't set then the plugin will attempt to determine this automagically.</td>
	</tr>
	<tr>
		<td><strong>assets_root</strong></td>
		<td>Pass in a path to your assets</td>
		<td><code>LITHIUM_APP_PATH . "/webroot"</code></td>
		<td>Where should the plugin look for your files, defaults to the standard <code>webroot</code> directory. The example above would look for CSS files in <code>/webroot/assets/css/</code></td>
	</tr>
	<tr>
		<td><strong>locations</strong></td>
		<td>array: <code>coffee</code> - <em>path to coffeescript on server</em><br /><code>node</code> - <em>path to node on server</em></td>
		<td><code>coffee</code> - <code>/usr/bin/coffee</code><br /><code>node</code> - <code>/usr/bin/node</code></td>
		<td>These are the locations of <code>node</code> and <code>coffeescript</code> on your server, defaults should suffice.</td>
	</tr>
	<tr>
		<td><strong>source</strong></td>
		<td>string: <code>composer</code><br /><code>submodule</code></td>
		<td><code>composer</code></td>
		<td>
			This determines where the library will pull dependency libraries, composer uses vendor paths in
			`libraries/_source` whereas submodule loads librarys within this plugin `libraries/li3_frontender/libraries`.
			Normally you only need to set this option if you do not install this plugin via composer.
		</td>
	</tr>
	<tr>
		<td><strong>cacheOnly</strong></td>
		<td>boolean</td>
		<td><code>false<code></td>
		<td>
			If true, will display a 404 if the assets could not be read from cache. For some plugins, such as
			`li3_docs` this will result in not being able to load the css contained in the plugin.
		</td>
</table>

## Collaborate
As always, I welcome your collaboration to make things "even more betterer", so fork and contribute if you feel the need.
