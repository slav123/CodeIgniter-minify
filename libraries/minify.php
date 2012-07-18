<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Name:  minify
 *
 * Author: Slawomir Jasinski
 *          slav123@gmail.com
 * @slavomirj
 *
 *
 * Location: http://github.com/slav123/CodeIgniter-Minify
 * @uses http://code.google.com/p/minify/
 *
 * Created:  02-04-2011
 * Last update: 17-01-2012
 *
 * Description:
 *
 * Requirements: PHP5 or above
 *
 */

class minify
{

	/**
	 * CodeIgniter global
	 *
	 * @var string
	 **/
	protected $ci;

	var $css = '';
	var $js = '';
	var $css_array = array();
	var $js_array = array();
	var $css_file = '';
	var $js_file = '';
	var $compress = 1;
	var $_inHack;

	function __construct()
	{
		$this->ci =& get_instance();
		$this->ci->load->config('minify', TRUE);
		// Do something with $params

		if (empty($this->css_dir))
			$this->css_dir = $this->ci->config->item('css_dir', 'minify');

		if (empty($this->js_dir))
			$this->js_dir = $this->ci->config->item('js_dir', 'minify');

		if (empty($this->assets_dir))
			$this->assets_dir = $this->ci->config->item('assets_dir', 'minify');

		if (empty($this->css_file)) {
			$this->css_file = $this->assets_dir . '/style.css';
			if ($this->compress)
				$this->css_file = $this->assets_dir . '/style.min.css';
		} else {
			$this->css_file = $this->assets_dir . '/' . $this->css_file;
		}

		if (empty($this->js_file)) {
			$this->js_file = $this->assets_dir . '/js.js';
			if ($this->compress)
				$this->js_file = $this->assets_dir . '/js.min.js';
		} else {
			$this->js_file = $this->assets_dir . '/' . $this->js_file;
		}

	}

	public function css($css)
	{
		$this->css_array = $css;
	}

	public function js($js)
	{
		$this->js_array = $js;
	}

	/**
	 * join css files into one file
	 */
	public function join_css()
	{
		$css = $this->css_array;
		if (file_exists($this->css))
			$x = filemtime($this->css);
		else
			$x = 0;

		$z = 0;
		if (is_array($css)) {
			foreach ($css as $c) {
				$filename = $this->css_dir . "/" . $c;
				$z = filemtime($filename);
				if ($z > $x)
					$this->_merge_css($filename);
			}
		} else {
			$filename = $this->css_dir . "/" . $css;
			$z = filemtime($css);
			if ($z > $x)
				$this->_merge_css($css);
		}
	}

	/**
	 * grab css files into one file
	 */
	public function join_js()
	{
		$js = $this->js_array;
		if (file_exists($this->js_file))
			$x = filemtime($this->js_file);
		else
			$x = 0;

		$z = 0;
		if (is_array($js)) {
			foreach ($js as $j) {
				$filename = $this->js_dir . '/' . $j;
				if (file_exists($filename)) {
					$z = filemtime($filename);
					if ($z > $x)
						$this->_merge_js($filename);
				}
			}
		} else {
			$filename = $js;
			$z = filemtime($js);
			if ($z > $x)
				$this->_merge_js($filename);
		}
	}

	public function deploy_css($refresh = false)
	{
		if ($refresh) {
			unlink($this->assets_dir . '/' . $this->css_file);
			$this->join_css();
		}
		$this->ci->load->helper('html');
		return link_tag($this->assets_dir . '/' . $this->css_file);
	}

	public function deploy_js($refresh = false)
	{
		if ($refresh) {
			$this->join_js();
		}
		return "<script type=\"text/javascript\" src=\"" . base_url() . $this->assets_dir . '/' . $this->js_file . "\"></script>";
	}


	/**
	 *
	 * join all css files into one big file
	 *
	 * @param string $filename name of source file
	 */
	private function _merge_css($filename)
	{

		$handle = fopen($filename, "r");
		$contents = fread($handle, filesize($filename));
		fclose($handle);

		if ($this->compress)
			$contents = $this->_process($contents);

		if (! is_writable($this->assets_dir))
			die("Can't write to assets directory: {$this->assets_dir}");

		$fh = fopen($this->assets_dir . '/' . $this->css_file, 'a');
		fwrite($fh, $contents);
		fclose($fh);

	}

	/**
	 *
	 * join all js files into one big file
	 *
	 * @param string $filename name of source file
	 */
	private function _merge_js($filename)
	{

		$handle = fopen($filename, "r");
		$contents = fread($handle, filesize($filename));
		fclose($handle);

		if ($this->compress)
			$contents = $this->_compress_js($contents);

		$fh = fopen($this->assets_dir . '/' . $this->js_file, 'a');
		fwrite($fh, $contents);
		fclose($fh);

	}

	private function _clear_css()
	{
		if (file_exists($this->css))
			unlink($this->css);
	}

	private function _clear_js()
	{
		if (file_exists($this->js))
			unlink($this->js);
	}

	private function _compress_js($script)
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
	 * @package Minify
	 * @authohor Stephen Clay <steve@mrclay.org>
	 * @author http://code.google.com/u/1stvamp/ (Issue 64 patch)
	 */

	private function _process($css)
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
		$m = $m[1];
		// $m is the comment content w/o the surrounding tokens,
		// but the return value will replace the entire comment.
		if ($m === 'keep') {
			return '/**/';
		}
		if ($m === '" "') {
			// component of http://tantek.com/CSS/Examples/midpass.html
			return '/*" "*/';
		}
		if (preg_match('@";\\}\\s*\\}/\\*\\s+@', $m)) {
			// component of http://tantek.com/CSS/Examples/midpass.html
			return '/*";}}/* */';
		}
		if ($this->_inHack) {
			// inversion: feeding only to one browser
			if (preg_match('@
                    ^/               # comment started like /*/
                    \\s*
                    (\\S[\\s\\S]+?)  # has at least some non-ws content
                    \\s*
                    /\\*             # ends like /*/ or /**/
                @x', $m, $n)
			) {
				// end hack mode after this comment, but preserve the hack and comment content
				$this->_inHack = false;
				return "/*/{$n[1]}/**/";
			}
		}
		if (substr($m, - 1) === '\\') { // comment ends like \*/
			// begin hack mode and preserve hack
			$this->_inHack = true;
			return '/*\\*/';
		}
		if ($m !== '' && $m[0] === '/') { // comment looks like /*/ foo */
			// begin hack mode and preserve hack
			$this->_inHack = true;
			return '/*/*/';
		}
		if ($this->_inHack) {
			// a regular comment ends hack mode but should be preserved
			$this->_inHack = false;
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
