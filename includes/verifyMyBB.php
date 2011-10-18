<?php
### MyBB 1.6 md5(md5($salt).md5($plain_pass)) ###
function verifyMyBB($user, $pass, $path, $dynKey){	
	require $path."/inc/config.php";

	isBlocked($user, $dynKey, $config['database']['hostname'], $config['database']['database'], $config['database']['username'], $config['database']['password']);
	

	/* Connect to the database */
	try {
	  # MySQL with PDO_MYSQL  
	  $DBH = new PDO("mysql:host=" . $config['database']['hostname'] . ";dbname=" . $config['database']['database'], $config['database']['username'], $config['database']['password']); 
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
		$STH = $DBH->prepare("SELECT * FROM ".$config['database']['table_prefix']."users  WHERE username = ?");
		$STH->execute(array($user));
		$STH->setFetchMode(PDO::FETCH_ASSOC);
		while($row = $STH->fetch()){
			$UserID = $row[uid];
			$userGroup = $row[usergroup];
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
		prntOutput(false, $user, "Username Not Found.\nYou have " . addStrike($dynKey, $config['database']['hostname'], $config['database']['database'], $config['database']['username'], $config['database']['password']) . " of a maximum 10 strikes remaining.\nYou will then be banned from this system.", $dynKey);
		$DBH = null;
		exit;
	}
	
	/* Fetching the usergroup info */
	try {
		# STH means "Statement Handle"
		$STH = $DBH->prepare("SELECT * FROM ".$config['database']['table_prefix']."usergroups  WHERE gid = ?");
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
	
	$sha1Pass = md5(md5($salt).md5($pass));

	if($sha1Pass == $realHash){
		removeStrike($dynKey, $config['database']['hostname'], $config['database']['database'], $config['database']['username'], $config['database']['password']);
		prntOutput(true, $userName, $userGroupName, $dynKey, $writeLog, $LogFileName);
	}else{
		prntOutput(false, $userName, "Bad Password.\nYou have " . addStrike($dynKey, $config['database']['hostname'], $config['database']['database'], $config['database']['username'], $config['database']['password']) . " of a maximum 10 strikes remaining.\nYou will then be banned from this system.", $dynKey);
	}

	$DBH = null;
	exit;
}

?>