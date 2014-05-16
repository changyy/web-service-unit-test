<?php
//
//  test on Ubuntu 14.04
//  $ apt-get install phpunit libxml2-utils wget
//
//  $ phpunit web-service-unit-test.php
//
class WebServiceUnitTest extends PHPUnit_Framework_TestCase
{
	public function __construct()
	{
		parent::__construct();

		$target_cgi = 'localhost';
		if(preg_match( '{/home/(.+?)/}',__FILE__ , $matches ))
			$target_cgi .= "/~".$matches[1];
		$this->target_cgi = "http://$target_cgi";

		// testing usage
		$this->target_web_service = 'https://github.com/';
		$this->target_api_service = 'https://api.github.com/';

		$this->case_enable = array(
			'testWeb' => true,
			'testAPI' => true,
			'' => false
		);
	}
	public function testWeb()
	{
		if( !$this->case_enable[__FUNCTION__] )
			return;
		$webpage_content = shell_exec( "wget -qO- $this->target_web_service");
		//$webpage_content = file_get_contents($this->target_cgi);

		// check size
		$this->assertTrue(strlen($webpage_content) > 0);

		// check html format
		$temp_file = tempnam(sys_get_temp_dir(), 'html_');
		$this->assertTrue(file_put_contents($temp_file, $webpage_content) !== false);
		$this->assertTrue(empty(shell_exec('xmllint --html --noout --valid "'.$temp_file.'"')));
		$this->assertTrue(unlink($temp_file));

		// check links
		$raw_link = array();
		if( preg_match_all( '/href=["\'](.+?)["\']/', $webpage_content, $matches ) )
			$raw_link = $matches[1];
		$this->assertTrue(count($raw_link) > 10);
	}
	public function testAPI()
	{
		if( !$this->case_enable[__FUNCTION__] )
			return;
		$webpage_content = shell_exec( "wget -qO- $this->target_api_service");
		//$webpage_content = file_get_contents($this->target_cgi);

		// check size
		$this->assertTrue(strlen($webpage_content) > 0);

		// check json format
		@json_decode($webpage_content);
		$this->assertTrue(json_last_error() == JSON_ERROR_NONE);
	}
}
