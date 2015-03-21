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
	 * @var object
	 */
	protected $ci;

	/**
	 * Css files array.
	 *
	 * @var array
	 */
	protected $css_array = array();

	/**
	 * Js files array.
	 *
	 * @var array
	 */
	protected $js_array = array();

	/**
	 * Assets dir.
	 *
	 * @var string
	 */
	public $assets_dir = 'assets';

	/**
	 * Css dir.
	 *
	 * @var string
	 */
	public $css_dir = 'assets/css';

	/**
	 * Js dir.
	 *
	 * @var string
	 */
	public $js_dir = 'assets/js';

	/**
	 * Output css file name.
	 *
	 * @var string
	 */
	public $css_file = 'styles.css';

	/**
	 * Output js file name.
	 *
	 * @var string
	 */
	public $js_file = 'scripts.js';

	/**
	 * Compress files or not.
	 *
	 * @var bool
	 */
	public $compress = TRUE;

	/**
	 * Compression engines.
	 *
	 * @var array
	 */
	public $compression_engine = array('css' => 'minify', 'js' => 'closurecompiler');

	/**
	 * Css file name with path.
	 *
	 * @var string
	 */
	private $_css_file = '';

	/**
	 * Js file name with path.
	 *
	 * @var string
	 */
	private $_js_file = '';

	/**
	 * Last modufication.
	 *
	 * @var array
	 */
	private $_lmod = array('css' => 0, 'js' => 0);

	/**
	 * Constructor
	 *
	 * @param array $config Config array
	 */
	public function __construct($config = array())
	{
		$this->ci = get_instance();
		$this->ci->load->config('minify', TRUE, TRUE);

		// user specified settings from config file
		$this->assets_dir         = $this->ci->config->item('assets_dir', 'minify') ?: $this->assets_dir;
		$this->css_dir            = $this->ci->config->item('css_dir', 'minify') ?: $this->css_dir;
		$this->js_dir             = $this->ci->config->item('js_dir', 'minify') ?: $this->js_dir;
		$this->css_file           = $this->ci->config->item('css_file', 'minify') ?: $this->css_file;
		$this->js_file            = $this->ci->config->item('js_file', 'minify') ?: $this->js_file;
		$this->auto_names         = $this->ci->config->item('auto_names', 'minify') ?: $this->auto_names;
		$this->compress           = $this->ci->config->item('compress', 'minify') ?: $this->compress;
		$this->compression_engine = $this->ci->config->item('compression_engine', 'minify') ?: $this->compression_engine;

		if (count($config) > 0)
		{
			// custom config array
			$this->initialize($config);
		}

		// perform checks
		$this->_config_checks();
		
		log_message('debug', "Minify Class Initialized");
	}

	//--------------------------------------------------------------------

	/**
	 * Perform config checks
	 *
	 * @return void
	 */
	private function _config_checks()
	{
		if ( ! is_writable($this->assets_dir))
		{
			throw new Exception('Assets directory ' . $this->assets_dir . ' is not writable');
		}

		if (empty($this->css_dir))
		{
			throw new Exception('CSS directory must be set');
		}

		if (empty($this->js_dir))
		{
			throw new Exception('JS directory must be set');
		}

		if (empty($this->css_file))
		{
			throw new Exception('CSS file name can\'t be empty');
		}

		if (empty($this->js_file))
		{
			throw new Exception('JS file name can\'t be empty');
		}

		if ($this->compress)
		{
			if ( ! isset($this->compression_engine['css']) OR empty($this->compression_engine['css']))
			{
				throw new Exception('Compression engine for CSS is required');
			}

			if ( ! isset($this->compression_engine['js']) OR empty($this->compression_engine['js']))
			{
				throw new Exception('Compression engine for JS is required');
			}
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Initialize with custom variables
	 *
	 * @param array $config Config array
	 *
	 * @return void
	 */
	public function initialize($config = array())
	{
		foreach ($config as $key => $val)
		{
			if (isset($this->$key))
			{
				$this->$key = $val;
			}
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Declare css files list
	 *
	 * @param mixed $css File or files names
	 *
	 * @return void
	 */
	public function css($css)
	{
		if (is_array($css))
		{
			$this->css_array = $css;
		}
		else 
		{
			$this->css_array = array_map('trim', explode(',', $css));
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Declare js files list
	 *
	 * @param mixed $js File or files names
	 *
	 * @return void
	 */
	public function js($js)
	{
		if (is_array($js))
		{
			$this->js_array = $js;
		}
		else 
		{
			$this->js_array = array_map('trim', explode(',', $js));
		}

		return $this;
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
		curl_setopt($ch, CURLOPT_POSTFIELDS, 'output_info=compiled_code&output_format=text&compilation_level=SIMPLE_OPTIMIZATIONS&js_code=' . urlencode($script));
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
