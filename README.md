# CodeIgniter - minify [![Build Status](https://travis-ci.org/slav123/CodeIgniter-minify.svg?branch=master)](https://travis-ci.org/slav123/CodeIgniter-minify)

Simple CodeIgniter library to compress **CSS and JavaScript** files on the fly.

Library is based on few other scripts like <http://code.google.com/p/minify/> 
or <https://code.google.com/p/cssmin> to minify CSS and it uses
[Google Closure compiler](https://developers.google.com/closure/compiler/) to 
compress JavaScript

## Instalation
Just put `Minify.php` file in libraries path, and create `minify.php` config file on config directory.

## Using the library

#### Configure the library:
All directories needs to be writable. Next you can set your own values for config file.

```php
// output path where the compiled files will be stored (default value: 'assets')
$config['assets_dir'] = 'assets';

// optional - path where the compiled css files will be stored (default value: '' - for backward compatibility)
$config['assets_dir_css'] = ''; 

// optional - path where the compiled js files will be stored (default value: '' - for backward compatibility)
$config['assets_dir_js'] = '';     

// where to look for css files (default value: 'assets/css')
$config['css_dir'] = 'assets/css';

// where to look for js files (default value: 'assets/js')
$config['js_dir'] = 'assets/js';

// default file name for css (default value: 'style.css')
$config['css_file'] = 'styles.css';

// default file name for js (default value: 'scripts.js')
$config['js_file'] = 'scripts.js';

// use automatic file names (default value: 'FALSE')
$config['auto_names'] = FALSE;

// compress files or not (default value: 'TRUE')
$config['compress'] = TRUE;

// compression engine setting (default values: 'minify' and 'closurecompiler')
$config['compression_engine'] = array(
	'css' => 'minify', // minify || cssmin
	'js'  => 'closurecompiler' // closurecompiler || jsmin || jsminplus
);

// when you use closurecompiler as compression engine you can choose compression level (default value: 'SIMPLE_OPTIMIZATIONS')
// avaliable options: "WHITESPACE_ONLY", "SIMPLE_OPTIMIZATIONS" or "ADVANCED_OPTIMIZATIONS"
$config['closurecompiler']['compilation_level'] = 'SIMPLE_OPTIMIZATIONS';
```

#### Available engines
* CSS - `minify` or `cssmin` - both of them are local, just try out which one is better for you,
* JS - `closurecompiler` makes API call to external server, it's slower then regular inline engine, but it's super efficient with compression, `jsmin` and `jsminplus` are local

#### Run the library
In the controller:
```php
// load the library
$this->load->library('minify');
// or load and assign custom config (will override values from config file)
$this->load->library('minify', $config);
// by default library's functionality is enabled, but in some cases you would like to return
// assets without compilation and compression - when debugging or in development environment
// in that case you can use config variable to disable it
$config['enabled'] = FALSE;
// or
$this->minify->enabled = FALSE;

```
In controller or view:	
```php
// set css files - you can use array or string with commas
// when using this method, you replaces previously added files
$this->minify->css(array('reset.css', 'style.css', 'tinybox.css'));
$this->minify->css('reset.css, style.css, tinybox.css');

// add css files - you can use array or string with commas
// when using this method, you're adding new files to previous ones
$this->minify->add_css(array('reset.css'))->add_css('style.css, tinybox.css');

// set js files - you can use array or string with commas
// when using this method, you replaces previously added files
$this->minify->js(array('html5.js', 'main.js'));
$this->minify->js('html5.js, main.js');

// set js files - you can use array or string with commas
// when using this method, you're adding new files to previous ones
$this->minify->add_js(array('html5.js'))->add_js('main.js');

// with methods: css(), js(), add_css() and add_js()
// you can pass group name for given files as second parameter
// default group name is "default"
$this->minify->js(array('html5.js', 'main.js'), 'extra');
$this->minify->add_css('style.css, tinybox.css', 'another');

// deploy css
// bool argument for rebuild css - false means skip rebuilding (default value: TRUE) 
echo $this->minify->deploy_css(TRUE);

//Output: '<link href="path-to-compiled-css" rel="stylesheet" type="text/css" />'

// deploy js
// bool argument for rebuild js  - false means skip rebuilding (default value: FALSE)
echo $this->minify->deploy_js(); 

//Output: '<script type="text/javascript" src="path-to-compiled-js"></script>'.

// you can use automatic file name for particular deploy when you have $config['auto_names'] set to FALSE
// to do so, you must set file name to 'auto' during deploy
echo $this->minify->deploy_css(TRUE, 'auto');
echo $this->minify->deploy_js(TRUE, 'auto');

//Output: '<link href="path-to-compiled-css-with-auto-file-name" rel="stylesheet" type="text/css" />'
//Output: '<script type="text/javascript" src="path-to-compiled-js-with-auto-file-name"></script>'.

// you can deploy only particular group of files
echo $this->minify->deploy_css(TRUE, NULL, 'another');
echo $this->minify->deploy_js(TRUE, 'auto', 'extra'); 

//Output: '<link href="path-to-compiled-css-group" rel="stylesheet" type="text/css" />'
//Output: '<script type="text/javascript" src="path-to-compiled-js-group-with-auto-file-name"></script>'.

// you can enable versioning your your assets via config variable `$config['versioning']` or manually
$this->minify->versioning = TRUE;
echo $this->minify->deploy_js(); 

//Output: '<script type="text/javascript" src="path-to-compiled.js?v=hash-here"></script>'.
```
    
## Changelog

17 Jun 2017
* new config variable to enable versioning assets `$config['versioning']` (default to FALSE)

29 Dec 2016
* introduce option to save compiled css and js files in different folders - new config variables: `$config['assets_dir_css']` and `$config['assets_dir_js']`.

29 Apr 2015
* allow to use automatic file name for particular deploy when you have `$config['auto_names']` set to `FALSE`
* documentation update

20 Apr 2015
* Closure compiler configuration extracted to config file 

22 Mar 2015
* method chaining support
* new methods: `add_css()` and `add_js()` - gives ability for adding files to existing files arrays
* added support to run library with custom array config, assigned as second parameter (during loading) `$this->load->library('minify', $config);`
* added support for *groups* in files arrays - as second (optional) parameter in methods: `css()`, `js()`, `add_css()` and `add_js()` (i.e. `$this->minify->js(array('script.js'), 'extra');` - default group name is *default*)
* added support for strings as first parameter in methods: `css()`, `js()`, `add_css()` and `add_js()` (i.e. `$this->minify->js('first.js, second.js');`)
* added support for automatic files names: `$config['auto_names'] = TRUE;`
* external compression classes moved to *minify* folder
* unit tests for new features

10 Feb 2015
* Unit testing

09 Feb 2015
* 2 new engines to compress JS files
* documentation update

13 Oct 2014
* changed way of generating JS file

14 July 2014
* small bug fixes in JS compression

4 July 2014
* sample JavaScript files to see how it works 
* detection of empty JS file causes force refresh

23 May 2014

* you can chose your compression engine library in config file (CSS only)
* speed optimisations
* force CSS rewrite using $this->minify->deploy_css(TRUE);

11 Mar 2014

* completely rewrite CSS parser - uses cssmin compress CSS,
* detects file modification time no longer force rewrites,
* example usage now included withing app

## Any questions?

Report theme here: <https://github.com/slav123/CodeIgniter-minify/issues>
