<?php
/**
 * Minify Library Class
 *
 * PHP Version 5.3
 *
 * @category  PHP
 * @package   Libraryr
 * @author    Slawomir Jasinski <slav123@gmail.com>
 * @copyright 2013 All Rights Reserved SpiderSoft
 * @license   Copyright 2013 All Rights Reserved Imagination
 * @link      Location: http://github.com/slav123/CodeIgniter-Minify
 */

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * the Minify LibraryClass
 *
 * @category  PHP
 * @package   Controller
 * @author    Slawomir Jasinski <slav123@gmail.com>
 * @copyright 2013 All Rights Reserved SpiderSoft
 * @license   Copyright 2012 All Rights Reserved SpiderSoft
 * @link      http://www.SpiderSoft.com,au
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
	 * @var string
	 */
	var $css_file = '';

	/**
	 * @var string
	 */
	var $js_file = '';

	/**
	 * @var int
	 */
	var $compress = TRUE;

	/**
	 * @var
	 */
	var $_inHack;

	var $assets_dir = '';

	private $_lmod = array('css', 'js');

	/**
	 *
	 */
	function __construct()
	{
		$this->ci =& get_instance();
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
			if (!is_writable($this->assets_dir))
			{
				die('Assets directory is not writeable');
			}
		}

		// process CSS file
		if (empty($this->css_file))
		{
			$this->css_file = $this->assets_dir . '/style.css';

			if ($this->compress)
			{
				$this->css_file = $this->assets_dir . '/style.min.css';
			}

			if (file_exists($this->css_file) && !is_writable($this->css_file))
			{
				die("Can\'t write to CSS file {$this->css_file}");
			}

			if (!file_exists($this->css_file))
			{
				if (!touch($this->css_file))
				{
					die("Can't create file {$this->css_file}");
				}
			}
			else
			{
				$this->_lmod['css'] = filemtime($this->css_file);
			}
		}
		else
		{
			$this->css_file = $this->assets_dir . '/' . $this->css_file;
		}

		if (empty($this->js_file))
		{
			$this->js_file = $this->assets_dir . '/script.js';
			if ($this->compress)
			{
				$this->js_file = $this->assets_dir . '/script.min.js';
			}

			if (!file_exists($this->js_file))
			{
				if (!touch($this->js_file))
				{
					die("Can't create file {$this->js_file}");
				}
			} else {
				$this->_lmod['js'] = filemtime($this->js_file);
			}

		}
		else
		{
			$this->js_file = $this->assets_dir . '/' . $this->js_file;
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
	 * @param $type
	 */
	public function scan_files($type, $force)
	{
		switch ($type)
		{
			case 'css':
				$files_array = $this->css_array;
				$directory   = $this->css_dir;
				$out_file    = $this->css_file;
				break;
			case 'js':
				$files_array = $this->js_array;
				$directory   = $this->js_dir;
				$out_file    = $this->js_file;
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
					if (filemtime($filename) > $this->_lmod[$type]) {
						$compile = TRUE;
					}
				}
				else
				{
					die("File {$filename} is missing");
				}
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
			$handle   = fopen($out_file, 'r');
			$contents = fread($handle, filesize($out_file));
			fclose($handle);

			$handle = fopen($out_file, 'w');

			$engine = $this->ci->config->item('compression_engine', 'minify');
			if (preg_match("/.css$/i", $out_file)) {
				$engine = "_{$engine['css']}";
			}

			if (preg_match("/.js$/i", $out_file)) {
				$engine = $this->ci->config->item('compression_engine', 'minify');
				$engine = "_{$engine['js']}";
			}

			//fwrite($handle, $this->_process($contents));
			fwrite($handle, call_user_func(array($this, $engine), $contents));
			fclose($handle);
		}

	}

	/**
	 * grab css files into one file
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
			if (!$flag) return; // nothing was changed
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
	 * @return mixed
	 */
	public function deploy_css($force = TRUE)
	{

		$this->scan_files('css', $force);

		$this->ci->load->helper('html');

		return link_tag($this->css_file);
	}

	/**
	 * deploy js
	 *
	 * @param bool $refresh
	 *
	 * @return string
	 */
	public function deploy_js($force = FALSE)
	{
		$this->scan_files('js', $force);

		return "<script type=\"text/javascript\" src=\"" . base_url() . '/' . $this->js_file . "\"></script>";
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
	 * cssmin compression engine
	 */
	private function _cssmin($data)
	{
		require_once('cssmin-v3.0.1.php');

		return CssMin::minify($data);
	}

	/**
	 * Minify compression engine
	 *
	 * @package  Minify
	 * @authohor Stephen Clay <steve@mrclay.org>
	 * @author   http://code.google.com/u/1stvamp/ (Issue 64 patch)
	 */

	private function _minify($css)
	{
		$css = str_replace("\r\n", "\n", $css);

		// preserve empty comment after '>'
		// http://www.webdevout.net/css-hacks#in_css-selectors
		$css = preg_replace('@>/\\*\\s*\\*/@', '>/*keep*/', $css);

		// preserve empty comment between property and value
		// http://css-discuss.incutio.com/?page=BoxModelHack
		$css = preg_replace('@/\\*\\s*\\*/\\s*:@', '/*keep*/:', $css);
		$css = preg_replace('@:\\s*/\\*\\s*\\*/@', ':/*keep*/', $css);

		// apply callback to all valid comments (and strip out surrounding ws
		$css = preg_replace_callback('@\\s*/\\*([\\s\\S]*?)\\*/\\s*@'
			, array($this, '_commentCB'), $css);

		// remove ws around { } and last semicolon in declaration block
		$css = preg_replace('/\\s*{\\s*/', '{', $css);
		$css = preg_replace('/;?\\s*}\\s*/', '}', $css);

		// remove ws surrounding semicolons
		$css = preg_replace('/\\s*;\\s*/', ';', $css);

		// remove ws around urls
		$css = preg_replace('/
                url\\(      # url(
                \\s*
                ([^\\)]+?)  # 1 = the URL (really just a bunch of non right parenthesis)
                \\s*
                \\)         # )
            /x', 'url($1)', $css);

		// remove ws between rules and colons
		$css = preg_replace('/
                \\s*
                ([{;])              # 1 = beginning of block or rule separator
                \\s*
                ([\\*_]?[\\w\\-]+)  # 2 = property (and maybe IE filter)
                \\s*
                :
                \\s*
                (\\b|[#\'"-])        # 3 = first character of a value
            /x', '$1$2:$3', $css);

		// remove ws in selectors
		$css = preg_replace_callback('/
                (?:              # non-capture
                    \\s*
                    [^~>+,\\s]+  # selector part
                    \\s*
                    [,>+~]       # combinators
                )+
                \\s*
                [^~>+,\\s]+      # selector part
                {                # open declaration block
            /x'
			, array($this, '_selectorsCB'), $css);

		// minimize hex colors
		$css = preg_replace('/([^=])#([a-f\\d])\\2([a-f\\d])\\3([a-f\\d])\\4([\\s;\\}])/i'
			, '$1#$2$3$4$5', $css);

		// remove spaces between font families
		$css = preg_replace_callback('/font-family:([^;}]+)([;}])/'
			, array($this, '_fontFamilyCB'), $css);

		$css = preg_replace('/@import\\s+url/', '@import url', $css);

		// replace any ws involving newlines with a single newline
		$css = preg_replace('/[ \\t]*\\n+\\s*/', "\n", $css);

		// separate common descendent selectors w/ newlines (to limit line lengths)
		$css = preg_replace('/([\\w#\\.\\*]+)\\s+([\\w#\\.\\*]+){/', "$1\n$2{", $css);

		// Use newline after 1st numeric value (to limit line lengths).
		$css = preg_replace('/
            ((?:padding|margin|border|outline):\\d+(?:px|em)?) # 1 = prop : 1st numeric value
            \\s+
            /x'
			, "$1\n", $css);

		// prevent triggering IE6 bug: http://www.crankygeek.com/ie6pebug/
		$css = preg_replace('/:first-l(etter|ine)\\{/', ':first-l$1 {', $css);

		return trim($css);
	}

	/**
	 * Replace what looks like a set of selectors
	 *
	 * @param array $m regex matches
	 *
	 * @return string
	 */
	protected function _selectorsCB($m)
	{
		// remove ws around the combinators
		return preg_replace('/\\s*([,>+~])\\s*/', '$1', $m[0]);
	}

	/**
	 * Process a comment and return a replacement
	 *
	 * @param array $m regex matches
	 *
	 * @return string
	 */
	protected function _commentCB($m)
	{
		$hasSurroundingWs = (trim($m[0]) !== $m[1]);
		$m                = $m[1];
		// $m is the comment content w/o the surrounding tokens,
		// but the return value will replace the entire comment.
		if ($m === 'keep')
		{
			return '/**/';
		}
		if ($m === '" "')
		{
			// component of http://tantek.com/CSS/Examples/midpass.html
			return '/*" "*/';
		}
		if (preg_match('@";\\}\\s*\\}/\\*\\s+@', $m))
		{
			// component of http://tantek.com/CSS/Examples/midpass.html
			return '/*";}}/* */';
		}
		if ($this->_inHack)
		{
			// inversion: feeding only to one browser
			if (preg_match('@
                    ^/               # comment started like /*/
                    \\s*
                    (\\S[\\s\\S]+?)  # has at least some non-ws content
                    \\s*
                    /\\*             # ends like /*/ or /**/
                @x', $m, $n)
			)
			{
				// end hack mode after this comment, but preserve the hack and comment content
				$this->_inHack = FALSE;

				return "/*/{$n[1]}/**/";
			}
		}
		if (substr($m, - 1) === '\\')
		{ // comment ends like \*/
			// begin hack mode and preserve hack
			$this->_inHack = TRUE;

			return '/*\\*/';
		}
		if ($m !== '' && $m[0] === '/')
		{ // comment looks like /*/ foo */
			// begin hack mode and preserve hack
			$this->_inHack = TRUE;

			return '/*/*/';
		}
		if ($this->_inHack)
		{
			// a regular comment ends hack mode but should be preserved
			$this->_inHack = FALSE;

			return '/**/';
		}
		// Issue 107: if there's any surrounding whitespace, it may be important, so
		// replace the comment with a single space
		return $hasSurroundingWs // remove all other comments
			? ' '
			: '';
	}

	/**
	 * Process a font-family listing and return a replacement
	 *
	 * @param array $m regex matches
	 *
	 * @return string
	 */
	protected function _fontFamilyCB($m)
	{
		$m[1] = preg_replace('/
                \\s*
                (
                    "[^"]+"      # 1 = family in double qutoes
                    |\'[^\']+\'  # or 1 = family in single quotes
                    |[\\w\\-]+   # or 1 = unquoted family
                )
                \\s*
            /x', '$1', $m[1]);

		return 'font-family:' . $m[1] . $m[2];
	}
}
