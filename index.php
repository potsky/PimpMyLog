<?php
/**
 * PHPApacheLogViewer
 *
 * Copyright © 2009-2012 Raphael Barbate (potsky) <potsky@me.com> [http://www.potsky.com]
 *
 * This file is part of PHPApacheLogViewer.
 *
 * PHPApacheLogViewer is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License.
 *
 * PHPApacheLogViewer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with PHPApacheLogViewer.  If not, see <http://www.gnu.org/licenses/>.
 */

if (!file_exists('config.inc.php')) {
	die('Not configured !');
}

$files	 = array(
	'Access'	=> 'access.log',
	'Error'		=> 'error.log',
);

include_once('config.inc.php');

$logtype = @$_GET['l'];
$logtype = ($logtype=='') ? 'Error' : $logtype;

$howmany = (int)@$_GET['n'];
$howmany = ($howmany>0) ? $howmany : 10;
$howmany = min($howmany,$howmax);

$Gi      = @$_GET['Gi'];	// ip
$Gc      = @$_GET['Gc'];	// code
$Gu      = @$_GET['Gu'];	// user
$Gm      = @$_GET['Gm'];	// cmd
$Gs      = @$_GET['Gs'];  // url
$Ga      = @$_GET['Ga'];	// ua

$ipselector   = array();
$codeselector = array();
$userselector = array();
$cmdselector  = array();
$urlselector  = array();
$uaselector   = array();


?>
<html>
<head>
<title>Apache logs</title>
<style>
body {
	font-family: Lucida Grande;
	font-size: 10px;
	background-color: #333;
	color: #aaa;
}
table {
	border-collapse: collapse;
	border: 1px solid #666;
	margin-top: 5px;
}
tr {
	background-color: #333;
}
tr:hover {
	background-color: #222;
}
th {
	padding-left: 5px;
	padding-right: 5px;
	padding-top: 1px;
	padding-bottom: 1px solid #333;
	color: #333;
	background-color: #f08;
	font-family: Lucida Grande;
	font-size: 10px;
	border-left: 1px solid #666;
	border-bottom: 1px solid #666;
}
td {
	vertical-align: top;
	padding-left: 5px;
	padding-right: 5px;
	padding-top: 1px;
	padding-bottom: 1px;
	font-family: Lucida Grande;
	font-size: 10px;
	border-left: 1px solid #666;
}
select, input {
	background-color: #333;
	border: 0;
	color: #aaa;
	padding: 0;
	text-align: right;
	font-size: 10px;
}
a {
	text-decoration: none;
	color: #f08;
}
a:hover {
	color: #fff;
}
.ua {
	color: #aaa;
}
.c1 {
	color: #f60;
}
.c2 {
	color: #0f0;
}
.c3 {
	color: #08f;
}
.c4 {
	color: #f00;
}
.c5 {
	color: #f00;
}
</style>
</head>
<body><center><form method="GET" name="form">

<?php
echo '<input type="hidden" name="l" value="'.$logtype.'"/>';
foreach ($files as $name=>$logfile) {
	if ($name==$logtype) {
		echo '['.$name.']';
	}
	else {
		echo '<a href="?n='.$howmany.'&l='.$name.'">['.$name.']</a>';
	}
	echo '&nbsp;';
}
echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-<input size="3" maxlength="3" type="text" name="n" value="'.$howmany.'"/> lines displayed';
echo '&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:document.location.href=\'?n='.$howmany.'&l='.$logtype.'\';">[Reset filters]</a>';

function selector($array,$currentvalue) {
	$return = '<option value=""></option>';
	ksort($array);
	foreach ($array as $value) {
		if ($value==$currentvalue) {
			$s = ' selected="selected"';
		} else {
			$s = '';
		}
		$value = htmlentities($value);
		$value2 = $value;
		if (strlen($value2)>50) $value2=substr($value2,0,80).'...';
		$return .= '<option value="'.$value.'"'.$s.'>'.$value2.'</option>';
	}
	return $return;
}
function strposa($haystack, $needles=array(), $offset=0) {
        $chr = array();
        foreach($needles as $needle) {
                $res = strpos($haystack, $needle, $offset);
                if ($res !== false) $chr[$needle] = $res;
        }
        if(empty($chr)) return false;
        return min($chr);
}

function preg_matcha($needles,$haystack) {
    foreach($needles as $needle) {
      if (preg_match($needle, $haystack)) {
    		return true;
    	}
    }
    return false;
}


//===================================================================================================================================================
// APACHE ACCESS
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
if ($logtype=='Access') {
	$result	 = '';
	//	$dumb		= exec('cat '.$files[$logtype].'|grep -v "::1"|grep -v "/wwwlogs/"|grep -v "/favicon.ico"|grep -v "/robots.txt"',$output);
	//	$lengtd		= count($output)-1;
	$lastip  = '';
	$lastday = '';
	$lndisp	 = 0;

	$fl = fopen($files[$logtype], "r");
	for($x_pos = 0, $ln = 0, $line=''; fseek($fl, $x_pos, SEEK_END) !== -1; $x_pos--) {
	    $char = fgetc($fl);
	    if ($char === "\n") {

			if (preg_matcha($exclude,$line)) {
	        	$deal = '';
			}
	        else {
		        $deal = $line;
	        }
	        $line = '';

	    	if ($deal!='') {

				$parser = parser_access($deal);
				if (!is_array($parser)) continue;
				list($day,$mon,$yea,$ddate,$ip,$login,$command,$url,$protocol,$code,$bytes,$ua) = $parser;

				// Filtering
				//
				$ipselector[$ip]   		= $ip;
				$codeselector[$code] 	= $code;
				$userselector[$login] 	= $login;
				$cmdselector[$command]  = $command;
				$urlselector[$url]  	= $url;
				$uaselector[$ua]   		= $ua;

		        $ln++;
				if ($ln>$howmax) break;
//				if ($lndisp>=$howmany) continue;
				if ($lndisp>=$howmany) break;

				if (($Gi!='')&&($Gi!=$ip)) continue;
				if (($Gc!='')&&($Gc!=$code)) continue;
				if (($Gu!='')&&($Gu!=$login)) continue;
				if (($Gm!='')&&($Gm!=$command)) continue;
				if (($Gs!='')&&($Gs!=$url)) continue;
				if (($Ga!='')&&($Ga!=$ua)) continue;


				// Display
				//
				$codetype	= substr($code,0,1);
				$hcode		= '<a href="http://fr.wikipedia.org/wiki/Liste_des_codes_HTTP" target="httpcode"><span class="c'.$codetype.'">'.$code.'</span></a>';

				if ($lastday!=$day) {
					$result.= '<tr><td style="border-left:1px solid #666; border-right:1px solid #666; border-top:1px solid #333;background-color:#666;color:#fff;" colspan="7">'.$day.'/'.$mon.'/'.$yea.'</td></tr>';
				}
				$lastday	= $day;

				if ($lastip!=$ip) {
					$result.= '<tr><td style="padding-top:3px;"></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>';
				}
				$lastip		= $ip;
				$hip		= '<a href="http://www.geoiptool.com/en/?IP='.$ip.'" target="geoip">'.$ip.'</a>';
				$hurl		= '<a href="'.$url.'" target="url">'.$url.'</a>';
				$hua		= '<a href="http://user-agent-string.info/?Fuas='.urlencode($ua).'&test=spamno&action=analyze" target="ua" class="ua">'.$ua.'</a>';

				$result	.= '<tr>
					<td>'.$ddate.'</td>
					<td>'.$hip.'</td>
					<td>'.$hcode.'</td>
					<td>'.$login.'</td>
					<td>'.$command.'</td>
					<td>'.$hurl.'</td>
					<td>'.$hua.'</td>
				</tr>';
				$lndisp++;
			}
			continue;
        }
        $line = $char.$line;
    }
	fclose($fl);

	echo '<table>';
	echo '<tr>
			<th>Date</td>
			<th>IP</td>
			<th>Code</td>
			<th>User</td>
			<th>CMD</td>
			<th>URL</td>
			<th>UA</td>
	</tr>';

	echo '<tr>
			<td></td>
			<td><select name="Gi" onChange="javascrip:this.form.submit();">'.selector($ipselector,$Gi).'</select></td>
			<td><select name="Gc" onChange="javascrip:this.form.submit();">'.selector($codeselector,$Gc).'</select></td>
			<td><select name="Gu" onChange="javascrip:this.form.submit();">'.selector($userselector,$Gu).'</select></td>
			<td><select name="Gm" onChange="javascrip:this.form.submit();">'.selector($cmdselector,$Gm).'</select></td>
			<td><select name="Gs" onChange="javascrip:this.form.submit();">'.selector($urlselector,$Gs).'</select></td>
			<td><select name="Ga" onChange="javascrip:this.form.submit();">'.selector($uaselector,$Ga).'</select></td>
	</tr>';
	echo $result;
    echo '</table><br/>'.$lndisp.' logs displayed';
    echo ($display_file_path==true) ? ' from file <a href="'.$files[$logtype].'" target="'.$files[$logtype].'">'.$files[$logtype].'</a>' : '';
}

//===================================================================================================================================================
// APACHE ERROR
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
else if ($logtype=='Error') {

	$result	 = '';
	//	$dumb		= exec('cat '.$files[$logtype].'|grep -v "::1"|grep -v "/wwwlogs/"|grep -v "/favicon.ico"|grep -v "/robots.txt"',$output);
	//	$lengtd		= count($output)-1;
	$lastip  = '';
	$lastday = '';
	$lndisp	 = 0;

	$fl = fopen($files[$logtype], "r");
	for($x_pos = 0, $ln = 0, $line=''; fseek($fl, $x_pos, SEEK_END) !== -1; $x_pos--) {
	    $char = fgetc($fl);
	    if ($char === "\n") {

			if (preg_matcha($exclude,$line)) {
	        	$deal = '';
	        }
	        else {
		        $deal = $line;
	        }
	        $line = '';

	    	if ($deal!='') {

				$parser = parser_error($deal);
				if (!is_array($parser)) continue;
				list($day,$mon,$yea,$ddate,$ip,$error) = $parser;

//				// Filtering
//				//
				$ipselector[$ip]   		= $ip;
		        $ln++;
				if ($ln>$howmax) break;
//				if ($lndisp>=$howmany) continue;
				if ($lndisp>=$howmany) break;

				if (($Gi!='')&&($Gi!=$ip)) continue;

				// Display
				//
				if ($lastday!=$day) {
					$result.= '<tr><td style="border-left:1px solid #666; border-right:1px solid #666; border-top:1px solid #333;background-color:#666;color:#fff;" colspan="3">'.$day.'/'.$mon.'/'.$yea.'</td></tr>';
				}
				$lastday	= $day;

				if ($lastip!=$ip) {
					$result.= '<tr><td style="padding-top:3px;" colspan="3"></td></tr>';
				}
				$lastip		= $ip;

				// c1 orange
				// c4 touge
				// c3 bleu
				$error 		= htmlentities($error);
				$error		= str_replace('\n', '<br/>', $error);
				$error		= str_replace('Password Mismatch', '<span class="c4">Password Mismatch</span>', $error);
				$error		= str_replace('not found or unable to stat', '<span class="c4">not found or unable to stat</span>', $error);
				$error		= str_replace('File does not exist:', '<span class="c4">File does not exist:</span>', $error);
				$error		= str_replace('PHP Fatal error:', '<span class="c4">PHP Fatal error:</span>', $error);
				$error		= str_replace('PHP Parse error:', '<span class="c4">PHP Parse error:</span>', $error);
				$error		= str_replace('PHP Notice:', '<span class="c3">PHP Notice:</span>', $error);
				$error		= str_replace('PHP Warning:', '<span class="c1">PHP Warning:</span>', $error);
				$error		= str_replace('ErrorException', '<span class="c4">ErrorException</span>', $error);

				if (strpos($error,', referer: ')!==false) {
					$error 		= substr($error,0,strpos($error,', referer: '));
				}
				$error      = preg_replace('/^(.*) in (.*) on line (.*)$/', '${1} in <span class="c3">${2}</span> on line <span class="c3">${3}</span>', $error);

				$hip		= '<a href="http://www.geoiptool.com/en/?IP='.$ip.'" target="geoip">'.$ip.'</a>';

				$result	.= '<tr>
					<td>'.$ddate.'</td>
					<td>'.$hip.'</td>
					<td>'.$error.'</td>
				</tr>';
				$lndisp++;
			}
			continue;
        }
        $line = $char.$line;
    }
	fclose($fl);

	echo '<table>';
	echo '<tr>
			<th>Date</td>
			<th>IP</td>
			<th>Log</td>
	</tr>';

	echo '<tr>
			<td></td>
			<td><select name="Gi" onChange="javascrip:this.form.submit();">'.selector($ipselector,$Gi).'</select></td>
			<td></td>
	</tr>';
	echo $result;
	echo '</table><br/>'.$lndisp.' logs displayed';
    echo ($display_file_path==true) ? ' from file <a href="'.$files[$logtype].'" target="'.$files[$logtype].'">'.$files[$logtype].'</a>' : '';

}
?>

</form></center></body>
</html>
