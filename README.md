# Assets Plugin for [li3](http://lithify.me)

***

> This is my second draft of an Assets plugin for Lithium PHP. The first was a great learning experiance but I've decided there are a number of things I would change.

***

This Plugin now uses the awesome [Assetic](https://github.com/kriswallsmith/assetic) library to power many of it's features.

## Original Project

This is a branch off of my [original assets plugin ("li3_frontender")](https://github.com/joseym/li3_frontender)

***

> Instructions will be added below as I build out plugin features

***

## Installation
1. Clone/Download the plugin into your app's ``libraries`` directory.
2. Tell your app to load the plugin by adding the following to your app's ``config/bootstrap/libraries.php``:

	Libraries::add('li3_frontender');

3. Pull in the the project dependencies.

> Currently dependancies include [Assetic](https://github.com/kriswallsmith/assetic) and [LessPHP](https://github.com/leafo/lessphp).

	$ cd app/libraries/li3_frontender
	$ git submodule init
	$ git submodule update

***

## That's all she wrote so far

Assetic features as well as some of my own will be added thru the use of a helper.

Stay tuned as this project should progress fairly quickly.