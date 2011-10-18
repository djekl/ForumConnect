<?php
### IPB 2 md5(md5(salt).md5(password)) ###
function verifyIPB2($user, $pass, $path, $dynKey){	
	require $path."conf_global.php";

	isBlocked($user, $dynKey,$INFO['sql_host'], $INFO['sql_database'], $INFO['sql_user'], $INFO['sql_pass']);
	

	/* Connect to the database */
	try {
	  # MySQL with PDO_MYSQL  
	  $DBH = new PDO("mysql:host=" . $INFO['sql_host'] . ";dbname=" . $INFO['sql_database'], $INFO['sql_user'], $INFO['sql_pass']); 
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
		$STH = $DBH->prepare("SELECT * FROM ".$INFO['sql_tbl_prefix']."members  WHERE name = ?");
		$STH->execute(array($user));
		$STH->setFetchMode(PDO::FETCH_ASSOC);
		while($row = $STH->fetch()){
			$UserID = $row[member_id];
			$userName = $row[name];
			$userGroup = $row[mgroup];
		}
	}
	catch(PDOException $e) {  
	  prntOutput(false, "PDO Error", "I'm sorry, Dave. But we seem to have a small error.", $dynKey);
	  file_put_contents('PDOErrors.txt', $e->getMessage() . "\n", FILE_APPEND);
	  $DBH = null;
	  exit;
	}
	
	if(strtolower($user) !== strtolower($userName)){
		prntOutput(false, $user, "Username Not Found.\nYou have " . addStrike($dynKey,$INFO['sql_host'], $INFO['sql_database'], $INFO['sql_user'], $INFO['sql_pass']) . " of a maximum 10 strikes remaining.\nYou will then be banned from this system.", $dynKey);
		$DBH = null;
		exit;
	}
	
	/* Fetching the usergroup info */
	try {
		# STH means "Statement Handle"
		$STH = $DBH->prepare("SELECT * FROM ".$INFO['sql_tbl_prefix']."groups  WHERE g_id = ?");
		$STH->execute(array($userGroup));
		$STH->setFetchMode(PDO::FETCH_ASSOC);
		while($row = $STH->fetch()) {  
			$userGroupName = $row[g_title];
		}
	}
	catch(PDOException $e) {  
	  prntOutput(false, "PDO Error", "I'm sorry, Garry. But we seem to have a slight error.", $dynKey);
	  file_put_contents('PDOErrors.txt', $e->getMessage() . "\n", FILE_APPEND);
      $DBH = null;
	  exit;
	}
	
	/* Fetching the members_converge info */
	try {
		# STH means "Statement Handle"
		$STH = $DBH->prepare("SELECT * FROM ".$INFO['sql_tbl_prefix']."members_converge  WHERE converge_id = ?");
		$STH->execute(array($UserID));
		$STH->setFetchMode(PDO::FETCH_ASSOC);
		while($row = $STH->fetch()) {  
			$realHash = $row[converge_pass_hash];
			$salt = $row[converge_pass_salt];
		}
	}
	catch(PDOException $e) {  
	  prntOutput(false, "PDO Error", "I'm sorry, Dave. But we seem to have a bit of an error.", $dynKey);
	  file_put_contents('PDOErrors.txt', $e->getMessage() . "\n", FILE_APPEND);
      $DBH = null;
	  exit;
	}
	
	$sha1Pass = md5(md5($salt).md5($pass));

	if($sha1Pass == $realHash){
		removeStrike($dynKey,$INFO['sql_host'], $INFO['sql_database'], $INFO['sql_user'], $INFO['sql_pass']);
		prntOutput(true, $userName, $userGroupName, $dynKey, $writeLog, $LogFileName);
	}else{
		prntOutput(false, $userName, "Bad Password.\nYou have " . addStrike($dynKey,$INFO['sql_host'], $INFO['sql_database'], $INFO['sql_user'], $INFO['sql_pass']) . " of a maximum 10 strikes remaining.\nYou will then be banned from this system.", $dynKey);
	}

	$DBH = null;
	exit;
}
?>