<?php
/**
 * Created by PhpStorm.
 * User: slav
 * Date: 10/02/15
 * Time: 9:51 AM
 */

class MinifyTest extends PHPUnit_Framework_TestCase {

	public $minify;

	public function setUp() {
		include_once('application/libraries/Minify.php');
	}

	public function testInit()
	{

		// Arrange
		$this->minify = new Minify();

		// Assert
		$this->assertTrue(is_object($this->minify), 'is object');

		$this->assertEquals($this->minify->js_file, 'scripts.min.js', 'default js file name');

		$this->assertEquals($this->minify->css_file, 'styles.min.css', 'default css file name');
	}

	public function testJsCompress()
	{
		$this->minify = new Minify();

		$this->minify->js_dir = 'assets/js';
		$this->minify->js(array('helpers.js'));

		$this->assertTrue(is_string($this->minify->deploy_js(FALSE, 'ut.js')));
	}

	public function testCssCompress()
	{
		$this->minify = new Minify();

		$this->minify->css_dir = 'assets/css';
		$this->minify->css(array('style.css'));

		$result = $this->minify->deploy_css(TRUE, 'ut.css');



		$this->assertTrue(is_string($result));
	}
}
