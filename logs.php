<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>ForumConnect Logs</title>
</head>
<body>
<h2 align='center'>fc_trap</h2>
<pre>
<?php

/* Simply setup this file for the forum script you are using. */

/* IPB Config */
#/*
require "../conf_global.php";
$sql_host = $INFO['sql_host'];
$sql_database = $INFO['sql_database'];
$sql_user = $INFO['sql_user'];
$sql_pass = $INFO['sql_pass'];
#*/

/* MyBB Config */
/*
require "../inc/config.php";
$sql_host = $config['database']['hostname'];
$sql_database = $config['database']['database'];
$sql_user = $config['database']['username'];
$sql_pass = $config['database']['password'];
#*/

/* phpBB Config*/
/*
require "../config.php";
$sql_host = $dbhost;
$sql_database = $dbname;
$sql_user = $dbuser;
$sql_pass = $dbpasswd;
#*/

/* SMF Config*/
/*
require "../Settings.php";
$sql_host = $db_server;
$sql_database = $db_name;
$sql_user = $db_user;
$sql_pass = $db_passwd;
#*/

/* vBulletin Config*/
/*
require "../includes/config.php";
$sql_host = $config['MasterServer']['servername'];
$sql_database = $config['Database']['dbname'];
$sql_user = $config['MasterServer']['username'];
$sql_pass = $config['MasterServer']['password'];
#*/



	/* Connect to the database */
	try {
	  # MySQL with PDO_MYSQL  
	  $DBH = new PDO("mysql:host=$sql_host;dbname=$sql_database", $sql_user, $sql_pass); 
	  $DBH->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );	  
	}  
	catch(PDOException $e) {  
	  echo "PDO Error: Cant connect to SQL.";
	  file_put_contents('PDOErrors.txt', $e->getMessage() . "\n", FILE_APPEND);
	  $DBH = null;
	  exit;
	}
	
	$ip = $_SERVER['REMOTE_ADDR'];
	
	/* Fetching the info */
	try {
		# STH means "Statement Handle"
		$STH = $DBH->prepare("SELECT * FROM fc_trap");
		$STH->execute();
		$STH->setFetchMode(PDO::FETCH_ASSOC);
		while($row = $STH->fetch()){
			$sql_ip = $row[ip];
			$strikeCount = $row[strikeCount];
			$timeOfStrike = $row[timeOfStrike];
			echo "<pre>\nsql_ip: $sql_ip\nstrikeCount: $strikeCount\ntimeOfStrike: $timeOfStrike\n</pre>";
		}
		$DBH = null;
	}
	catch(PDOException $e) {  
	  echo "PDO Error: uh oh spagettio!";
	  file_put_contents('PDOErrors.txt', $e->getMessage() . "\n", FILE_APPEND);
	  $DBH = null;
	}
?>
</pre>
<br /><hr /><br />
<h2 align='center'>PDOErrors.txt</h2>
<pre>
<?php
	echo @file_get_contents('PDOErrors.txt');
?>
</pre>
<br /><hr /><br />
<h2 align='center'>ForumConnect.log</h2>
<pre>
<?php
	echo @file_get_contents('ForumConnect.log');
?>
</pre>


<style type="text/css">

/* The tab itself */

#followTab {

  /* No bullets */
  list-style: none;

  /* Position and float the tab */  
  position: fixed;
  z-index: 1;
  right: 0;
  top: 0px;
  
  /* Give the tab width and padding */
  width: 200px;
  padding: 5px 5px;
  
  /* Add the curved white border */
  border: 3px solid #fff;
  border-right: none;
  -moz-border-radius: 10px 0 0 10px;
  -webkit-border-radius: 10px 0 0 10px;
  border-radius: 10px 0 0 10px;
  
  /* Add the drop shadow */
  -moz-box-shadow: 0 0 7px rgba(0, 0, 0, .6);
  -webkit-box-shadow: 0 0 7px rgba(0, 0, 0, .6);
  box-shadow: 0 0 7px rgba(0, 0, 0, .6);
  
  /* Add the semitransparent gradient background */
  background: rgba(239, 91, 10, .75);
  background: -moz-linear-gradient(top, rgba(243, 52, 8, .75), rgba(239, 91, 10, .75));
  background: -webkit-gradient( linear, left top, left bottom, from( rgba(243, 52, 8, .75) ), to( rgba(239, 91, 10, .75) ) );
  background: linear-gradient(top, rgba(243, 52, 8, .75), rgba(239, 91, 10, .75));
  filter: progid:DXImageTransform.Microsoft.Gradient( startColorStr='#c0f33408', endColorStr='#c0ef5b0a', GradientType=0 );
}

/* Items within the tab */
#followTab li {
  margin: 0px 0px 0px -35px;
  list-style-type: none;
}

#followTab li:first-child {
  margin-top: 0;
}

img {
/*
  border-radius: 10px;
*/
  -moz-border-radius: 10px;
  -webkit-border-radius: 10px;
  border-radius: 10px;

  /* Add the drop shadow */
  -moz-box-shadow: 0 0 7px rgba(0, 0, 0, .6);
  -webkit-box-shadow: 0 0 7px rgba(0, 0, 0, .6);
  box-shadow: 0 0 7px rgba(0, 0, 0, .6);
}

</style>

<?php
$twitterUserName = "djekl";

function getTwitterAvatar($twitterUserName) {
$options = array(
CURLOPT_HEADER => TRUE, // Final URL returned in the header
CURLOPT_FOLLOWLOCATION => TRUE, // We need the ultimate destination
CURLOPT_NOBODY => TRUE, // We don't care about the content
CURLOPT_RETURNTRANSFER => TRUE,
CURLOPT_CONNECTTIMEOUT => 10, // Keep small to not delay page rendering
CURLOPT_TIMEOUT => 10, // Keep small to not delay page rendering
CURLOPT_MAXREDIRS => 5 // Shouldn't be more than 1 redirect, but just in case
);
$ch = curl_init('https://api.twitter.com/1/users/profile_image/'.$twitterUserName.'.json');
curl_setopt_array($ch, $options);
$response = curl_exec($ch);
$header = curl_getinfo($ch);
curl_close($ch);
$imgSrc = $header['url'];

if (strrpos($imgSrc, 'http') !== FALSE) {
// Return Twitter avatar
return $imgSrc;
} else {
// Return generic image
return 'https://si3.twimg.com/sticky/default_profile_images/default_profile_4_reasonably_small.png';
}
}
?>

<ul id="followTab">
	<div id="latest_tweet">
		<img src="<?php echo getTwitterAvatar("$twitterUserName"); ?>" alt="@<?php echo $twitterUserName; ?> on Twitter!" align="absmiddle" />&nbsp;&nbsp;&nbsp;<a href="https://www.twitter.com/<?php echo $twitterUserName; ?>" target="_NEW" >@<?php echo $twitterUserName; ?></a> on Twitter!<br /><br />
		<ul id="twitter_update_list">
			<li>Loading...</li>
		</ul>
		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
		<script type="text/javascript" src="https://twitter.com/javascripts/blogger.js"></script>
		<script type="text/javascript" src="https://twitter.com/statuses/user_timeline/<?php echo $twitterUserName; ?>.json?callback=twitterCallback2&amp;count=1"></script>
	</div>
</div>
</body>
</html>
