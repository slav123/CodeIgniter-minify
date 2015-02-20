<?php
/**
 * Minify Library Class
 *
 * PHP Version 5.3
 *
 * @category  PHP
 * @package   Libraryr
 * @author    Slawomir Jasinski <slav123@gmail.com>
 * @copyright 2014 All Rights Reserved SpiderSoft
 * @license   Copyright 2014 All Rights Reserved SpiderSoft
 * @link      Location: http://github.com/slav123/CodeIgniter-Minify
 */

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * the Minify LibraryClass
 *
 * @category  PHP
 * @package   Controller
 * @author    Slawomir Jasinski <slav123@gmail.com>
 * @copyright 2014 All Rights Reserved SpiderSoft
 * @license   Copyright 2014 All Rights Reserved SpiderSoft
 * @link      http://www.spidersoft.com.au
 */
class Minify
{

	/**
	 * CodeIgniter global.
	 *
	 * @var string
	 **/
	protected $ci;

	/**
	 * @var string
	 */
	var $css = '';

	/**
	 * @var string
	 */
	var $js = '';

	/**
	 * @var array
	 */
	var $css_array = array();

	/**
	 * @var array
	 */
	var $js_array = array();

	/**
	 * Public space for JS file name
	 *
	 * @var string
	 */
	public $js_file = 'scripts.js', $css_file = 'styles.css', $css_dir, $js_dir;

	/**
	 * Private js file name with path
	 *
	 * @var
	 */
	private $_js_file, $_css_file;

	/**
	 * @var int
	 */
	var $compress = TRUE;

	/**
	 * @var string
	 */
	var $_closure_optimization = 'SIMPLE_OPTIMIZATIONS';

	/**
	 * @var bool
	 */
	var $_closure_show_errors = FALSE;

	/**
	 * @var bool
	 */
	var $_closure_show_warnings = FALSE;

	/**
	 * @var
	 */
	var $_inHack;

	var $assets_dir = '';

	private $_lmod = array('css' => 0, 'js' => 0);

	/**
	 *
	 */
	function __construct()
	{
		$this->ci = get_instance();
		$this->ci->load->config('minify', TRUE);

		$this->_setup();
	}

	/**
	 * some basic setup
	 *
	 */
	private function _setup()
	{

		// assign variables from confif file
		if (empty($this->css_dir))
		{
			$this->css_dir = $this->ci->config->item('css_dir', 'minify');
		}

		// check JS dir
		if (empty($this->js_dir))
		{
			$this->js_dir = $this->ci->config->item('js_dir', 'minify');
		}

		// check general assets dir
		if (empty($this->assets_dir))
		{
			$this->assets_dir = $this->ci->config->item('assets_dir', 'minify');
			if ( ! is_writable($this->assets_dir))
			{
				die("Assets directory {$this->assets_dir} is not writeable");
			}
		}

		$this->_set('js_file', $this->js_file);
		$this->_set('css_file', $this->css_file);

	}

	/**
	 * construct js_file and css_file
	 *
	 * @param string $name
	 * @param string $value
	 */
	private function _set($name, $value)
	{

		switch ($name)
		{
			case 'js_file':

				if ($this->compress)
				{
					if (!preg_match("/\.min\.js$/", $value)) {
						$value = str_replace('.js', '.min.js', $value);
					}

					$this->js_file = $value;
				}

				$this->_js_file = $this->assets_dir . '/' . $value;

				if ( ! file_exists($this->_js_file) && ! touch($this->_js_file))
				{
					die("Can not create file {$this->_js_file}");
				}
				else
				{
					$this->_lmod['js'] = filemtime($this->_js_file);
				}

				break;
			case 'css_file':

				if ($this->compress)
				{
					$value = str_replace('.css', '.min.css', $value);
					$this->css_file = $value;
				}

				$this->_css_file = $this->assets_dir . '/' . $value;

				if ( ! file_exists($this->_css_file) && !touch($this->_css_file))
				{
					die("Can not create file {$this->_css_file}");
				}
				else
				{
					try
					{
						$this->_lmod['css'] = filemtime($this->_css_file);
					} catch (Exception $e) {
						echo $e->getMessage();
					}
				}
				break;
		}


	}

	/**
	 * @param $css
	 */
	public function css($css)
	{
		$this->css_array = $css;
	}

	/**
	 * @param $js
	 */
	public function js($js)
	{
		$this->js_array = $js;
	}

	/**
	 * scan CSS direcctory and look for changes
	 *
	 * @param string $type  css | js
	 * @param bool   $force rewrite no mather what
	 */
	public function scan_files($type, $force)
	{
		switch ($type)
		{
			case 'css':
				$files_array = $this->css_array;
				$directory   = $this->css_dir;
				$out_file    = $this->_css_file;
				break;
			case 'js':
				$files_array = $this->js_array;
				$directory   = $this->js_dir;
				$out_file    = $this->_js_file;
		}

		// if multiple files
		if (is_array($files_array))
		{
			$compile = FALSE;
			foreach ($files_array as $file)
			{
				$filename = $directory . '/' . $file;

				if (file_exists($filename))
				{
					if (filemtime($filename) > $this->_lmod[$type])
					{
						$compile = TRUE;
					}
				}
				else
				{
					die("File {$filename} is missing");
				}
			}

			// check if this is init build
			if (file_exists($out_file) && filesize($out_file) === 0)
			{
				$force = TRUE;
			}

			if ($compile || $force)
			{
				$this->_concat_files($files_array, $directory, $out_file);
			}
		}

	}

	/**
	 * add merge files
	 *
	 * @param string $file_array input file array
	 * @param        $directory
	 * @param string $out_file   output file
	 *
	 * @internal param string $filename file name
	 */
	private function _concat_files($file_array, $directory, $out_file)
	{

		if ($fh = fopen($out_file, 'w'))
		{
			foreach ($file_array as $file_name)
			{
				$file_name = $directory . '/' . $file_name;
				$handle    = fopen($file_name, 'r');
				$contents  = fread($handle, filesize($file_name));
				fclose($handle);

				fwrite($fh, $contents);
			}
			fclose($fh);
		}
		else
		{
			die("Can't write to {$out_file}");
		}


		if ($this->compress)
		{
			// read output file contenst (already concated)
			$handle   = fopen($out_file, 'r');
			$contents = fread($handle, filesize($out_file));
			fclose($handle);

			// recreate file
			$handle = fopen($out_file, 'w');

			//get engine file from config file
			$engine = $this->ci->config->item('compression_engine', 'minify');
			if (preg_match("/.css$/i", $out_file))
			{
				$engine = "_{$engine['css']}";
			}

			if (preg_match("/.js$/i", $out_file))
			{
				$engine = $this->ci->config->item('compression_engine', 'minify');
				$engine = "_{$engine['js']}";
			}

			// get function name to compress file

			//fwrite($handle, $this->_process($contents));
			fwrite($handle, call_user_func(array($this, $engine), $contents));
			fclose($handle);
		}

	}

	/**
	 * grab js files into one file
	 */
	public function join_js()
	{
		$js = $this->js_array;
		if (file_exists($this->js_file))
		{
			$x = filemtime($this->js_file);
		}
		else
		{
			$x = 0;
		}

		$flag = FALSE; // flag to check if any of the file was changed to rebuild all the set of files
		if (is_array($js))
		{
			foreach ($js as $j)
			{
				$filename = $this->js_dir . '/' . $j;
				if (file_exists($filename) && filemtime($filename) > $x)
				{
					$flag = TRUE;
					break;
				}
			}
			if ( ! $flag)
			{
				return;
			} // nothing was changed
			@unlink($this->js_file);
			foreach ($js as $j)
			{
				$filename = $this->js_dir . '/' . $j;
				if (file_exists($filename))
				{
					$this->_merge_js($filename);
				}
			}
		}
		else
		{
			$filename = $this->css_dir . "/" . $js;
			if (file_exists($filename) && filemtime($filename) > $x)
			{
				@unlink($this->js_file);
				$this->_merge_js($filename);
			}
		}
	}

	/**
	 * deploy and minify CSS
	 *
	 * @param bool $force     force to rewrite file
	 * @param null $file_name file name to create
	 *
	 * @return mixed
	 */
	public function deploy_css($force = TRUE, $file_name = NULL)
	{

		if ( ! is_null($file_name))
		{
			$this->_set('css_file', $file_name);
		}

		$this->scan_files('css', $force);

		$this->ci->load->helper('html');

		return link_tag($this->_css_file);
	}

	/**
	 * deploy js
	 *
	 * @param bool $force     force rewriting js file
	 * @param null $file_name file name
	 *
	 * @return string
	 */
	public function deploy_js($force = FALSE, $file_name = NULL)
	{
		if ( ! is_null($file_name))
		{
			$this->_set('js_file', $file_name);
		}
		$this->scan_files('js', $force);

		return '<script type="text/javascript" src="' . base_url($this->_js_file) . '"></script>';
	}


	/**
	 * reset closure compiler options to their default value
	 */
	public function closure_reset_options()
	{
		$this->_closure_optimization = 'SIMPLE_OPTIMIZATIONS';
		$this->_closure_warnings = FALSE;
		$this->_closure_errors = FALSE;
		return $this;
	}

	/**
	 * set optimization level for the closure compiler
	 *
	 * @param string $opt optimization level (can be "WHITESPACE_ONLY", "SIMPLE_OPTIMIZATIONS" or "ADVANCED_OPTIMIZATIONS")
	 *
	 */
	public function closure_set_optimization($opt = '')
	{
		$this->_closure_optimization = $opt;
		return $this;
	}

	/**
	 * enable/disable closure compiler warnings (they will be printed among the js code so be sure to disable it in production)
	 *
	 * @param bool $warnings TRUE to enable warnings, FALSE to disable them
	 *
	 */
	public function closure_warnings($warnings)
	{
		$this->_closure_show_warnings = $warnings;
		return $this;
	}

	/**
	 * enable/disable closure compiler errors (they will be printed among the js code so be sure to disable it in production)
	 *
	 * @param bool $errors TRUE to enable errors, FALSE to disable them
	 *
	 */
	public function closure_errors($errors)
	{
		$this->_closure_show_errors = $errors;
		return $this;
	}

	/**
	 * compress javascript using closure compiler service
	 *
	 * @param string $script source to compress
	 *
	 * @return mixed
	 */
	private function _closurecompiler($script)
	{
		$ch = curl_init('http://closure-compiler.appspot.com/compile');

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, ($this->_closure_show_errors ? 'output_info=errors&' : '' ) . ($this->_closure_show_warnings ? 'output_info=warnings&' : '' ) . 'output_info=compiled_code&output_format=text&compilation_level=' . $this->_closure_optimization . '&js_code=' . urlencode($script));
		$output = curl_exec($ch);
		curl_close($ch);

		return $output;
	}

	/**
	 * implements jsmin as alternavive to closure compiler
	 *
	 * @param $data
	 *
	 * @return string
	 */
	private function _jsmin($data)
	{
		require_once('JSMin.php');

		return JSMin::minify($data);
	}

	/**
	 * implements jsminplus as alternavive to closure compiler
	 *
	 * @param $data
	 *
	 * @return string
	 */
	private function _jsminplus($data)
	{
		require_once('JSMinPlus.php');

		return JSMinPlus::minify($data);
	}

	/**
	 * cssmin compression engine
	 *
	 * @param $data
	 *
	 * @return string
	 */
	private function _cssmin($data)
	{
		require_once('cssmin-v3.0.1.php');

		return CssMin::minify($data);
	}

	private function _minify($data) {
		require_once('cssminify.php');
		$cssminify = new cssminify();
		return $cssminify->compress($data);
	}
}
