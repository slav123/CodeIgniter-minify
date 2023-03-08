<?php
/**
 * Minify Library Class
 *
 * PHP Version 5.3
 *
 * @category  PHP
 * @package   Library
 * @author    Slawomir Jasinski <slav123@gmail.com>
 * @copyright 2015 All Rights Reserved SpiderSoft
 * @license   Copyright 2015 All Rights Reserved SpiderSoft
 * @link      Location: http://github.com/slav123/CodeIgniter-Minify
 */

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * the Minify LibraryClass
 *
 * @category  PHP
 * @package   Controller
 * @author    Slawomir Jasinski <slav123@gmail.com>
 * @copyright 2016 All Rights Reserved SpiderSoft
 * @license   Copyright 2015 All Rights Reserved SpiderSoft
 * @link      http://www.spidersoft.com.au
 */
class Minify {
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
	 * Enable/disable.
	 *
	 * @var bool
	 */
	public $enabled = TRUE;

	/**
	 * Assets dir.
	 *
	 * @var string
	 */
	public $assets_dir = 'assets';

	/**
	 * Assets dir for css (optional).
	 *
	 * @var string
	 */
	public $assets_dir_css = '';

	/**
	 * Assets dir for js (optional).
	 *
	 * @var string
	 */
	public $assets_dir_js = '';

	/**
	 * Base URL.
	 *
	 * @var string
	 */
	public $base_url = '';

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
	 * Output css tag template.
	 *
	 * @var string
	 */
	public $css_tag = '<link href="%s" rel="stylesheet" type="text/css" />';

	/**
	 * Output js tag template.
	 *
	 * @var string
	 */
	public $js_tag = '<script type="text/javascript" src="%s"></script>';

	/**
	 * Use html tags on output.
	 *
	 * @var string
	 */
	public $html_tags = TRUE;

	/**
	 * Automatic file names.
	 *
	 * @var bool
	 */
	public $auto_names = FALSE;

	/**
	 * Automatic deploy on change.
	 *
	 * @var bool
	 */
	public $deploy_on_change = TRUE;

	/**
	 * File versioning.
	 *
	 * @var bool
	 */
	public $versioning = FALSE;

	/**
	 * File version number override.
	 *
	 * @var string
	 */
	public $version_number = NULL;

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
	 * Closurecompiler settings.
	 *
	 * @var array
	 */
	public $closurecompiler = array('compilation_level' => 'SIMPLE_OPTIMIZATIONS');

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
	 * Last modification.
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
		$this->enabled            = $this->ci->config->item('enabled', 'minify') ?: $this->enabled;
		$this->assets_dir         = $this->ci->config->item('assets_dir', 'minify') ?: $this->assets_dir;
		$this->assets_dir_css     = $this->ci->config->item('assets_dir_css', 'minify') ?: $this->assets_dir_css;
		$this->assets_dir_js      = $this->ci->config->item('assets_dir_js', 'minify') ?: $this->assets_dir_js;
		$this->base_url           = $this->ci->config->item('base_url', 'minify') ?: $this->base_url;
		$this->css_dir            = $this->ci->config->item('css_dir', 'minify') ?: $this->css_dir;
		$this->js_dir             = $this->ci->config->item('js_dir', 'minify') ?: $this->js_dir;
		$this->css_file           = $this->ci->config->item('css_file', 'minify') ?: $this->css_file;
		$this->js_file            = $this->ci->config->item('js_file', 'minify') ?: $this->js_file;
		$this->css_tag            = $this->ci->config->item('css_tag', 'minify') ?: $this->css_tag;
		$this->js_tag             = $this->ci->config->item('js_tag', 'minify') ?: $this->js_tag;
		$this->html_tags          = $this->ci->config->item('html_tags', 'minify') ?: $this->html_tags;
		$this->auto_names         = $this->ci->config->item('auto_names', 'minify') ?: $this->auto_names;
		$this->deploy_on_change   = $this->ci->config->item('deploy_on_change', 'minify') ?: $this->deploy_on_change;
		$this->versioning         = $this->ci->config->item('versioning', 'minify') ?: $this->versioning;
		$this->version_number     = $this->ci->config->item('version_number', 'minify') ?: $this->version_number;
		$this->compress           = $this->ci->config->item('compress', 'minify') ?: $this->compress;
		$this->compression_engine = $this->ci->config->item('compression_engine',
		                                                    'minify') ?: $this->compression_engine;
		$this->closurecompiler    = $this->ci->config->item('closurecompiler', 'minify') ?: $this->closurecompiler;

		if (count($config) > 0)
		{
			// custom config array
			foreach ($config as $key => $val)
			{
				if (isset($this->$key))
				{
					$this->$key = $val;
				}
			}
		}

		// save default names for later use/reset
		$this->css_file_default = $this->css_file;
		$this->js_file_default  = $this->js_file;


		log_message('debug', "Minify Class Initialized");
	}

	/**
	 * Declare css files list
	 *
	 * @param mixed $css   File or files names
	 * @param bool  $group Set group for files
	 *
	 * @return Minify
	 */
	public function css($css, $group = 'default')
	{
		if (is_array($css))
		{
			$this->css_array[$group] = $css;
		}
		else
		{
			$this->css_array[$group] = array_map('trim', explode(',', $css));
		}

		return $this;
	}

	/**
	 * Declare js files list
	 *
	 * @param mixed $js    File or files names
	 * @param bool  $group Set group for files
	 *
	 * @return Minify
	 */
	public function js($js, $group = 'default')
	{
		if (is_array($js))
		{
			$this->js_array[$group] = $js;
		}
		else
		{
			$this->js_array[$group] = array_map('trim', explode(',', $js));
		}

		return $this;
	}

	/**
	 * Declare css files list
	 *
	 * @param mixed $css   File or files names
	 * @param bool  $group Set group for files
	 *
	 * @return Minify
	 */
	public function add_css($css, $group = 'default')
	{
		if ( ! isset($this->css_array[$group]))
		{
			$this->css_array[$group] = array();
		}

		if (is_array($css))
		{
			$this->css_array[$group] = array_unique(array_merge($this->css_array[$group], $css));
		}
		else
		{
			$this->css_array[$group] = array_unique(array_merge($this->css_array[$group],
			                                                    array_map('trim', explode(',', $css))));
		}

		return $this;
	}

	/**
	 * Declare js files list
	 *
	 * @param mixed $js    File or files names
	 * @param bool  $group Set group for files
	 *
	 * @return Minify
	 */
	public function add_js($js, $group = 'default')
	{
		if ( ! isset($this->js_array[$group]))
		{
			$this->js_array[$group] = array();
		}

		if (is_array($js))
		{
			$this->js_array[$group] = array_unique(array_merge($this->js_array[$group], $js));
		}
		else
		{
			$this->js_array[$group] = array_unique(array_merge($this->js_array[$group],
			                                                   array_map('trim', explode(',', $js))));
		}

		return $this;
	}

	/**
	 * Deploy and minify CSS
	 *
	 * @param bool $force     Force to rewrite file
	 * @param null $file_name File name to create
	 * @param null $group     Group name
	 *
	 * @return string|array
	 */
	public function deploy_css($force = TRUE, $file_name = NULL, $group = NULL)
	{
		// perform checks
		$this->_config_checks('css');

		$return = array();

		if (is_null($file_name))
		{
			$file_name = $this->css_file_default;
		}

		if (is_null($group))
		{
			foreach ($this->css_array as $group_name => $group_array)
			{
				$return = array_merge($return, $this->_deploy_css($force, $file_name, $group_name));
			}
		}
		else
		{
			$return = array_merge($return, $this->_deploy_css($force, $file_name, $group));
		}

		return $this->_output($return, 'css');
	}

	/**
	 * Deploy and minify js
	 *
	 * @param bool $force     Force rewriting js file
	 * @param null $file_name File name
	 * @param null $group     Group name
	 *
	 * @return string|array
	 */
	public function deploy_js($force = FALSE, $file_name = NULL, $group = NULL)
	{
		// perform checks
		$this->_config_checks('js');

		$return = array();

		if (is_null($file_name))
		{
			$file_name = $this->js_file_default;
		}

		if (is_null($group))
		{
			foreach ($this->js_array as $group_name => $group_array)
			{
				$return = array_merge($return, $this->_deploy_js($force, $file_name, $group_name));
			}
		}
		else
		{
			$return = array_merge($return, $this->_deploy_js($force, $file_name, $group));
		}

		return $this->_output($return, 'js');
	}

	/**
	 * Build and minify CSS
	 *
	 * @param bool $force     Force to rewrite file
	 * @param null $file_name File name to create
	 * @param null $group     Group name
	 *
	 * @return array
	 */
	private function _deploy_css($force = TRUE, $file_name = NULL, $group = NULL)
	{
		if ($this->enabled === FALSE)
		{
			return $this->_simple_output('css', $group);
		}

		if ($this->auto_names or $file_name === 'auto')
		{
			$file_name = md5(serialize($this->css_array[$group])) . '.css';
		}
		else
		{
			$file_name = ($group === 'default') ? $file_name : $group . '_' . $file_name;
		}

		$this->_set('css_file', $file_name);

		$this->_scan_files('css', $force, $group);

		if ($this->versioning)
		{
			$this->_css_file = $this->_css_file . '?v=' . $this->_version_number($this->_css_file);
		}

		return [$this->_css_file];
	}

	/**
	 * Build and minify js
	 *
	 * @param bool $force     Force rewriting js file
	 * @param null $file_name File name
	 * @param null $group     Group name
	 *
	 * @return array
	 */
	private function _deploy_js($force = FALSE, $file_name = NULL, $group = NULL)
	{
		if ($this->enabled === FALSE)
		{
			return $this->_simple_output('js', $group);
		}

		if ($this->auto_names or $file_name === 'auto')
		{
			$file_name = md5(serialize($this->js_array[$group])) . '.js';
		}
		else
		{
			$file_name = ($group === 'default') ? $file_name : $group . '_' . $file_name;
		}

		$this->_set('js_file', $file_name);

		$this->_scan_files('js', $force, $group);

		if ($this->versioning)
		{
			$this->_js_file = $this->_js_file . '?v=' . $this->_version_number($this->_js_file);
		}

		return [$this->_js_file];
	}

	/**
	 * construct js_file and css_file
	 *
	 * @param string $name  File type
	 * @param string $value File name
	 *
	 * @return void
	 */
	private function _set($name, $value)
	{
		switch ($name)
		{
			case 'js_file':

				if ($this->compress)
				{
					if ( ! preg_match("/\.min\.js$/", $value))
					{
						$value = str_replace('.js', '.min.js', $value);
					}

					$this->js_file = $value;
				}

				// determine if we have special dir for js specified
				$assets_dir     = empty($this->assets_dir_js) ? $this->assets_dir : $this->assets_dir_js;
				$this->_js_file = $assets_dir . '/' . $value;

				if ( ! file_exists($this->_js_file) && ! touch($this->_js_file))
				{
					throw new Exception('Can not create file ' . $this->_js_file);
				}
				else
				{
					$this->_lmod['js'] = filemtime($this->_js_file);
				}

			break;
			case 'css_file':

				if ($this->compress)
				{
					if ( ! preg_match("/\.min\.css$/", $value))
					{
						$value = str_replace('.css', '.min.css', $value);
					}

					$this->css_file = $value;
				}

				// determine if we have special dir for css specified
				$assets_dir      = empty($this->assets_dir_css) ? $this->assets_dir : $this->assets_dir_css;
				$this->_css_file = $assets_dir . '/' . $value;

				if ( ! file_exists($this->_css_file) && ! touch($this->_css_file))
				{
					throw new Exception('Can not create file ' . $this->_css_file);
				}
				else
				{
					$this->_lmod['css'] = filemtime($this->_css_file);
				}

			break;
		}
	}


	/**
	 * scan CSS directory and look for changes
	 *
	 * @param string $type  Type (css | js)
	 * @param bool   $force Rewrite no mather what
	 * @param string $group Group name
	 */
	private function _scan_files($type, $force, $group)
	{
		switch ($type)
		{
			case 'css':
				$files_array = $this->css_array[$group];
				$directory   = $this->css_dir;
				$out_file    = $this->_css_file;
			break;
			case 'js':
				$files_array = $this->js_array[$group];
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
					if ($this->deploy_on_change && filemtime($filename) > $this->_lmod[$type])
					{
						$compile = TRUE;
					}
				}
				else
				{
					throw new Exception('File ' . $filename . ' is missing');
				}
			}

			// check if this is init build
			if (file_exists($out_file) && filesize($out_file) === 0)
			{
				$force = TRUE;
			}

			if ($compile or $force)
			{
				$this->_concat_files($files_array, $directory, $out_file);
			}
		}
	}

	/**
	 * output files with proper template for html_tags
	 * or without as array
	 *
	 * @param array  $files Files array
	 * @param string $type  Type (css | js)
	 *
	 * @return string|array
	 */
	private function _output($files, $type)
	{
		switch ($type)
		{
			case 'css':
				$template = $this->css_tag;
			break;
			case 'js':
				$template = $this->js_tag;
		}

		$output = array();

		foreach ($files as $file)
		{
			$output[] = $this->html_tags ? sprintf($template, $this->_base_url($file)) : $this->_base_url($file);
		}

		if ( ! empty($output))
		{
			return $this->html_tags ? implode(PHP_EOL, $output) : $output;
		}

		return $this->html_tags ? '' : array();
	}

	/**
	 * simple output files - no compress, no compile (files in = files out)
	 * good for debugging or development env
	 *
	 * @param string $type  Type (css | js)
	 * @param string $group Group name
	 *
	 * @return array
	 */
	private function _simple_output($type, $group)
	{
		switch ($type)
		{
			case 'css':
				$files     = $this->css_array[$group];
				$directory = $this->css_dir;
				$template  = $this->css_tag;
			break;
			case 'js':
				$files     = $this->js_array[$group];
				$directory = $this->js_dir;
				$template  = $this->js_tag;
		}

		$output = array();

		foreach ($files as $file)
		{
			$filename = $directory . '/' . $file;

			if ($this->versioning)
			{
				$filename .= '?v=' . $this->_version_number($filename);
			}

			$output[] = $filename;
		}

		return $output;
	}

	/**
	 * add merge files
	 *
	 * @param string $file_array Input file array
	 * @param string $directory  Directory
	 * @param string $out_file   Output file
	 *
	 * @return void
	 */
	private function _concat_files($file_array, $directory, $out_file)
	{

		if ($fh = fopen($out_file, 'w'))
		{
			foreach ($file_array as $file_name)
			{
				$file_name = $directory . '/' . $file_name;
				$contents  = file_get_contents($file_name);

				// if this is javascript file, check if we have ; at the end
				if (preg_match("/.js$/i", $out_file))
				{
					if (substr(rtrim($contents), - 1) !== ';')
					{
						$contents .= ';';
					}

					$contents .= "\n";
				}
				fwrite($fh, $contents);
			}
			fclose($fh);
		}
		else
		{
			throw new Exception('Can\'t write to ' . $out_file);
		}

		if ($this->compress)
		{
			// read output file contest (already concated)
			$contents = file_get_contents($out_file);

			// recreate file
			$handle = fopen($out_file, 'w');

			if (preg_match("/.css$/i", $out_file))
			{
				$engine = '_' . $this->compression_engine['css'];
			}

			if (preg_match("/.js$/i", $out_file))
			{
				$engine = '_' . $this->compression_engine['js'];
			}

			// call function name to compress file
			fwrite($handle, call_user_func(array($this, $engine), $contents));
			fclose($handle);
		}
	}

	/**
	 * Compress javascript using closure compiler service
	 *
	 * @param string $data Source to compress
	 *
	 * @return mixed
	 */
	private function _closurecompiler($data)
	{
		$config = $this->closurecompiler;

		$ch = curl_init('https://closure-compiler.appspot.com/compile');
		
		//if server is not https
		if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off')
		{
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		}
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,
		            'output_info=compiled_code&output_info=errors&output_format=text&compilation_level=' . $config['compilation_level'] . '&js_code=' . urlencode($data));
		$output = curl_exec($ch);
		curl_close($ch);

		if (preg_match('/Input_0:[0-9]+: ERROR/', $output))
		{
			throw new Exception('Closure Compiler error: ' . $output);
		}

		return $output;
	}

	/**
	 * Implements jsmin as alternative to closure compiler
	 *
	 * @param string $data Source to compress
	 *
	 * @return string
	 */
	private function _jsmin($data)
	{
		require_once(APPPATH . 'libraries/minify/JSMin.php');

		return JSMin::minify($data);
	}

	/**
	 * Implements jsminplus as alternative to closure compiler
	 *
	 * @param string $data Source to compress
	 *
	 * @return string
	 */
	private function _jsminplus($data)
	{
		require_once(APPPATH . 'libraries/minify/JSMinPlus.php');

		return JSMinPlus::minify($data);
	}

	/**
	 * Implements cssmin compression engine
	 *
	 * @param string $data Source to compress
	 *
	 * @return string
	 */
	private function _cssmin($data)
	{
		require_once(APPPATH . 'libraries/minify/cssmin-v3.0.1.php');

		return CssMin::minify($data);
	}

	/**
	 * Implements cssminify compression engine
	 *
	 * @param string $data Source to compress
	 *
	 * @return string
	 */
	private function _minify($data)
	{
		require_once(APPPATH . 'libraries/minify/cssminify.php');
		$cssminify = new cssminify();

		return $cssminify->compress($data);
	}

	/**
	 * Build correct URL for file
	 *
	 * @param string $file File with path
	 *
	 * @return string
	 */
	private function _base_url($file)
	{
		if ($this->base_url === '')
		{
			return base_url($file);
		}

		return rtrim($this->base_url, '/') . '/' . $file;
	}

	/**
	 * Perform config checks
	 *
	 * @param $type string CSS / JS check
	 *
	 * @return void
	 * @throws Exception
	 */
	private function _config_checks($type)
	{

		switch ($type)
		{
			case 'css':
				if (empty($this->assets_dir_css) && ! is_writable($this->assets_dir))
				{
					throw new Exception('Assets directory ' . $this->assets_dir . ' is not writable');
				}
				if ( ! empty($this->assets_dir_css) && ! is_writable($this->assets_dir_css))
				{
					throw new Exception('Assets directory for css ' . $this->assets_dir_css . ' is not writable');
				}
				if (empty($this->css_dir))
				{
					throw new Exception('CSS directory must be set');
				}
				if ($this->html_tags === TRUE && empty($this->css_tag))
				{
					throw new Exception('CSS tag template must be set');
				}
				if ( ! $this->auto_names)
				{
					if (empty($this->css_file))
					{
						throw new Exception('CSS file name can\'t be empty');
					}
				}

				if ($this->compress)
				{
					if ( ! isset($this->compression_engine['css']) or empty($this->compression_engine['css']))
					{
						throw new Exception('Compression engine for CSS is required');
					}
				}
			break;
			case 'js':
				if (empty($this->assets_dir_js) && ! is_writable($this->assets_dir))
				{
					throw new Exception('Assets directory ' . $this->assets_dir . ' is not writable');
				}
				if ( ! empty($this->assets_dir_js) && ! is_writable($this->assets_dir_js))
				{
					throw new Exception('Assets directory for js ' . $this->assets_dir_js . ' is not writable');
				}
				if (empty($this->js_dir))
				{
					throw new Exception('JS directory must be set');
				}
				if ($this->html_tags === TRUE && empty($this->js_tag))
				{
					throw new Exception('JS tag template must be set');
				}
				if ( ! $this->auto_names)
				{

					if (empty($this->js_file))
					{
						throw new Exception('JS file name can\'t be empty');
					}
				}
				if ($this->compress)
				{
					if ( ! isset($this->compression_engine['js']) or empty($this->compression_engine['js']))
					{
						throw new Exception('Compression engine for JS is required');
					}

					if ($this->compression_engine['js'] === 'closurecompiler' && ( ! isset($this->closurecompiler['compilation_level']) or empty($this->closurecompiler['compilation_level'])))
					{
						throw new Exception('Compilation level for closurecompiler is needed');
					}
				}
			break;
		}

	}

	/**
	 * Get Version Number for file
	 *
	 * @param string $file File with path
	 *
	 * @return string
	 */
	private function _version_number($file)
	{
		if ( ! empty($this->version_number))
		{
			return $this->version_number;
		}

		return md5_file($file);
	}

}
/* End of file Minify.php */
/* Location: ./libraries/Minify.php */
