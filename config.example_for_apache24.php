<?php

$exclude           = array('/favicon.ico/','/PHP Stack trace:/','/PHP *[0-9]*\. /');
$howmax            = 100;
$display_file_path = false;
$files             = array(
	'Access'	=> '/var/log/httpd-access.log',
	'Error'		=> '/var/log/httpd-error.log',
);


function parser_access($line) {
	@list($ip,$dumb1,$login,$date,$tz,$command,$url,$protocol,$code,$bytes,$dumb2,$ua) = @explode(' ',$line,12);

	if ($url=='/') return;

	@list($fdate,$hr,$mn,$sc) = @explode(':',$date);
	@list($day,$mon,$yea)     = @explode('/',substr($fdate,1));
	$ddate 		= $hr.':'.$mn.':'.$sc;

	if ($ua=='-') {
		$ua = '';
	}
	if ($login=='-') {
		$login = '';
	}
	$command	= str_replace('"','',$command);

	return array(
		$day,
		$mon,
		$yea,
		$ddate,
		$ip,
		$login,
		$command,
		$url,
		$protocol,
		$code,
		$bytes,
		$ua,
	);
}

function parser_error($line) {
	@list($dumb1,$mon,$day,$ddate,$yea,$log,$dumb3,$dumb4,$dumb2,$ip,$error) = @explode(' ',$line,11);
// [Fri Oct 11 04:41:06.897613 2013] [:error] [pid 61939] [client 71.46.115.190:44171] script '/usr/local/www/apache24/data/test.php' not found or unable to stat

	if ($dumb2!='[client') return;

	//$ip		= substr($ip,0,-1);
	$ipport = explode(':',$ip);
	$ip = $ipport[0];
	$port = $ipport[1];
	$yea	= substr($yea,0,-1);

	return array(
		$day,
		$mon,
		$yea,
		$ddate,
		$ip,
		$error,
	);
}
