<?php

class ParserTest extends TestCase {

	public function test_Read_Last_Lines()
    {
        $this->assertCount( 1 , LogParser::getLinesFromBottom( PHPMOCKUP . '/apache2.4_error.log' ) );
        $this->assertCount( 3 , LogParser::getLinesFromBottom( PHPMOCKUP . '/apache2.4_error.log' , 3 ) );
    }

    // Tests with multi line and timezone
	public function test_PHP54_User_Errors()
    {
		$regex         = "@^\\[(.*)-(.*)-(.*) (.*):(.*):(.*)( (.*))*\\] (.*)\$@U";
		$match         = array( "Date" => array( 2 , " " , 1 , " " , 4 , ":" , 5 , ":" , 6 , " " , 3 , " " , 8 ) , "Error" => 9 );
		$types         = array( "Date" => "date:H:i:s" , 'Error' => "txt" );
		$multiline     = 'Error';
		$exclude       = '';
		$file_path     = PHPMOCKUP . '/php_error_log_5.4.log';
		$start_offset  = 0;
		$start_from    = SEEK_END;
		$load_more     = false;
		$old_lastline  = '';
		$data_to_parse = filesize( $file_path );
		$full          = true;
		$timeout       = 2;

		$logs = LogParser::getNewLines( $regex , $match , $types , 'Europe/Paris' , 2 , $exclude , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , '' , $data_to_parse , $full , $timeout );
		$this->assertEquals( 2 , $logs['count'] );
		$this->assertFalse( strpos( $logs['logs'][0]['Date'] , 'ERROR ! Unable to convert this string to date') );

		$logs = LogParser::getNewLines( $regex , $match , $types , 'Europe/Paris' , 100 , $exclude , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , '' , $data_to_parse , $full , $timeout );
		$this->assertEquals( 4 , $logs['count'] );
		$this->assertNotEquals( '12:47:45' , $logs['logs'][0]['Date'] ); // America/Adak should be different
		$this->assertEquals( '12:41:36' , $logs['logs'][1]['Date'] ); // Server and this log are in the same timezone, result has to be the same
		$this->assertFalse( strpos( $logs['logs'][0]['Date'] , 'ERROR ! Unable to convert this string to date') );

		$logs = LogParser::getNewLines( $regex , $match , $types , 'Europe/Paris' , 100 , $exclude , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , 'coucou' , $data_to_parse , $full , $timeout );
		$this->assertEquals( 2 , $logs['count'] );
		$this->assertFalse( strpos( $logs['logs'][0]['Date'] , 'ERROR ! Unable to convert this string to date') );

		$logs = LogParser::getNewLines( $regex , $match , $types , 'Europe/Paris' , 100 , $exclude , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , '|12:3[0-9]|' , $data_to_parse , $full , $timeout );
		$this->assertEquals( 2 , $logs['count'] );
		$this->assertFalse( strpos( $logs['logs'][0]['Date'] , 'ERROR ! Unable to convert this string to date') );
    }

	public function test_PHP53_User_Errors()
    {
		$regex         = "@^\\[(.*)-(.*)-(.*) (.*):(.*):(.*)( (.*))*\\] ((PHP (.*):  (.*) in (.*) on line (.*))|(.*))\$@U";
		$match         = array( "Date" => array( 2 , " " , 1 , " " , 4 , ":" , 5 , ":" , 6 , " " , 3 ), "Severity" => 11, "Error"    => array( 12 , 15 ), "File"     => 13, "Line"     => 14 );
		$types         = array( "Date" => "date:H:i:s", "Severity" => "badge:severity", "File"     => "pre:\/-69", "Line"     => "numeral", "Error"    => "pre" );
		$multiline     = '';
		$exclude       = '';
		$file_path     = PHPMOCKUP . '/php_error_log_5.3.log';
		$start_offset  = 0;
		$start_from    = SEEK_END;
		$load_more     = false;
		$old_lastline  = '';
		$data_to_parse = filesize( $file_path );
		$full          = true;
		$timeout       = 2;

		$logs = LogParser::getNewLines( $regex , $match , $types , 'Europe/Paris' , 2 , $exclude , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , '' , $data_to_parse , $full , $timeout );
		$this->assertEquals( 2 , $logs['count'] );
		$this->assertFalse( strpos( $logs['logs'][0]['Date'] , 'ERROR ! Unable to convert this string to date') );

		$logs = LogParser::getNewLines( $regex , $match , $types , 'Europe/Paris' , 100 , $exclude , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , '' , $data_to_parse , $full , $timeout );
		$this->assertEquals( 18 , $logs['count'] );
		$this->assertFalse( strpos( $logs['logs'][0]['Date'] , 'ERROR ! Unable to convert this string to date') );

		$logs = LogParser::getNewLines( $regex , $match , $types , 'Europe/Paris' , 100 , $exclude , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , 'Pimp my Log' , $data_to_parse , $full , $timeout );
		$this->assertEquals( 2 , $logs['count'] );
		$this->assertFalse( strpos( $logs['logs'][0]['Date'] , 'ERROR ! Unable to convert this string to date') );

		$logs = LogParser::getNewLines( $regex , $match , $types , 'Europe/Paris' , 100 , $exclude , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , '|10$|' , $data_to_parse , $full , $timeout );
		$this->assertEquals( 2 , $logs['count'] );
		$this->assertFalse( strpos( $logs['logs'][0]['Date'] , 'ERROR ! Unable to convert this string to date') );
    }

	public function test_PHP_System_Error()
    {
		$regex         = "@^\\[(.*)-(.*)-(.*) (.*):(.*):(.*) (.*)\\] ((PHP (.*):  (.*) in (.*) on line (.*))|(.*))\$@U";
		$match         = array( "Date" => array( 2 , " " , 1 , " " , 4 , ":" , 5 , ":" , 6 , " " , 3 , " " , 7 ), "Severity" => 10, "Error"    => array( 11 , 14 ), "File"     => 12, "Line"     => 13 );
		$types         = array( "Date"     => "date:H:i:s", "Severity" => "badge:severity", "File"     => "pre:\/-69", "Line"     => "numeral", "Error"    => "pre" );
		$multiline     = 'Error';
		$exclude       = '';
		$file_path     = PHPMOCKUP . '/php_system_error_log.log';
		$start_offset  = 0;
		$start_from    = SEEK_END;
		$load_more     = false;
		$old_lastline  = '';
		$data_to_parse = filesize( $file_path );
		$full          = true;
		$timeout       = 2;

		$logs = LogParser::getNewLines( $regex , $match , $types , 'Europe/Paris' , 2 , $exclude , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , '' , $data_to_parse , $full , $timeout );
		$this->assertEquals( 2 , $logs['count'] );
		$this->assertFalse( strpos( $logs['logs'][0]['Date'] , 'ERROR ! Unable to convert this string to date') );

		$logs = LogParser::getNewLines( $regex , $match , $types , 'Europe/Paris' , 100 , $exclude , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , '' , $data_to_parse , $full , $timeout );
		$this->assertEquals( 3 , $logs['count'] );
		$this->assertFalse( strpos( $logs['logs'][0]['Date'] , 'ERROR ! Unable to convert this string to date') );

		$logs = LogParser::getNewLines( $regex , $match , $types , 'Europe/Paris' , 100 , $exclude , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , 'file_get_contents' , $data_to_parse , $full , $timeout );
		$this->assertEquals( 1 , $logs['count'] );
		$this->assertFalse( strpos( $logs['logs'][0]['Date'] , 'ERROR ! Unable to convert this string to date') );

		$logs = LogParser::getNewLines( $regex , $match , $types , 'Europe/Paris' , 100 , $exclude , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , '|on line [0-9]$|' , $data_to_parse , $full , $timeout );
		$this->assertEquals( 2 , $logs['count'] );
		$this->assertEquals( 2 , $logs['count'] );
		$this->assertFalse( strpos( $logs['logs'][0]['Date'] , 'ERROR ! Unable to convert this string to date') );
    }

	public function test_Apache22_Error()
    {
		$regex         = "|^\\[(.*)\\] \\[(.*)\\] (\\[client (.*)\\] )*((?!\\[client ).*)(, referer: (.*))*\$|U";
		$match         = array( "Date"=>1,"IP"=>4,"Log"=>5,"Severity"=>2,"Referer"=>7 );
		$types         = array( "Date"=>"date:H:i:s","IP"=>"ip:http","Log"=>"pre","Severity"=>"badge:severity","Referer"=>"link" );
		$multiline     = '';
		$exclude       = '';
		$file_path     = PHPMOCKUP . '/apache2.2_error.log';
		$start_offset  = 0;
		$start_from    = SEEK_END;
		$load_more     = false;
		$old_lastline  = '';
		$data_to_parse = filesize( $file_path );
		$full          = true;
		$timeout       = 2;

		$logs = LogParser::getNewLines( $regex , $match , $types , 'Europe/Paris' , 10 , $exclude , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , '' , $data_to_parse , $full , $timeout );
		$this->assertEquals( 10 , $logs['count'] );
		$this->assertFalse( strpos( $logs['logs'][0]['Date'] , 'ERROR ! Unable to convert this string to date') );

		$logs = LogParser::getNewLines( $regex , $match , $types , 'Europe/Paris' , 100 , $exclude , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , '' , $data_to_parse , $full , $timeout );
		$this->assertEquals( 47 , $logs['count'] );
		$this->assertFalse( strpos( $logs['logs'][0]['Date'] , 'ERROR ! Unable to convert this string to date') );

		$logs = LogParser::getNewLines( $regex , $match , $types , 'Europe/Paris' , 100 , $exclude , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , 'File does not exist:' , $data_to_parse , $full , $timeout );
		$this->assertEquals( 35 , $logs['count'] );
		$this->assertFalse( strpos( $logs['logs'][0]['Date'] , 'ERROR ! Unable to convert this string to date') );

		$logs = LogParser::getNewLines( $regex , $match , $types , 'Europe/Paris' , 100 , $exclude , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , '|^\[Mon|' , $data_to_parse , $full , $timeout );
		$this->assertEquals( 37 , $logs['count'] );
		$this->assertFalse( strpos( $logs['logs'][0]['Date'] , 'ERROR ! Unable to convert this string to date') );
    }


	public function test_Apache22_Access()
    {
		$regex         = "|^((\\S*) )*(\\S*) (\\S*) (\\S*) \\[(.*)\\] \"(\\S*) (.*) (\\S*)\" ([0-9]*) (.*)( \"(.*)\" \"(.*)\"( [0-9]*/([0-9]*))*)*\$|U";
		$match         = array( "Date"    => 6, "IP"      => 3, "CMD"     => 7, "URL"     => 8, "Code"    => 10, "Size"    => 11, "Referer" => 13, "UA"      => 14, "User"    => 5, "\u03bcs" => 16 );
		$types         = array( "Date"    => "date:H:i:s", "IP"      => "ip:geo", "URL"     => "txt", "Code"    => "badge:http", "Size"    => "numeral:0b", "Referer" => "link", "UA"      => "ua:{os.name} {os.version} | {browser.name} {browser.version}\/100", "\u03bcs" => "numeral:0,0" );
		$multiline     = '';
		$exclude       = '';
		$file_path     = PHPMOCKUP . '/apache2.2_access.log';
		$start_offset  = 0;
		$start_from    = SEEK_END;
		$load_more     = false;
		$old_lastline  = '';
		$data_to_parse = filesize( $file_path );
		$full          = true;
		$timeout       = 2;

		$logs = LogParser::getNewLines( $regex , $match , $types , 'Europe/Paris' , 10 , $exclude , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , '' , $data_to_parse , $full , $timeout );
		$this->assertEquals( 10 , $logs['count'] );
		$this->assertFalse( strpos( $logs['logs'][0]['Date'] , 'ERROR ! Unable to convert this string to date') );

		$logs = LogParser::getNewLines( $regex , $match , $types , 'Europe/Paris' , 100 , $exclude , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , '' , $data_to_parse , $full , $timeout );
		$this->assertEquals( 26 , $logs['count'] );
		$this->assertFalse( strpos( $logs['logs'][0]['Date'] , 'ERROR ! Unable to convert this string to date') );

		$logs = LogParser::getNewLines( $regex , $match , $types , 'Europe/Paris' , 100 , $exclude , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , 'WordPress' , $data_to_parse , $full , $timeout );
		$this->assertEquals( 3 , $logs['count'] );
		$this->assertFalse( strpos( $logs['logs'][0]['Date'] , 'ERROR ! Unable to convert this string to date') );

		$logs = LogParser::getNewLines( $regex , $match , $types , 'Europe/Paris' , 100 , $exclude , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , '|^62\.129\.7\.178|' , $data_to_parse , $full , $timeout );
		$this->assertEquals( 4 , $logs['count'] );
		$this->assertFalse( strpos( $logs['logs'][0]['Date'] , 'ERROR ! Unable to convert this string to date') );
    }



	public function test_Apache24_Error()
    {
		$regex         = "|^\\[(.*) (.*) (.*) (.*):(.*):(.*)\\.(.*) (.*)\\] \\[(.*):(.*)\\] \\[pid (.*)\\] .*\\[client (.*):(.*)\\] (.*)(, referer: (.*))*\$|U";
		$match         = array( "Date"=>1,"IP"=>4,"Log"=>5,"Severity"=>2,"Referer"=>7 );
		$types         = array( "Date"=>"date:H:i:s","IP"=>"ip:http","Log"=>"pre","Severity"=>"badge:severity","Referer"=>"link" );
		$multiline     = '';
		$exclude       = '';
		$file_path     = PHPMOCKUP . '/apache2.4_error.log';
		$start_offset  = 0;
		$start_from    = SEEK_END;
		$load_more     = false;
		$old_lastline  = '';
		$data_to_parse = filesize( $file_path );
		$full          = true;
		$timeout       = 2;

		$logs = LogParser::getNewLines( $regex , $match , $types , 'Europe/Paris' , 10 , $exclude , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , '' , $data_to_parse , $full , $timeout );
		$this->assertEquals( 10 , $logs['count'] );
		$this->assertFalse( strpos( $logs['logs'][0]['Date'] , 'ERROR ! Unable to convert this string to date') );

		$logs = LogParser::getNewLines( $regex , $match , $types , 'Europe/Paris' , 100 , $exclude , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , '' , $data_to_parse , $full , $timeout );
		$this->assertEquals( 20 , $logs['count'] );
		$this->assertFalse( strpos( $logs['logs'][0]['Date'] , 'ERROR ! Unable to convert this string to date') );

		$logs = LogParser::getNewLines( $regex , $match , $types , 'Europe/Paris' , 100 , $exclude , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , '127.0.0.1' , $data_to_parse , $full , $timeout );
		$this->assertEquals( 1 , $logs['count'] );
		$this->assertFalse( strpos( $logs['logs'][0]['Date'] , 'ERROR ! Unable to convert this string to date') );

		$logs = LogParser::getNewLines( $regex , $match , $types , 'Europe/Paris' , 100 , $exclude , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , '|^\[Sat|' , $data_to_parse , $full , $timeout );
		$this->assertEquals( 17 , $logs['count'] );
		$this->assertFalse( strpos( $logs['logs'][0]['Date'] , 'ERROR ! Unable to convert this string to date') );
    }


	public function test_Apache24_Access()
    {
		$regex         = "|^((\\S*) )*(\\S*) (\\S*) (\\S*) \\[(.*)\\] \"(\\S*) (.*) (\\S*)\" ([0-9]*) (.*)( \"(.*)\" \"(.*)\"( [0-9]*/([0-9]*))*)*\$|U";
		$match         = array( "Date"    => 6, "IP"      => 3, "CMD"     => 7, "URL"     => 8, "Code"    => 10, "Size"    => 11, "Referer" => 13, "UA"      => 14, "User"    => 5, "\u03bcs" => 16 );
		$types         = array( "Date"    => "date:H:i:s", "IP"      => "ip:geo", "URL"     => "txt", "Code"    => "badge:http", "Size"    => "numeral:0b", "Referer" => "link", "UA"      => "ua:{os.name} {os.version} | {browser.name} {browser.version}\/100", "\u03bcs" => "numeral:0,0" );
		$multiline     = '';
		$exclude       = '';
		$file_path     = PHPMOCKUP . '/apache2.4_access.log';
		$start_offset  = 0;
		$start_from    = SEEK_END;
		$load_more     = false;
		$old_lastline  = '';
		$data_to_parse = filesize( $file_path );
		$full          = true;
		$timeout       = 2;

		$logs = LogParser::getNewLines( $regex , $match , $types , 'Europe/Paris' , 10 , $exclude , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , '' , $data_to_parse , $full , $timeout );
		$this->assertEquals( 10 , $logs['count'] );
		$this->assertFalse( strpos( $logs['logs'][0]['Date'] , 'ERROR ! Unable to convert this string to date') );

		$logs = LogParser::getNewLines( $regex , $match , $types , 'Europe/Paris' , 100 , $exclude , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , '' , $data_to_parse , $full , $timeout );
		$this->assertEquals( 22 , $logs['count'] );
		$this->assertFalse( strpos( $logs['logs'][0]['Date'] , 'ERROR ! Unable to convert this string to date') );

		$logs = LogParser::getNewLines( $regex , $match , $types , 'Europe/Paris' , 100 , $exclude , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , 'favicon.ico' , $data_to_parse , $full , $timeout );
		$this->assertEquals( 2 , $logs['count'] );
		$this->assertFalse( strpos( $logs['logs'][0]['Date'] , 'ERROR ! Unable to convert this string to date') );

		$logs = LogParser::getNewLines( $regex , $match , $types , 'Europe/Paris' , 100 , array( "URL" => array(  "/favicon.ico/" ) ) , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , 'favicon.ico' , $data_to_parse , $full , $timeout );
		$this->assertEquals( 0 , $logs['count'] );

		$logs = LogParser::getNewLines( $regex , $match , $types , 'Europe/Paris' , 100 , $exclude , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , '|^::1|' , $data_to_parse , $full , $timeout );
		$this->assertEquals( 18 , $logs['count'] );
		$this->assertFalse( strpos( $logs['logs'][0]['Date'] , 'ERROR ! Unable to convert this string to date') );
    }

	public function test_IIS_IIS()
    {
		$regex         = '|^(.*), (.*), (.*), (.*), (.*), (.*), (.*), (.*), (.*), (.*), (.*), (.*), (.*), (.*), (.*),(.*)$|U';
		$match         = array( "Date"    => array( 3 , " " , 4 ), "IP"      => 1, "Site"    => 5, "CMD"     => 13, "URL"     => 14, "Code"    => 11, "Size"    => 10, "User"    => 2, "ms"      => 8 );
		$types         = array( "Date"    => "date:H:i:s", "IP"      => "ip:geo", "URL"     => "txt", "Code"    => "badge:http", "Size"    => "numeral:0b", "ms"      => "numeral:0,0" );
		$multiline     = '';
		$exclude       = '';
		$file_path     = PHPMOCKUP . '/iis_iis.log';
		$start_offset  = 0;
		$start_from    = SEEK_END;
		$load_more     = false;
		$old_lastline  = '';
		$data_to_parse = filesize( $file_path );
		$full          = true;
		$timeout       = 2;

		$logs = LogParser::getNewLines( $regex , $match , $types , 'Europe/Paris' , 10 , $exclude , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , '' , $data_to_parse , $full , $timeout );
		$this->assertEquals( 10 , $logs['count'] );
		$this->assertFalse( strpos( $logs['logs'][0]['Date'] , 'ERROR ! Unable to convert this string to date') );

		$logs = LogParser::getNewLines( $regex , $match , $types , 'Europe/Paris' , 100 , $exclude , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , '' , $data_to_parse , $full , $timeout );
		$this->assertEquals( 61 , $logs['count'] );
		$this->assertFalse( strpos( $logs['logs'][0]['Date'] , 'ERROR ! Unable to convert this string to date') );

		$logs = LogParser::getNewLines( $regex , $match , $types , 'Europe/Paris' , 100 , $exclude , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , 'main.css' , $data_to_parse , $full , $timeout );
		$this->assertEquals( 4 , $logs['count'] );
		$this->assertFalse( strpos( $logs['logs'][0]['Date'] , 'ERROR ! Unable to convert this string to date') );

		$logs = LogParser::getNewLines( $regex , $match , $types , 'Europe/Paris' , 100 , array( "URL" => array(  "/main.css/" ) ) , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , 'main.css' , $data_to_parse , $full , $timeout );
		$this->assertEquals( 0 , $logs['count'] );

		$logs = LogParser::getNewLines( $regex , $match , $types , 'Europe/Paris' , 100 , $exclude , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , '/main\\.(css|js)/' , $data_to_parse , $full , $timeout );
		$this->assertEquals( 5 , $logs['count'] );
		$this->assertFalse( strpos( $logs['logs'][0]['Date'] , 'ERROR ! Unable to convert this string to date') );
    }

	public function test_IIS_NCSA_Common()
    {
		$regex         = '|^((\\S*) )*(\\S*) (\\S*) (\\S*) \\[(.*)\\] \"(\\S*) (.*) (\\S*)\" ([0-9]*) (.*)( \"(.*)\" \"(.*)\"( [0-9]*/([0-9]*))*)*$|U';
		$match         = array( "Date"    => 6, "IP"      => 3, "CMD"     => 7, "URL"     => 8, "Code"    => 10, "Size"    => 11, "User"    => 5 );
		$types         = array( "Date"    => "date:H:i:s", "IP"      => "ip:geo", "URL"     => "txt", "Code"    => "badge:http", "Size"    => "numeral:0b" );
		$multiline     = '';
		$exclude       = '';
		$file_path     = PHPMOCKUP . '/iis_ncsa_common.log';
		$start_offset  = 0;
		$start_from    = SEEK_END;
		$load_more     = false;
		$old_lastline  = '';
		$data_to_parse = filesize( $file_path );
		$full          = true;
		$timeout       = 2;

		$logs = LogParser::getNewLines( $regex , $match , $types , 'Europe/Paris' , 10 , $exclude , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , '' , $data_to_parse , $full , $timeout );
		$this->assertEquals( 10 , $logs['count'] );
		$this->assertFalse( strpos( $logs['logs'][0]['Date'] , 'ERROR ! Unable to convert this string to date') );

		$logs = LogParser::getNewLines( $regex , $match , $types , 'Europe/Paris' , 1000 , $exclude , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , '' , $data_to_parse , $full , $timeout );
		$this->assertEquals( 141 , $logs['count'] );
		$this->assertFalse( strpos( $logs['logs'][0]['Date'] , 'ERROR ! Unable to convert this string to date') );

		$logs = LogParser::getNewLines( $regex , $match , $types , 'Europe/Paris' , 100 , $exclude , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , 'main.css' , $data_to_parse , $full , $timeout );
		$this->assertEquals( 10 , $logs['count'] );
		$this->assertFalse( strpos( $logs['logs'][0]['Date'] , 'ERROR ! Unable to convert this string to date') );

		$logs = LogParser::getNewLines( $regex , $match , $types , 'Europe/Paris' , 100 , array( "URL" => array(  "/main.css/" ) ) , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , 'main.css' , $data_to_parse , $full , $timeout );
		$this->assertEquals( 0 , $logs['count'] );

		$logs = LogParser::getNewLines( $regex , $match , $types , 'Europe/Paris' , 100 , $exclude , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , '/main\\.(css|js)/' , $data_to_parse , $full , $timeout );
		$this->assertEquals( 14 , $logs['count'] );
		$this->assertFalse( strpos( $logs['logs'][0]['Date'] , 'ERROR ! Unable to convert this string to date') );
    }

	public function test_IIS_W3C_Extended()
    {
		$regex         = '|^(.*) (.*) (.*) (.*) (.*) (.*) (.*) (.*) (.*) (.*) (.*) (.*) (.*) (.*) (.*) (.*) (.*) (.*) (.*) (.*) (.*) (.*)$|U';
		$match         = array("Date"=>array(1," ",2," "),"IP"=>11,"Site"=>3,"CMD"=>6,"URL"=>7,"QS"=>8,"Code"=>17,"Size"=>20,"Referer"=>15,"UA"=>13,"User"=>10,"ms"=>22);
		$types         = array("Date"=>"date:H:i:s","Site"=>"txt","CMD"=>"txt","URL"=>"txt","QS"=>"txt","User"=>"txt","IP"=>"ip:geo","UA"=>"uaw3c:{os.name} {os.version} | {browser.name} {browser.version}\/100","Referer"=>"link","Code"=>"badge:http","Size"=>"numeral:0b","ms"=>"numeral:0,0");
		$multiline     = '';
		$exclude       = '';
		$file_path     = PHPMOCKUP . '/iis_w3cextended.log';
		$start_offset  = 0;
		$start_from    = SEEK_END;
		$load_more     = false;
		$old_lastline  = '';
		$data_to_parse = filesize( $file_path );
		$full          = true;
		$timeout       = 2;

		$logs = LogParser::getNewLines( $regex , $match , $types , 'Europe/Paris' , 10 , $exclude , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , '' , $data_to_parse , $full , $timeout );
		$this->assertEquals( 10 , $logs['count'] );
		$this->assertFalse( strpos( $logs['logs'][0]['Date'] , 'ERROR ! Unable to convert this string to date') );

		$logs = LogParser::getNewLines( $regex , $match , $types , 'Europe/Paris' , 100 , $exclude , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , '' , $data_to_parse , $full , $timeout );
		$this->assertEquals( 91 , $logs['count'] );
		$this->assertFalse( strpos( $logs['logs'][0]['Date'] , 'ERROR ! Unable to convert this string to date') );

		$logs = LogParser::getNewLines( $regex , $match , $types , 'Europe/Paris' , 100 , $exclude , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , 'main.css' , $data_to_parse , $full , $timeout );
		$this->assertEquals( 7 , $logs['count'] );
		$this->assertFalse( strpos( $logs['logs'][0]['Date'] , 'ERROR ! Unable to convert this string to date') );

		$logs = LogParser::getNewLines( $regex , $match , $types , 'Europe/Paris' , 100 , array( "URL" => array(  "/main.css/" ) ) , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , 'main.css' , $data_to_parse , $full , $timeout );
		$this->assertEquals( 0 , $logs['count'] );

		$logs = LogParser::getNewLines( $regex , $match , $types , 'Europe/Paris' , 100 , $exclude , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , '/main\\.(css|js)/' , $data_to_parse , $full , $timeout );
		$this->assertEquals( 10 , $logs['count'] );
		$this->assertFalse( strpos( $logs['logs'][0]['Date'] , 'ERROR ! Unable to convert this string to date') );
    }

	public function test_nginx_error()
    {
		$regex         = '@^(.*)/(.*)/(.*) (.*):(.*):(.*) \\[(.*)\\] [0-9#]*: \\*[0-9]+ (((.*), client: (.*), server: (.*), request: \"(.*) (.*) HTTP.*\", host: \"(.*)\"(, referrer: \"(.*)\")*)|(.*))$@U';
		$match         = array("Date" => array(1,"/",2,"/",3," ",4,":",5,":",6), "Severity" => 7, "Error"    => array(10,18), "Client"   => 11, "Server"   => 12, "Method"   => 13, "Request"  => 14, "Host"     => 15, "Referer"  => 17 );
		$types         = array("Date" => "date:H:i:s","Site"=>"txt","CMD"=>"txt","URL"=>"txt","QS"=>"txt","User"=>"txt","IP"=>"ip:geo","UA"=>"uaw3c:{os.name} {os.version} | {browser.name} {browser.version}\/100","Referer"=>"link","Code"=>"badge:http","Size"=>"numeral:0b","ms"=>"numeral:0,0");
		$multiline     = '';
		$exclude       = '';
		$file_path     = PHPMOCKUP . '/nginx_error.log';
		$start_offset  = 0;
		$start_from    = SEEK_END;
		$load_more     = false;
		$old_lastline  = '';
		$data_to_parse = filesize( $file_path );
		$full          = true;
		$timeout       = 2;

		$logs = LogParser::getNewLines( $regex , $match , $types , 'Europe/Paris' , 10 , $exclude , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , '' , $data_to_parse , $full , $timeout );
		$this->assertEquals( 10 , $logs['count'] );
		$this->assertFalse( strpos( $logs['logs'][0]['Date'] , 'ERROR ! Unable to convert this string to date') );

		$logs = LogParser::getNewLines( $regex , $match , $types , 'Europe/Paris' , 1000 , $exclude , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , '' , $data_to_parse , $full , $timeout );
		$this->assertEquals( 133 , $logs['count'] );
		$this->assertFalse( strpos( $logs['logs'][0]['Date'] , 'ERROR ! Unable to convert this string to date') );

		$logs = LogParser::getNewLines( $regex , $match , $types , 'Europe/Paris' , 100 , $exclude , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , 'index.php' , $data_to_parse , $full , $timeout );
		$this->assertEquals( 23 , $logs['count'] );
		$this->assertFalse( strpos( $logs['logs'][0]['Date'] , 'ERROR ! Unable to convert this string to date') );

		$logs = LogParser::getNewLines( $regex , $match , $types , 'Europe/Paris' , 100 , array( "Error" => array(  "/index.php/" ) , "Request" => array(  "/index.php/" ) ) , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , 'index.php' , $data_to_parse , $full , $timeout );
		$this->assertEquals( 0 , $logs['count'] );

		$logs = LogParser::getNewLines( $regex , $match , $types , 'Europe/Paris' , 100 , $exclude , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , '/access phase: [0-9]$/' , $data_to_parse , $full , $timeout );
		$this->assertEquals( 2 , $logs['count'] );
		$this->assertFalse( strpos( $logs['logs'][0]['Date'] , 'ERROR ! Unable to convert this string to date') );
    }

	public function test_nginx_access()
    {
		$regex         = '|^((\\S*) )*(\\S*) (\\S*) (\\S*) \\[(.*)\\] \"(\\S*) (.*) (\\S*)\" ([0-9]*) (.*)( \"(.*)\" \"(.*)\"( [0-9]*/([0-9]*))*)*$|U';
		$match         = array( "Date"    => 6, "IP"      => 3, "CMD"     => 7, "URL"     => 8, "Code"    => 10, "Size"    => 11, "Referer" => 13, "UA"      => 14, "User"    => 5 );
		$types         = array( "Date"    => "date:H:i:s", "IP"      => "ip:geo", "URL"     => "txt", "Code"    => "badge:http", "Size"    => "numeral:0b", "Referer" => "link", "UA"      => "ua:{os.name} {os.version} | {browser.name} {browser.version}\/100" );
		$multiline     = '';
		$exclude       = '';
		$file_path     = PHPMOCKUP . '/nginx_access.log';
		$start_offset  = 0;
		$start_from    = SEEK_END;
		$load_more     = false;
		$old_lastline  = '';
		$data_to_parse = filesize( $file_path );
		$full          = true;
		$timeout       = 2;

		$logs = LogParser::getNewLines( $regex , $match , $types , 'Europe/Paris' , 10 , $exclude , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , '' , $data_to_parse , $full , $timeout );
		$this->assertEquals( 10 , $logs['count'] );
		$this->assertFalse( strpos( $logs['logs'][0]['Date'] , 'ERROR ! Unable to convert this string to date') );

		$logs = LogParser::getNewLines( $regex , $match , $types , 'Europe/Paris' , 1000 , $exclude , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , '' , $data_to_parse , $full , $timeout );
		$this->assertEquals( 157 , $logs['count'] );
		$this->assertFalse( strpos( $logs['logs'][0]['Date'] , 'ERROR ! Unable to convert this string to date') );

		$logs = LogParser::getNewLines( $regex , $match , $types , 'Europe/Paris' , 100 , $exclude , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , 'index.php' , $data_to_parse , $full , $timeout );
		$this->assertEquals( 48 , $logs['count'] );
		$this->assertFalse( strpos( $logs['logs'][0]['Date'] , 'ERROR ! Unable to convert this string to date') );

		$logs = LogParser::getNewLines( $regex , $match , $types , 'Europe/Paris' , 100 , array( "URL" => array(  "/index.php/" ) ) , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , 'index.php' , $data_to_parse , $full , $timeout );
		$this->assertEquals( 0 , $logs['count'] );

		$logs = LogParser::getNewLines( $regex , $match , $types , 'Europe/Paris' , 100 , $exclude , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , '/jekyll\/(.*)\//' , $data_to_parse , $full , $timeout );
		$this->assertEquals( 43 , $logs['count'] );
		$this->assertFalse( strpos( $logs['logs'][0]['Date'] , 'ERROR ! Unable to convert this string to date') );
    }

}
