<?php
### vBulletin 3 & 4 = md5(md5(password).salt) ###
function verifyVb($user, $pass, $path, $dynKey){	
	require $path."includes/config.php";

	isBlocked($user, $dynKey,$config['MasterServer']['servername'], $config['Database']['dbname'], $config['MasterServer']['username'], $config['MasterServer']['password']);
	

	/* Connect to the database */
	try {
	  # MySQL with PDO_MYSQL  
	  $DBH = new PDO("mysql:host=" . $config['MasterServer']['servername'] . ";dbname=" . $config['Database']['dbname'], $config['MasterServer']['username'], $config['MasterServer']['password']); 
	  $DBH->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );	  
	}  
	catch(PDOException $e) {  
	  prntOutput(false, "PDO Error", "I'm sorry, Dave. I'm afraid I can't do that.", $dynKey);
	  file_put_contents('PDOErrors.txt', $e->getMessage() . "\n", FILE_APPEND);
	  $DBH = null;
	  exit;
	}
	
	/* Fetching the user info */
	try {
		# STH means "Statement Handle"
		$STH = $DBH->prepare("SELECT * FROM ".$config['Database']['tableprefix']."user  WHERE username = ?");
		$STH->execute(array($user));
		$STH->setFetchMode(PDO::FETCH_ASSOC);
		while($row = $STH->fetch()){
			$UserID = $row[userid];
			$userGroup = $row[usergroupid];
			$userName = $row[username];
			$realHash = $row[password];
			$salt = $row[salt];
		}
	}
	catch(PDOException $e) {  
	  prntOutput(false, "PDO Error", "I'm sorry, Dave. But we seem to have a small error.", $dynKey);
	  file_put_contents('PDOErrors.txt', $e->getMessage() . "\n", FILE_APPEND);
	  $DBH = null;
	  exit;
	}
	
	if(strtolower($user) !== strtolower($userName)){
		prntOutput(false, $user, "Username Not Found.\nYou have " . addStrike($dynKey,$config['MasterServer']['servername'], $config['Database']['dbname'], $config['MasterServer']['username'], $config['MasterServer']['password']) . " of a maximum 10 strikes remaining.\nYou will then be banned from this system.", $dynKey);
		$DBH = null;
		exit;
	}
	
	/* Fetching the usergroup info */
	try {
		# STH means "Statement Handle"
		$STH = $DBH->prepare("SELECT * FROM ".$config['Database']['tableprefix']."usergroup  WHERE usergroupid = ?");
		$STH->execute(array($userGroup));
		$STH->setFetchMode(PDO::FETCH_ASSOC);
		while($row = $STH->fetch()) {  
			$userGroupName = $row[title];
		}
	}
	catch(PDOException $e) {  
	  prntOutput(false, "PDO Error", "I'm sorry, Garry. But we seem to have a slight error.", $dynKey);
	  file_put_contents('PDOErrors.txt', $e->getMessage() . "\n", FILE_APPEND);
      $DBH = null;
	  exit;
	}
	
	$sha1Pass = md5(md5($pass).$salt);

	if($sha1Pass == $realHash){
		removeStrike($dynKey,$config['MasterServer']['servername'], $config['Database']['dbname'], $config['MasterServer']['username'], $config['MasterServer']['password']);
		prntOutput(true, $userName, $userGroupName, $dynKey, $writeLog, $LogFileName);
	}else{
		prntOutput(false, $userName, "Bad Password.\nYou have " . addStrike($dynKey,$config['MasterServer']['servername'], $config['Database']['dbname'], $config['MasterServer']['username'], $config['MasterServer']['password']) . " of a maximum 10 strikes remaining.\nYou will then be banned from this system.", $dynKey);
	}

	$DBH = null;
	exit;
}
?>