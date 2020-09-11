<?php

require_once("config.php");
require_once("bulkid_users.php");

function isLoggedIn()
{
	return (!empty($_SESSION['BULKID_USERNAME']));	
}


function checkCredentials($username, $password, $logins) {
	if(array_key_exists($username,$logins) && $logins[$username]['password'] == $password) {
		$_SESSION['BULKID_USERNAME']=$username;
		$_SESSION['IS_ADMIN']=(array_key_exists("role",$logins[$username]) && $logins[$username]['role'] == "admin");
		return true;
	}
	else
		return false;
}

function getCoronaIdList($num) {
	$result=array();
	if(empty($num)) {
		setError("Anzahl muss grösser Null sein");
		return $result;
	}
	
	$pdo = new \PDO('mysql:host=localhost;dbname=' . DB_NAME , DB_USER, DB_PASSWORD);
	try {                             
		$pdo ->beginTransaction();
		$sql = "SELECT * FROM " . CORONAID_TABLE . "  WHERE state = 0 ORDER BY random LIMIT {$num} FOR UPDATE;";
		$statement = $pdo->prepare($sql);
		$statement->execute(array( "num" => $num));
		$rows=$statement->fetchAll(PDO::FETCH_ASSOC);
		if($rows === false) throw new \Exception("Could not fetch corona_id's");
		if(empty($rows)) throw new \Exception("Keine ID's mehr zur Verfügung");
		$ids=array_column($rows,"id");
		$sql = 'UPDATE eg_data.corona_id SET state = 1  WHERE id IN ( ' . implode(",",$ids) . ' );';
		$statement = $pdo->prepare($sql);
		if(!$statement->execute())
			throw new \Exception("Error updating status of corona_id");
		$pdo->commit();
		$result=$rows;
	} catch (Exception $e) {
		$pdo ->rollBack();
		setError("Datenbankfehler: " . $e->getMessage());
		
	}

	return $result;
}

function setError($txt) {
	global $ERR_MESSAGE;
	
	if($ERR_MESSAGE !== false)
		$ERR_MESSAGE .= "<br>{$txt}";
	else
		$ERR_MESSAGE=$txt;
}

function setMessage($txt) {
	global $MESSAGE;
	
	if($MESSAGE !== false)
		$MESSAGE .= "<br>{$txt}";
	else
		$MESSAGE=$txt;
}

/********************************* functions end **********************************/

/********************************* Initializations **********************************/
date_default_timezone_set ( "Europe/Berlin" );
$login_error=" invisible ";
$saved_success="display: none;";
$MESSAGE=false;
$ERR_MESSAGE=false;
$form=null;
$CoronaIdList=array();
/********************************* Initializations end *******************************/

switch(	@$_REQUEST['action']) {
	case "login":
		if(!checkCredentials($_REQUEST['username'],$_REQUEST['password'], $logins))
			$login_error=" visible ";
		break;
		
	case "logout":
		session_unset();  
		session_destroy();
		break;
					
	case "get_coronaid_list":
		$CoronaIdList=getCoronaIdList($_REQUEST['num_corona_ids']);	
		break;
	default:
		break;
	
}



if(!isLoggedIn())
	$inc="Login.php";
else
{
	$inc="bulkid_form.php";
	
	$options_user="{$logins[$_SESSION['BULKID_USERNAME']]['display_name']}";

}

?>