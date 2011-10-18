<?php
function removeStrike($dynKey, $sql_host, $sql_database, $sql_user, $sql_pass){ //Adds a login failed strike
	/* Connect to the database */
	try {
	  # MySQL with PDO_MYSQL  
	  $DBH = new PDO("mysql:host=$sql_host;dbname=$sql_database", $sql_user, $sql_pass); 
	  $DBH->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );	  
	}  
	catch(PDOException $e) {  
	  prntOutput(false, "PDO Error", "Slight error here.", $dynKey);
	  file_put_contents('PDOErrors.txt', $e->getMessage() . "\n", FILE_APPEND);
	  $DBH = null;
	  exit;
	}
	/* Doing the SQL Command */
	try {
		# STH means "Statement Handle"
		$STH = $DBH->prepare("SELECT strikeCount FROM fc_trap WHERE ip = ?");
		$STH->execute(array($_SERVER['REMOTE_ADDR']));
		$STH->setFetchMode(PDO::FETCH_ASSOC);
		while($row = $STH->fetch()){
			$strikeCount = $row[strikeCount];
		}
		if($strikeCount >> 0){
			$STH = $DBH->prepare("UPDATE fc_trap SET strikeCount = ? WHERE ip = ?");
			$STH->execute(array($strikeCount-1,$_SERVER['REMOTE_ADDR']));
			$DBH = null;
		}
	}
	catch(PDOException $e) {  
	  prntOutput(false, "PDO Error", "OK So what happened there??", $dynKey);
	  file_put_contents('PDOErrors.txt', $e->getMessage() . "\n", FILE_APPEND);
	  $DBH = null;
	  exit;
	}
}

function addStrike($dynKey, $sql_host, $sql_database, $sql_user, $sql_pass){ //Adds a login failed strike
	/* Connect to the database */
	try {
	  # MySQL with PDO_MYSQL  
	  $DBH = new PDO("mysql:host=$sql_host;dbname=$sql_database", $sql_user, $sql_pass); 
	  $DBH->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );	  
	}  
	catch(PDOException $e) {  
	  prntOutput(false, "PDO Error", "Slight error here.", $dynKey);
	  file_put_contents('PDOErrors.txt', $e->getMessage() . "\n", FILE_APPEND);
	  $DBH = null;
	  exit;
	}

	/* Doing the SQL Command */
	try {
		# STH means "Statement Handle"
		$STH = $DBH->prepare("SELECT strikeCount FROM fc_trap WHERE ip = ?");
		$STH->execute(array($_SERVER['REMOTE_ADDR']));
		$STH->setFetchMode(PDO::FETCH_ASSOC);
		while($row = $STH->fetch()){
			$strikeCount = $row[strikeCount];
		}
		if($strikeCount === null){
			$STH = $DBH->prepare("INSERT INTO fc_trap (ip, strikeCount, timeOfStrike) VALUES (?, 1, CURRENT_TIMESTAMP);");
			$STH->execute(array($_SERVER['REMOTE_ADDR']));
			$DBH = null;
		}else{
			$STH = $DBH->prepare("UPDATE fc_trap SET strikeCount = ? WHERE ip = ?");
			$STH->execute(array($strikeCount+1,$_SERVER['REMOTE_ADDR']));
			$DBH = null;
		}
	}
	catch(PDOException $e) {  
	  prntOutput(false, "PDO Error", "OK So what happened there??", $dynKey);
	  file_put_contents('PDOErrors.txt', $e->getMessage() . "\n", FILE_APPEND);
	  $DBH = null;
	  exit;
	}
	return 10 - $strikeCount;
}

function isBlocked($user, $dynKey, $sql_host, $sql_database, $sql_user, $sql_pass){ //Check if the current IP is blocked
	SQL_Logs_Table_Test($dynKey, $sql_host, $sql_database, $sql_user, $sql_pass);
	
	/* Connect to the database */
	try {
	  # MySQL with PDO_MYSQL  
	  $DBH = new PDO("mysql:host=$sql_host;dbname=$sql_database", $sql_user, $sql_pass); 
	  $DBH->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );	  
	}  
	catch(PDOException $e) {  
	  prntOutput(false, "PDO Error", "Slight error here.", $dynKey);
	  file_put_contents('PDOErrors.txt', $e->getMessage() . "\n", FILE_APPEND);
	  $DBH = null;
	  exit;
	}
	
	$ip = $_SERVER['REMOTE_ADDR'];
	
	/* Fetching the info */
	try {
		# STH means "Statement Handle"
		$STH = $DBH->prepare("SELECT * FROM fc_trap WHERE ip = ?");
		$STH->execute(array($ip));
		$STH->setFetchMode(PDO::FETCH_ASSOC);
		while($row = $STH->fetch()){
			$sql_ip = $row[ip];
			$strikeCount = $row[strikeCount];
			$timeOfStrike = $row[timeOfStrike];
		}
		$DBH = null;
	}
	catch(PDOException $e) {  
	  prntOutput(false, "PDO Error", "uh oh spagettio!", $dynKey);
	  file_put_contents('PDOErrors.txt', $e->getMessage() . "\n", FILE_APPEND);
	  $DBH = null;
	  exit;
	}

	if($sql_ip === null){ //If the user has no strikes
		return false;
	}
	else{
		if($strikeCount >= 10){ //If the lockout limit is over 10, the user is blocked.
			prntOutput(false, $user, "Blocked - IP lockout", $dynKey);
			exit;
		}
	}
}

function SQL_Logs_Table_Test($dynKey, $sql_host, $sql_database, $sql_user, $sql_pass){ //Check that the 'fc_trap' table exists. If not, then create it...
	/* Connect to the database */
	try {
	  # MySQL with PDO_MYSQL  
	  $DBH = new PDO("mysql:host=$sql_host;dbname=$sql_database", $sql_user, $sql_pass); 
	  $DBH->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );	  
	}  
	catch(PDOException $e) {  
	  prntOutput(false, "PDO Error", "Little SQL error here.", $dynKey);
	  file_put_contents('PDOErrors.txt', $e->getMessage() . "\n", FILE_APPEND);
	  $DBH = null;
	  exit;
	}
	
	/* Doing the SQL Command */
	try {
		# STH means "Statement Handle"
		$STH = $DBH->prepare("CREATE TABLE IF NOT EXISTS `fc_trap` (`ip` text NOT NULL,`strikeCount` text NOT NULL,`timeOfStrike` timestamp NOT NULL default CURRENT_TIMESTAMP) ENGINE=MyISAM DEFAULT CHARSET=latin1;");
		$STH->execute();
		$DBH = null;
	}
	catch(PDOException $e) {  
	  prntOutput(false, "PDO Error", "OK So what happened there??", $dynKey);
	  file_put_contents('PDOErrors.txt', $e->getMessage() . "\n", FILE_APPEND);
	  $DBH = null;
	  exit;
	}
}

function prntOutput($isTrue, $userName = "Unknown", $userGroup = "Unknown", $dynKey = 0){
	if($isTrue){
		$token = "true";
		$token .= "\n";
		$token .= $userGroup;
		$token .= "\n";
		$token .= $userName;
		$token .= "\n";
	}else{
		$token = str_replace("\n", " ", $userGroup);
	}
	
	$token = rc4($token, $dynKey);
	echo $token;
	
	/* ForumConnect Logging */
	if($userName === "PDO Error"){
		file_put_contents("ForumConnect.log", $userName." | ".date("j/m/y - G:i:s")."\n", FILE_APPEND);
	}elseif($userGroup === "Blocked - IP lockout"){
		file_put_contents("ForumConnect.log", $userName." | ".$userGroup." | ".$_SERVER['REMOTE_ADDR']." | ".date("j/m/y - G:i:s")."\n", FILE_APPEND);
	}else{
		$Access = $isTrue?"Login Accepted":"Login Blocked - " . base64_encode(str_replace("\n", " ", $userGroup));
		file_put_contents("ForumConnect.log", $userName." | ".$Access." | ".$_SERVER['REMOTE_ADDR']." | ".date("j/m/y - G:i:s")."\n", FILE_APPEND);
	}
}

function rc4 ($data, $pwd){
    $key[] = '';
    $box[] = '';
    $cipher = '';

    $pwd_length = strlen($pwd);
    $data_length = strlen($data);

	for ($i = 0; $i < 256; $i++)
    {
        $key[$i] = ord($pwd[$i % $pwd_length]);
        $box[$i] = $i;
    }
    for ($j = $i = 0; $i < 256; $i++)
    {
        $j = ($j + $box[$i] + $key[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }
    for ($a = $j = $i = 0; $i < $data_length; $i++)
    {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $k = $box[(($box[$a] + $box[$j]) % 256)];
        $cipher .= chr(ord($data[$i]) ^ (0xff - $k));
    }
    return $cipher;
}

function doesContain($haystack, $needle){
	if(strpos($haystack, $needle) == false){
		return false;
	}
	else{
		return true;
	}
}

?>