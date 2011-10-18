<?php
### SMF = sha1(toLower(username).password) ###
function verifySmf($user, $pass, $path, $dynKey){	
	require $path."Settings.php";

	isBlocked($user, $dynKey,$db_server, $db_name, $db_user, $db_passwd);

	$sha1Pass = sha1(strtolower($user).$pass);

	/* Connect to the database */
	try {
	  # MySQL with PDO_MYSQL  
	  $DBH = new PDO("mysql:host=" . $db_server . ";dbname=" . $db_name, $db_user, $db_passwd); 
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
		$STH = $DBH->prepare("SELECT * FROM ".$db_prefix."members  WHERE passwd = ?");
		$STH->execute(array($sha1Pass));
		$STH->setFetchMode(PDO::FETCH_ASSOC);
		while($row = $STH->fetch()){
			#$UserID = $row[member_id];
			$userGroup = $row[id_group];
			$userName = $row[member_name];
			#$realHash = $row[members_pass_hash];
			#$salt = $row[members_pass_salt];
		}
	}
	catch(PDOException $e) {  
	  prntOutput(false, "PDO Error", "I'm sorry, Dave. But we seem to have a small error.", $dynKey);
	  file_put_contents('PDOErrors.txt', $e->getMessage() . "\n", FILE_APPEND);
	  $DBH = null;
	  exit;
	}
	
	if(strtolower($user) !== strtolower($userName)){
		prntOutput(false, $user, "Username/Password error.\nYou have " . addStrike($dynKey, $db_server, $db_name, $db_user, $db_passwd) . " of a maximum 10 strikes remaining.\nYou will then be banned from this system.", $dynKey);
		$DBH = null;
		exit;
	}
	
	/* Fetching the usergroup info */
	try {
		# STH means "Statement Handle"
		$STH = $DBH->prepare("SELECT * FROM ".$db_prefix."membergroups  WHERE id_group = ?");
		$STH->execute(array($userGroup));
		$STH->setFetchMode(PDO::FETCH_ASSOC);
		while($row = $STH->fetch()) {  
			$userGroupName = $row[group_name];
		}
	}
	catch(PDOException $e) {  
	  prntOutput(false, "PDO Error", "I'm sorry, Garry. But we seem to have a slight error.", $dynKey);
	  file_put_contents('PDOErrors.txt', $e->getMessage() . "\n", FILE_APPEND);
      $DBH = null;
	  exit;
	}
	
	removeStrike($dynKey, $db_server, $db_name, $db_user, $db_passwd);
	prntOutput(true, $userName, $userGroupName, $dynKey, $writeLog, $LogFileName);

	$DBH = null;
	exit;
}
?>