<?php

define('BASEPATH', 'application');
define('APPPATH', 'application/');

function get_instance()
{
	return new CI();
}

function base_url($string) {
	return $string;
}

function link_tag($string) {
	return $string;
}

function log_message($string1, $string2) {
	return;
}

class CI
{


	public function __construct()
	{
		$this->load   = new CI_Loader();
		$this->config = new CI_Config();
	}

	public function config()
	{

	}


}

/**
 * The loads happen in the constructor (before we can mock anything out),
 * so instead we'll fakeify the Loader
 */
class CI_Loader
{
	public function __construct()
	{
		$this->item = new CI_Config();
	}

	public function __call($method, $params = array())
	{


	}

}

class CI_Config
{
	public function __call($method, $params = array())
	{

		switch ($params[0]) {
			case 'assets_dir':
				return sys_get_temp_dir();
				break;
			case 'compression_engine':
				return array('js' => 'jsmin', 'css' => 'minify');
				break;
		}
	}
}


