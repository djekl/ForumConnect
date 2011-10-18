<?PHP
/*
 * ForumConnect PHP Version 3.0
 * Strictly for usage with the ForumConnect.dll verseion 3.x
 * Coded by djeklDevelopments
 *
 * Special thanks to Oscar for starting this project and helping when I needed advice.
 *
 * Special thanks to v3n3 for helping me when learning C#.
 * Without your help m8 I would have been lost.
 */

// Please change this!!
$SALT = "CHANGE ME";

/**************************************
******** Don't edit below this ********
**************************************/
/* No errors */
	ini_set( 'display_errors', 0 );

require "./includes/manditory_includes.php";

if(isset($_REQUEST['notes'])){
echo base64_decode("PGh0bWw+DQoJPGhlYWQ+DQoJCTx0aXRsZT5Gb3J1bUNvbm5lY3QgMi42PC90aXRsZT4NCgk8L2hlYWQ+DQo8Ym9keT4NCjxwcmU+DQojIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIw0KIyAgICAgICAgOjo6OiAgICA6OjogIDo6Ojo6Ojo6IDo6Ojo6Ojo6Ojo6IDo6Ojo6Ojo6OjogOjo6Ojo6OjogICMNCiMgICAgICAgOis6KzogICA6KzogOis6ICAgIDorOiAgICA6KzogICAgIDorOiAgICAgICA6KzogICAgOis6ICAjDQojICAgICAgOis6KzorICArOisgKzorICAgICs6KyAgICArOisgICAgICs6KyAgICAgICArOisgICAgICAgICAgIw0KIyAgICAgKyMrICs6KyArIysgKyMrICAgICs6KyAgICArIysgICAgICsjKys6KysjICArIysrOisrIysrICAgICMNCiMgICAgKyMrICArIysjKyMgKyMrICAgICsjKyAgICArIysgICAgICsjKyAgICAgICAgICAgICAgKyMrICAgICAjDQojICAgIysjICAgIysjKyMgIysjICAgICMrIyAgICAjKyMgICAgICMrIyAgICAgICAjKyMgICAgIysjICAgICAgIw0KIyAgIyMjICAgICMjIyMgICMjIyMjIyMjICAgICAjIyMgICAgICMjIyMjIyMjIyMgIyMjIyMjIyMgICAgICAgICMNCiMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjDQovKg0KICogRm9ydW1Db25uZWN0IFBIUCBWZXJzaW9uIDMuMA0KICogU3RyaWN0bHkgZm9yIHVzYWdlIHdpdGggdGhlIEZvcnVtQ29ubmVjdC5kbGwgdmVyc2Vpb24gMy54DQogKiBDb2RlZCBieSBkamVrbERldmVsb3BtZW50cw0KICoNCiAqIFNwZWNpYWwgdGhhbmtzIHRvIE9zY2FyIGZvciBzdGFydGluZyB0aGlzIHByb2plY3QgYW5kIGhlbHBpbmcgd2hlbiBJIG5lZWRlZCBhZHZpY2UuDQogKg0KICogU3BlY2lhbCB0aGFua3MgdG8gdjNuMyBmb3IgaGVscGluZyBtZSB3aGVuIGxlYXJuaW5nIEMjLg0KICogV2l0aG91dCB5b3VyIGhlbHAgbTggSSB3b3VsZCBoYXZlIGJlZW4gbG9zdC4NCiAqLw0KPC9wcmU+DQo8L2JvZHk+DQo8L2h0bWw+");
exit;
}


$rc4a = base64_decode($SALT);

$user = $_GET['u'];
$pass = $_GET['p'];
$dynKey = $_GET['d'];

$dynKey = base64_decode(rc4(base64_decode($dynKey), $rc4a));
$user = rc4(base64_decode($user), $rc4a);
$pass = rc4(base64_decode($pass), $rc4a);

$path = "../";
$strikeCount = 0;

/* PHP Version Check. We need Version 5+ */
if(!phpversion() >= 5)
	die(rc4("Your installed version of PHP does not meet the requirements for this script!", $rc4a));

if($_GET['act'] == "test"){
	$Output = rc4("ForumConnect 3.x by djekl Developments", $rc4a);
	echo base64_encode($Output);
	exit;
}

unset($_GET);

if($user == ""){
	die(rc4("No username given.", $dynKey));
}
elseif($pass == ""){
	die(rc4("No password given.", $dynKey));
}
$indexContents = file_get_contents($path."index.php")or die(rc4("Can't open 'index.php'", $dynKey));

# SMF 1.1 #
if(doesContain($indexContents, "$forum_version = 'SMF 1.1")){
	$forum = "smf1";
	require "./includes/verifySmf.php";
	verifySmf($user, $pass, $path, $dynKey);
}

# SMF 2 #
elseif(doesContain($indexContents, "$forum_version = 'SMF 2.")){
	$forum = "smf2";
	require "./includes/verifySmf.php";
	verifySmf($user, $pass, $path, $dynKey);
}

# MyBB 1 #
elseif(doesContain($indexContents, "MyBB 1.") && doesContain($indexContents, "Website: http://mybb.com")){
	$forum = "MyBB 1.6";
	require "./includes/verifyMyBB.php";
	verifyMyBB($user, $pass, $path, $dynKey);
}

# vBulletin 3 #
elseif(doesContain($indexContents, "vBulletin 3") && doesContain($indexContents, "| http://www.vbulletin.com/license.html")){
	$forum = "vb3";
	require "./includes/verifyvB.php";
	verifyvB($user, $pass, $path, $dynKey);
}

# vBulletin 4 #
elseif(doesContain($indexContents, "vBulletin 4") && doesContain($indexContents, "| http://www.vbulletin.com/license.html")){
	$forum = "vb4";
	require "./includes/verifyvB.php";
	verifyvB($user, $pass, $path, $dynKey);
}

# IPB 2 #
elseif(doesContain($indexContents, "version	2.") && doesContain($indexContents, "@package	InvisionPowerBoard")){
	$forum = "ipb2";
	require "./includes/verifyIPB2.php";
	verifyIPB2($user, $pass, $path, $dynKey);
}

# IPB 3 #
elseif(doesContain($indexContents, "IP.Board v3.") && (doesContain($indexContents, "@package		IP.Board") or doesContain($indexContents, "@package		Invision Power Board"))){
	$forum = "ipb3";
	require "./includes/verifyIPB3.php";
	verifyIPB3($user, $pass, $path, $dynKey);
}

# phpBB 3 #
elseif(doesContain($indexContents, "@package phpBB3")){
	$forum = "phpbb3";
	require "./includes/verifyphpBB3.php";
	verifyphpBB3($user, $pass, $path, $dynKey);
}

# Unknown #
else{die(rc4("Forum type could not be determined.", $dynKey));}

################################################################
#       ::::    :::  :::::::: ::::::::::: :::::::::: ::::::::  #
#      :+:+:   :+: :+:    :+:    :+:     :+:       :+:    :+:  #
#     :+:+:+  +:+ +:+    +:+    +:+     +:+       +:+          #
#    +#+ +:+ +#+ +#+    +:+    +#+     +#++:++#  +#++:++#++    #
#   +#+  +#+#+# +#+    +#+    +#+     +#+              +#+     #
#  #+#   #+#+# #+#    #+#    #+#     #+#       #+#    #+#      #
# ###    ####  ########     ###     ########## ########        #
################################################################
/*
 * ForumConnect PHP Version 3.0
 * Strictly for usage with the ForumConnect.dll verseion 3.x
 * Coded by djeklDevelopments
 *
 * Special thanks to Oscar for starting this project and helping when I needed advice.
 *
 * Special thanks to v3n3 for helping me when learning C#.
 * Without your help m8 I would have been lost.
 */
?>