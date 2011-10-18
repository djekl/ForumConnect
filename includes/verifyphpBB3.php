<?php
### phpBB3 = 'user_password' => phpbb_hash($data['new_password']) ###
function verifyphpBB3($user, $pass, $path, $dynKey){	
	require $path."config.php";

	isBlocked($user, $dynKey, $dbhost, $dbname, $dbuser, $dbpasswd);
	

	/* Connect to the database */
	try {
	  # MySQL with PDO_MYSQL  
	  $DBH = new PDO("mysql:host=" . $dbhost . ";dbname=" . $dbname, $dbuser, $dbpasswd); 
	  $DBH->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );	  
	}  
	catch(PDOException $e) {  
	  prntOutput(false, "PDO Error", "I'm sorry, Dave. I'm afraid I can't do that.", $dynKey);
	  file_put_contents('PDOErrors.txt', $e->getMessage() . "\n", FILE_APPEND);
	  $DBH = null;
	  exit;
	}
	
	/* Fetching the users info */
	try {
		# STH means "Statement Handle"
		$STH = $DBH->prepare("SELECT * FROM ".$table_prefix."users  WHERE username = ?");
		$STH->execute(array($user));
		$STH->setFetchMode(PDO::FETCH_ASSOC);
		while($row = $STH->fetch()){
			$UserID = $row[user_id];
			/* $userGroup = $row[group_id]; */
			$userGroup = $row[group_name];
			$userName = $row[username];
			$realHash = $row[user_password];
		}
	}
	catch(PDOException $e) {  
	  prntOutput(false, "PDO Error", "I'm sorry, Dave. But we seem to have a small error.", $dynKey);
	  file_put_contents('PDOErrors.txt', $e->getMessage() . "\n", FILE_APPEND);
	  $DBH = null;
	  exit;
	}
	
	if(strtolower($user) !== strtolower($userName)){
		prntOutput(false, $user, "Username Not Found.\nYou have " . addStrike($dynKey, $dbhost, $dbname, $dbuser, $dbpasswd) . " of a maximum 10 strikes remaining.\nYou will then be banned from this system.", $dynKey);
		$DBH = null;
		exit;
	}
	
	/* Fetching the usergroup info */
	try {
		# STH means "Statement Handle"
		$STH = $DBH->prepare("SELECT * FROM ".$table_prefix."groups  WHERE g_id = ?");
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
	
	if(phpbb_check_hash($pass, $realHash)){
		removeStrike($dynKey, $dbhost, $dbname, $dbuser, $dbpasswd);
		prntOutput(true, $userName, $userGroupName, $dynKey, $writeLog, $LogFileName);
	}else{
		prntOutput(false, $userName, "Bad Password.\nYou have " . addStrike($dynKey, $dbhost, $dbname, $dbuser, $dbpasswd) . " of a maximum 10 strikes remaining.\nYou will then be banned from this system.", $dynKey);
	}

	$DBH = null;
	exit;
}


### phpBB stuff ###
function phpbb_check_hash($password, $hash)
{
	$itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
	if (strlen($hash) == 34)
	{
		return (_hash_crypt_private($password, $hash, $itoa64) === $hash) ? true : false;
	}

	return (md5($password) === $hash) ? true : false;
}

function _hash_encode64($input, $count, &$itoa64)
{
	$output = '';
	$i = 0;

	do
	{
		$value = ord($input[$i++]);
		$output .= $itoa64[$value & 0x3f];

		if ($i < $count)
		{
			$value |= ord($input[$i]) << 8;
		}

		$output .= $itoa64[($value >> 6) & 0x3f];

		if ($i++ >= $count)
		{
			break;
		}

		if ($i < $count)
		{
			$value |= ord($input[$i]) << 16;
		}

		$output .= $itoa64[($value >> 12) & 0x3f];

		if ($i++ >= $count)
		{
			break;
		}

		$output .= $itoa64[($value >> 18) & 0x3f];
	}
	while ($i < $count);

	return $output;
}

function _hash_crypt_private($password, $setting, &$itoa64)
{
	$output = '*';

	// Check for correct hash
	if (substr($setting, 0, 3) != '$H$')
	{
		return $output;
	}

	$count_log2 = strpos($itoa64, $setting[3]);

	if ($count_log2 < 7 || $count_log2 > 30)
	{
		return $output;
	}

	$count = 1 << $count_log2;
	$salt = substr($setting, 4, 8);

	if (strlen($salt) != 8)
	{
		return $output;
	}
	if (PHP_VERSION >= 5)
	{
		$hash = md5($salt . $password, true);
		do
		{
			$hash = md5($hash . $password, true);
		}
		while (--$count);
	}
	else
	{
		$hash = pack('H*', md5($salt . $password));
		do
		{
			$hash = pack('H*', md5($hash . $password));
		}
		while (--$count);
	}

	$output = substr($setting, 0, 12);
	$output .= _hash_encode64($hash, 16, $itoa64);

	return $output;
}
### phpBB stuff end ###
?>