# CodeIgniter - minify
=====================================

[![Build Status](https://travis-ci.org/slav123/CodeIgniter-minify.svg?branch=master)](https://travis-ci.org/slav123/CodeIgniter-minify)

## Overview

Simple CodeIgniter library to compress **CSS and JavaScript** files on the fly.

Library is based on few other scripts like <http://code.google.com/p/minify/> 
or <https://code.google.com/p/cssmin> to minify CSS and it uses
[Google Closure compiler](https://developers.google.com/closure/compiler/) to 
compress JavaScript

###INSTALATION:
Just put Minify.php file in libraries path, and create config file on config directory.

###USING THE LIBRARY:

####Configure the library:
All directories needs to be writable.

	// output path where the compiled files will be stored
    $config['assets_dir'] = 'assets/'; 	
    
    // where to look for css files 
    $config['css_dir'] = 'assets/css';
    
    // where to look for js files 
	$config['js_dir'] = 'assets/js'; 

	// compression engine setting
	$config['compression_engine'] = array(
		'css' => 'minify', // minify || cssmin
    	'js'  => 'closurecompiler' // jsmin || closurecompiler || jsminplus
    );

####Available engines
CSS - *minify* or *cssmin* - both of them are local, just try out which one is better for you,
JS - *closurecompiler* makes API call to external server, it's slower then regular inline engine, but it's super efficient with compression, *jsmin* and *jsminplus* are local

####Run the library
In the controller

    //load the library
	$this->load->library('minify'); 
	$this->load->helper('url');

In view	

	// add css files
	$this->minify->css(array('reset.css', 'style.css', 'tinybox.css')); 
	
	// add js files
	$this->minify->js(array('html5.js', 'main.js')); 
	
	// bool argument for rebuild css (false means skip rebuilding). 
	echo $this->minify->deploy_css(TRUE);

    //Output: '<link href="path-to-compiled-css" rel="stylesheet" type="text/css" />'
    
    // rebuild js (false means skip rebuilding).
    echo $this->minify->deploy_js(); 
 
    //Output: '<script type="text/javascript" src="path-to-compiled-js"></script>'.

    
### Changelog

20 Apr 2015
* Closure compilerconfiguration extracted to config file

22 Mar 2015
* method chaining support
* new methods: `add_css()` and `add_js()` - gives ability for adding files to existing files arrays
* added support to run library with custom array config, assigned as second parameter (during loading) $this->load->library('minify', $config);
* added support for *groups* in files arrays - as second (optional) parameter in methods: `css()`, `js()`, `add_css()` and `add_js()` (i.e. $this->minify->js(array('script.js'), 'extra'); - default group name is *default*)
* added support for strings as first parameter in methods: `css()`, `js()`, `add_css()` and `add_js()` (i.e. $this->minify->js('first.js, second.js');)
* added support for automatic files names: $config['auto_names'] = TRUE;
* external compression classes moved to *minify* folder
* unit tests for new features

10 Feb 2015
* Unit testing

09 Feb 2015
* 2 new engines to compres JS files
* documentation update

13 Oct 2014
* changed way of generating JS file

14 July 2014
* small bug fixes in JS compression

4 July 2014
* sample JavaScript files to see how it works 
* detection of empty JS file couses force refresh

23 May 2014

* you can chose your compression engine library in config file (CSS only)
* speed optimisations
* force CSS rewrite using $this->minify->deploy_css(TRUE);

11 Mar 2014

* completly rewrite CSS parser - uses cssmin compress CSS,
* detects file modification time no longer force rewrites,
* example usage now included withing app

### Any questions ?

Report theme here: <https://github.com/slav123/CodeIgniter-minify/issues>

### Sponsors

This project is build with [PHP Storm](https://www.jetbrains.com/phpstorm/)

