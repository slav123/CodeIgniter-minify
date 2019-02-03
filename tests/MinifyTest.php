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

	// test init test
	public function testInit()
	{

		// Arrange
		$this->minify = new Minify();

		// Assert
		$this->assertTrue(is_object($this->minify), 'is object');
	}

	// test js when functionality is disabled
	public function testJsDisabled()
	{
		$this->minify = new Minify();

		$this->minify->enabled = FALSE;
		$this->minify->assets_dir_js = 'assets/js';
		$this->minify->add_js(array('helpers.js'))->add_js('jqModal.js');

		$result = $this->minify->deploy_js(FALSE);
		$this->assertTrue(is_string($result), 'deploy with add_js');

		$this->assertEquals('<script type="text/javascript" src="http://minify.localhost/assets/js/helpers.js"></script>'.
			PHP_EOL.'<script type="text/javascript" src="http://minify.localhost/assets/js/jqModal.js"></script>',
			$result, 'output js with disabled library\'s functionality');
	}

	// test css when functionality is disabled
	public function testCssDisabled()
	{
		$this->minify = new Minify();

		$this->minify->enabled = FALSE;
		$this->minify->assets_dir_css = 'assets/css';
		$this->minify->add_css(array('style.css'))->add_css('browser-specific.css');

		$result = $this->minify->deploy_css(TRUE);
		$this->assertTrue(is_string($result), 'deploy with add_css');

		$this->assertEquals('<link href="http://minify.localhost/assets/css/style.css" rel="stylesheet" type="text/css" />'.
			PHP_EOL.'<link href="http://minify.localhost/assets/css/browser-specific.css" rel="stylesheet" type="text/css" />',
			$result, 'output css with disabled library\'s functionality');
	}

	// test js when no html tags
	public function testJsNoHtmlTagsWithDisabled()
	{
		$this->minify = new Minify();

		$this->minify->enabled = FALSE;
		$this->minify->html_tags = FALSE;
		$this->minify->assets_dir_js = 'assets/js';
		$this->minify->add_js(array('helpers.js'))->add_js('jqModal.js');

		$result = $this->minify->deploy_js(FALSE);
		$this->assertTrue(is_array($result), 'deploy with add_js');

		$this->assertEquals(array('http://minify.localhost/assets/js/helpers.js', 'http://minify.localhost/assets/js/jqModal.js'), $result, 'output js with no html tags');
	}

	// test css when no html tags
	public function testCssNoHtmlTagsWithDisabled()
	{
		$this->minify = new Minify();

		$this->minify->enabled = FALSE;
		$this->minify->html_tags = FALSE;
		$this->minify->assets_dir_css = 'assets/css';
		$this->minify->add_css(array('style.css'))->add_css('browser-specific.css');

		$result = $this->minify->deploy_css(TRUE);
		$this->assertTrue(is_array($result), 'deploy with add_css');

		$this->assertEquals(array('http://minify.localhost/assets/css/style.css', 'http://minify.localhost/assets/css/browser-specific.css'), $result, 'output css with no html tags');
	}

	// test js when no html tags
	public function testJsNoHtmlTagsWithEnabled()
	{
		$this->minify = new Minify();

		$this->minify->html_tags = FALSE;
		$this->minify->assets_dir_js = 'assets/js';
		$this->minify->add_js(array('helpers.js'))->add_js('jqModal.js');

		$result = $this->minify->deploy_js(FALSE);
		$this->assertTrue(is_array($result), 'deploy with add_js');

		$this->assertEquals(array('http://minify.localhost/assets/js/scripts.min.js'), $result, 'output js with no html tags');
	}

	// test css when no html tags
	public function testCssNoHtmlTagsWithEnabled()
	{
		$this->minify = new Minify();

		$this->minify->html_tags = FALSE;
		$this->minify->assets_dir_css = 'assets/css';
		$this->minify->add_css(array('style.css'))->add_css('browser-specific.css');

		$result = $this->minify->deploy_css(TRUE);
		$this->assertTrue(is_array($result), 'deploy with add_css');

		$this->assertEquals(array('http://minify.localhost/assets/css/styles.min.css'), $result, 'output css with no html tags');
	}

	// check js compression
	public function testJsCompress()
	{
		$this->minify = new Minify();

		$this->minify->js_dir = 'assets/js';
		$this->minify->js(array('helpers.js'));

		$result = $this->minify->deploy_js(FALSE, 'ut.js');
		$this->assertTrue(is_string($result), 'deploy js with name');

		$this->assertEquals($this->minify->js_file, 'ut.min.js', 'output js file name');
	}

	// check js compression with closule compiler
	public function testJsCompressWithClosureCompiler()
	{
		$this->minify = new Minify();

		$this->minify->js_dir = 'assets/js';
		$this->minify->compression_engine['js'] = 'closurecompiler';
		$this->minify->js(array('helpers.js'));

		$result = $this->minify->deploy_js(TRUE, 'ut.js');
		$this->assertTrue(is_string($result), 'deploy js with closurecompiler');
		$this->assertEquals($this->minify->js_file, 'ut.min.js', 'output js file name');

		$file_content = file_get_contents($this->minify->assets_dir.DIRECTORY_SEPARATOR.$this->minify->js_file);
		$this->assertNotContains('Error', $file_content, 'output file has errors');
		$this->assertTrue( ! empty($file_content), 'output is not empty');
		
	}

	// test css compresion
	public function testCssCompress()
	{
		$this->minify = new Minify();

		$this->minify->css_dir = 'assets/css';
		$this->minify->css(array('style.css'));

		$result = $this->minify->deploy_css(TRUE, 'ut.css');
		$this->assertTrue(is_string($result), 'deploy css with name');

		$this->assertEquals($this->minify->css_file, 'ut.min.css', 'output css file name');
	}

	// test js with auto names
	public function testJsCompressWithAutoNames()
	{
		$this->minify = new Minify();

		$this->minify->auto_names = TRUE;
		$this->minify->js_dir = 'assets/js';
		$this->minify->js(array('helpers.js'));

		$result = $this->minify->deploy_js(FALSE);
		$this->assertTrue(is_string($result), 'deploy js with auto name');

		$this->assertEquals($this->minify->js_file, '91e30b9b77dc616476b94acf4dbb25c1.min.js', 'output js auto file name');
	}

	// test css with auto names
	public function testCssCompressWithAutoNames()
	{
		$this->minify = new Minify();

		$this->minify->auto_names = TRUE;
		$this->minify->css_dir = 'assets/css';
		$this->minify->css(array('style.css'));

		$result = $this->minify->deploy_css(TRUE);
		$this->assertTrue(is_string($result), 'deploy css with auto name');

		$this->assertEquals($this->minify->css_file, '72ac8bfd7cb9dd0f9df9ef4aafe0c714.min.css', 'output css auto file name');
	}

	// test js add
	public function testJsCompressWithAdd()
	{
		$this->minify = new Minify();

		$this->minify->js_dir = 'assets/js';
		$this->minify->add_js(array('helpers.js'))->add_js('jqModal.js');

		$result = $this->minify->deploy_js(FALSE);
		$this->assertTrue(is_string($result), 'deploy with add_js');

		$this->assertEquals($this->minify->js_file, 'scripts.min.js', 'output js default file name');
	}

	// tetst css with cadd
	public function testCssCompressWithAdd()
	{
		$this->minify = new Minify();

		$this->minify->css_dir = 'assets/css';
		$this->minify->add_css(array('style.css'))->add_css('browser-specific.css');

		$result = $this->minify->deploy_css(TRUE);
		$this->assertTrue(is_string($result), 'deploy with add_css');

		$this->assertEquals($this->minify->css_file, 'styles.min.css', 'output css default file name');
	}


	public function testJsCompressWithIndividualAutoName()
	{
		$this->minify = new Minify();

		$this->minify->js_dir = 'assets/js';
		$this->minify->js(array('helpers.js'));

		$result = $this->minify->deploy_js(FALSE, 'auto');
		$this->assertTrue(is_string($result), 'deploy js with individual auto name');

		$this->assertEquals($this->minify->js_file, '91e30b9b77dc616476b94acf4dbb25c1.min.js', 'output js auto file name');
	}

	public function testCssCompressWithIndividualAutoName()
	{
		$this->minify = new Minify();

		$this->minify->css_dir = 'assets/css';
		$this->minify->css(array('style.css'));

		$result = $this->minify->deploy_css(TRUE, 'auto');
		$this->assertTrue(is_string($result), 'deploy css with individual auto name');

		$this->assertEquals($this->minify->css_file, '72ac8bfd7cb9dd0f9df9ef4aafe0c714.min.css', 'output css auto file name');
	}

	// feature/custom_folders_for_compiled_css_and_js
	public function testCustomJsPathForAssets()
	{
		$this->minify = new Minify();

		$this->minify->assets_dir_js = 'assets/js';
		$this->minify->js_dir = 'assets/js';
		$this->minify->js(array('helpers.js'));

		$result = $this->minify->deploy_js(TRUE, 'auto');
		$this->assertEquals('<script type="text/javascript" src="http://minify.localhost/assets/js/91e30b9b77dc616476b94acf4dbb25c1.min.js"></script>', $result, 'deploy js with custom save path');
	}

	//
	public function testCustomCssPathForAssets()
	{
		$this->minify = new Minify();

		$this->minify->assets_dir_css = 'assets/css';
		$this->minify->css_dir        = 'assets/css';
		$this->minify->css(array('style.css'));

		$result = $this->minify->deploy_css(TRUE, 'auto');
		$this->assertEquals('<link href="http://minify.localhost/assets/css/72ac8bfd7cb9dd0f9df9ef4aafe0c714.min.css" rel="stylesheet" type="text/css" />', $result, 'deploy css with custom save path');
	}

	public function testCssCompressWithGroupNames()
	{
		$this->minify = new Minify();

		$this->minify->css_dir = 'assets/css';
		$this->minify->add_css(array('style.css'), 'sample1')->add_css('browser-specific.css', 'sample2');

		$result = $this->minify->deploy_css(TRUE, NULL, 'sample1');
		$this->assertTrue(is_string($result), 'deploy with group name: sample1');

		$this->assertEquals($this->minify->css_file, 'sample1_styles.min.css', 'output css default file name for group: sample1');

		$result = $this->minify->deploy_css(TRUE, NULL, 'sample2');
		$this->assertTrue(is_string($result), 'deploy with group name: sample2');

		$this->assertEquals($this->minify->css_file, 'sample2_styles.min.css', 'output css default file name for group: sample2');
	}

	public function testJsCompressWithGroupNames()
	{
		$this->minify = new Minify();

		$this->minify->css_dir = 'assets/js';
		$this->minify->add_js(array('helpers.js'), 'sample1')->add_js('jqModal.js', 'sample2');

		$result = $this->minify->deploy_js(TRUE, NULL, 'sample1');
		$this->assertTrue(is_string($result), 'deploy with group name: sample1');

		$this->assertEquals($this->minify->js_file, 'sample1_scripts.min.js', 'output js default file name for group: sample1');

		$result = $this->minify->deploy_js(TRUE, NULL, 'sample2');
		$this->assertTrue(is_string($result), 'deploy with group name: sample2');

		$this->assertEquals($this->minify->js_file, 'sample2_scripts.min.js', 'output js default file name for group: sample2');
	}

	// test versioning js
	public function testJsVersioning()
	{
		$this->minify = new Minify();

		unlink(APPPATH.'../assets/js/scripts.min.js');

		$this->minify->assets_dir_js = 'assets/js';
		$this->minify->versioning = TRUE;
		$this->minify->add_js(array('helpers.js'))->add_js('jqModal.js');

		$result = $this->minify->deploy_js(FALSE);
		$this->assertTrue(is_string($result), 'deploy js with versioning');

		$this->assertEquals('<script type="text/javascript" src="http://minify.localhost/assets/js/scripts.min.js?v=a6a391594c356eb31f44360b30b40c6b"></script>', $result, 'output js default file name with version');
	}

	// test versioning css
	public function testCssVersioning()
	{
		$this->minify = new Minify();

		unlink(APPPATH.'../assets/css/styles.min.css');

		$this->minify->assets_dir_css = 'assets/css';
		$this->minify->versioning = TRUE;
		$this->minify->add_css(array('style.css'))->add_css('browser-specific.css');

		$result = $this->minify->deploy_css(TRUE);
		$this->assertTrue(is_string($result), 'deploy css with versioning');

		$this->assertEquals('<link href="http://minify.localhost/assets/css/styles.min.css?v=bde54117b391ceca3436e85e7ddf1851" rel="stylesheet" type="text/css" />', $result, 'output css default file name with versioning');
	}

	// test deploy on change js
	public function testJsDeployOnChangeFalse()
	{
		$this->minify = new Minify();

		$this->minify->assets_dir_js = 'assets/js';
		$this->minify->versioning = TRUE;

		// deploy on change turned off
		$this->minify->deploy_on_change = FALSE;
		$this->minify->js(array('helpers.js'));

		$result = $this->minify->deploy_js(FALSE);
		$this->assertTrue(is_string($result), 'deploy js with deploy_on_change = FALSE');

		$this->assertEquals('<script type="text/javascript" src="http://minify.localhost/assets/js/scripts.min.js?v=a6a391594c356eb31f44360b30b40c6b"></script>', $result, 'output js default file name without deploy_on_change');
	}

	// test deploy on change css
	public function testCssDeployOnChangeFalse()
	{
		$this->minify = new Minify();

		$this->minify->assets_dir_css = 'assets/css';
		$this->minify->versioning = TRUE;

		// deploy on change turned off
		$this->minify->deploy_on_change = FALSE;
		$this->minify->css(array('style.css'));

		$result = $this->minify->deploy_css(FALSE);
		$this->assertTrue(is_string($result), 'deploy css with deploy_on_change = FALSE');

		$this->assertEquals('<link href="http://minify.localhost/assets/css/styles.min.css?v=bde54117b391ceca3436e85e7ddf1851" rel="stylesheet" type="text/css" />', $result, 'output css default file name without deploy_on_change');
	}

	// test js with custom base url
	public function testJsWithChangedBaseUrl()
	{
		$this->minify = new Minify();

		$this->minify->html_tags = FALSE;
		$this->minify->base_url = 'http://cdn1.minify.localhost/';
		$this->minify->assets_dir_js = 'assets/js';
		$this->minify->add_js(array('helpers.js'))->add_js('jqModal.js');

		$result = $this->minify->deploy_js(FALSE);
		$this->assertTrue(is_array($result), 'deploy with add_js');

		$this->assertEquals(array('http://cdn1.minify.localhost/assets/js/scripts.min.js'), $result, 'output js with no html tags and custom base_url');
	}

	// test css with custom base url
	public function testCssWithChangedBaseUrl()
	{
		$this->minify = new Minify();

		$this->minify->html_tags = FALSE;
		$this->minify->base_url = 'http://cdn2.minify.localhost';
		$this->minify->assets_dir_css = 'assets/css';
		$this->minify->add_css(array('style.css'))->add_css('browser-specific.css');

		$result = $this->minify->deploy_css(TRUE);
		$this->assertTrue(is_array($result), 'deploy with add_css');

		$this->assertEquals(array('http://cdn2.minify.localhost/assets/css/styles.min.css'), $result, 'output css with no html tags and custom base_url');
	}

}