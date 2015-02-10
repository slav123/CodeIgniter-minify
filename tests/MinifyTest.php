<?php
/**
 * Created by PhpStorm.
 * User: slav
 * Date: 10/02/15
 * Time: 9:51 AM
 */

class MinifyTest extends PHPUnit_Framework_TestCase {


	public function testInit()
	{
		include('application/libraries/Minify.php');
		// Arrange
		$minify = new Minify();

		// Assert
		$this->assertTrue(is_object($minify), 'is object');

		$this->assertEquals($minify->js_file, 'scripts.js', 'default js file name');

		$this->assertEquals($minify->css_file, 'styles.css', 'default css file name');
	}
}
