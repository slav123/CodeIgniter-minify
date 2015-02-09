<?php
/**
 * Created by PhpStorm.
 * User: slav
 * Date: 10/02/15
 * Time: 9:51 AM
 */

class MinifyTest extends PHPUnit_Framework_TestCase {

	private $CI;

	public static function setUpBeforeClass()
	{
		$CI = get_instance();

	}

	public function testInit()
	{
		include('application/libraries/minify.php');
		// Arrange
		$minify = new Minify();

		// Assert
		$this->assertTrue(is_object($minify), 'is object');

		$this->assertEquals($minify->js_file, 'scripts.js', 'default js file name');
	}
}
