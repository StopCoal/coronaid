<?php
require_once("config.php");
require_once("pin_users.php");

function isLoggedIn()
{
	return (!empty($_SESSION['PINCHECK_USERNAME']));	
}

function checkCredentials($username, $password, $logins) {
	if(array_key_exists($username,$logins) && $logins[$username]['password'] == $password) {
		$_SESSION['PINCHECK_USERNAME']=$username;
		$_SESSION['IS_ADMIN']=(array_key_exists("role",$logins[$username]) && $logins[$username]['role'] == "admin");
		return true;
	}
	else
		return false;
}


function sendJson($ar,$headers=array())
{
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	header('HTTP/1.0 200 OK', true, 200);
	//header('Content-Type: text/json');
	header('Content-Type: application/json');
	foreach($headers as $header)
		header($header);
	
	echo json_encode($ar);	
	exit(0);
	
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



function search($pos) {
	global $ERR_MESSAGE;
	
	if(!$ERR_MESSAGE) { echo ""; return; }
	
	if($pos < 5) 
		echo " value='" . strtoupper (substr($_REQUEST['form']['corona_id'],$pos,1)) . "' ";
	else
		echo " value='" . $_REQUEST['form']['pin'] . "' ";

}


function searchById($corona_id,$pin) {
	$result=array(
		"status" => true,
		"msg" => "OK",
		"data" => null,
	);
	
	try {
		if(empty($corona_id) && empty($pin))
			throw new Exception("Es müssen mindestens ID oder PIN angegeben werden");
		
		$pdo = new \PDO('mysql:host=localhost;dbname=' . DB_NAME , DB_USER, DB_PASSWORD);
		
		if(empty($corona_id)) {
			$sql = 'SELECT * FROM ' . TABLE . ' WHERE pin = :pin ;';
			$where=array("pin" => $pin);
		}
		elseif(empty($pin)) {
			$sql = 'SELECT * FROM ' . TABLE . ' WHERE corona_key LIKE :corona_id ;';
			$where=array("corona_id" => $corona_id);
		}
		else {
			$sql = 'SELECT * FROM ' . TABLE . ' WHERE corona_key LIKE :corona_id AND pin = :pin  ;';
			$where=array("corona_id" => $corona_id, "pin" => $pin );
		}

		$statement = $pdo->prepare($sql);
		if($statement->execute($where) === false) throw new Exception("Datenbankfehler (execute)");
		if(($result['data']=$statement->fetchAll(PDO::FETCH_ASSOC)) === false) throw new Exception("Datenbankfehler (fetch)");		
		$statement->closeCursor();
		
	} catch (\Exception $e) {
		$result['msg']=$e->getMessage();
		$result['status']=false;

	}
	
	return $result;
	
}

function checkPin($corona_id,$pin) {
	global $ERR_MESSAGE, $MESSAGE;
	$result=false;
	
	try {
		if(empty($corona_id) || empty($pin))
			throw new Exception("Es müssen ID und PIN angegeben werden");
		
		$pdo = new \PDO('mysql:host=localhost;dbname=' . DB_NAME , DB_USER, DB_PASSWORD);
		$sql = 'SELECT COUNT(corona_key) FROM ' . TABLE . ' WHERE corona_key=:corona_id  AND pin=:pin;';
		$statement = $pdo->prepare($sql);
		if($statement->execute(array("corona_id" => $corona_id, "pin" => $pin)) === false) throw new Exception("Datenbankfehler (execute)");
		if(($numRows=$statement->fetchColumn()) === false) throw new Exception("Datenbankfehler (fetch)");
		
		
		if($numRows != 1) throw new Exception("Falsche PIN zu dieser ID");
		
		$MESSAGE="Die PIN gehört zu dieser ID";
		$result=true;
		
	} catch (\Exception $e) {
		$ERR_MESSAGE=$e->getMessage();
		$result=false;

	}
	
	return $result;
	
}

/********************************* functions end **********************************/

/********************************* Initializations **********************************/
date_default_timezone_set ( "Europe/Berlin" );
$login_error=" invisible ";
$saved_success="display: none;";
$ERR_MESSAGE=false;
$MESSAGE=false;
$form=null;
$CoronaIdList=array();
/********************************* Initializations end *******************************/

switch(@$_REQUEST['action']) {
	case "login":
		if(!checkCredentials($_REQUEST['username'],$_REQUEST['password'], $logins))
			$login_error=" visible ";
		break;
		
	case "logout":
		session_unset();  
		session_destroy();
		break;
					
	case "check_pin":
		checkPin(strtoupper($_REQUEST['form']['corona_id']),$_REQUEST['form']['pin']);
		break;
	case "search_by_id":
		sendJson(searchById(@$_REQUEST['corona_id'],@$_REQUEST['pin']));
		break;
	default:
		break;
	
}



if(!isLoggedIn())
	$inc="Login.php";
else
{
	$inc="pin_form.php";
	
	$options_user="{$logins[$_SESSION['PINCHECK_USERNAME']]['display_name']}";

}

?>