<?php

define('CWRC_COOKIE', 'cwrc_api');
define('MULTIPART_BOUNDRY', md5(time()));

function cwrc_url() {
	return "http://cwrc-dev-01.srv.ualberta.ca";
}

function cwrc_site() {
	return "http://localhost/cwrc";
	//return "http://localhost/";
	/*if(strlen($_SERVER[QUERY_STRING]) > 10){
	 $pos = strpos($_SERVER["REQUEST_URI"], substr($_SERVER["QUERY_STRING"], 10));

	 if($pos == 0){
	 return "";
	 }

	 $asd = substr($_SERVER["REQUEST_URI"], 0, $pos);

	 return $asd;
	 }else{
	 return substr($_SERVER["REQUEST_URI"], 0, strlen($_SERVER["REQUEST_URI"]) - 1);
	 }*/
}

function is_initialized() {
	$object = array();

	$object["result"] = isset($_SESSION[CWRC_COOKIE]);

	echo json_encode($object);
}

function initialize_cookie() {
	$data = explode(';', $_POST['data']);
	
	$cookies = '';
	if(empty($data[0])){
		//error_log("empty");
		foreach(getallheaders() as $name => $value){
			if($name == "Cookie"){
				$cookies = $cookies . $name . "=" . $value;
			}
		}
	}else{
		//error_log("not empty " . count($data));
		foreach($data as $d){
			$cookies = $cookies . trim($d) . ';';
			//error_log(trim($d));
		}	
	}
	
	$_SESSION[CWRC_COOKIE] = $cookies;
}

function initialize_user() {
	// Should this be encrypted
	cwrc_login($_POST['username'], $_POST['password']);
}

function cwrc_login($username, $password) {
	$url = cwrc_url() . "/rest/user/login";
	$data = array('username' => $username, 'password' => $password);
	$options = array('http' => array('header' => "Content-type: application/json\r\n", 'method' => 'POST', 'content' => json_encode($data), ), );
	$context = stream_context_create($options);
	$result = file_get_contents($url, false, $context);

	if (strpos($http_response_header[0], "200")) {
		$cookies = '';
		foreach ($http_response_header as $s) {
			if (preg_match('/^Set-Cookie:\s*([^;]+)/', $s, $parts)) {
				$cookies = $cookies . $parts[1] . ';';

				//error_log($s); //TODO: Get the eparation date
			}
		}

		//setcookie(CWRC_COOKIE, $cookies, 0, '/');
		$_SESSION[CWRC_COOKIE] = $cookies;
	} else {
		error_log($http_response_header[0]);
		throw new Exception('An error occured while trying to login.');
	}
}

function get_login_cookie() {
	$cookies = array();

	if (isset($_SESSION[CWRC_COOKIE])) {
		$eachCookie = explode(';', $_SESSION[CWRC_COOKIE]);

		foreach ($eachCookie as $val) {
			if (strlen($val) > 0) {
				$parts = explode('=', $val);

				if (strpos($parts[0], "DRUPALCHAT") === 0) {
					continue;
				}

				$cookies[$parts[0]] = $parts[1];
			}
		}
	}

	return $cookies;
}

function cwrc_logout() {
	unset($_SESSION[CWRC_COOKIE]);
	//setcookie('cwrc-api', '', 1, '/');
}

function cwrc_createFormContent($inputXml, $data) {

	$content = "--" . MULTIPART_BOUNDRY . "\r\n";

	foreach ($data as $key => $val) {
		$content .= "Content-Disposition: form-data; name=\"" . $key . "\"\r\n\r\n";
		$content .= $val . "\r\n";
		$content .= "--" . MULTIPART_BOUNDRY . "\r\n";
	}

	$content .= "Content-Disposition: form-data; name=\"entity\"; filename=\"entity_data\"\r\n";
	$content .= "Content-type: text/xml\r\n\r\n";
	$content .= $inputXml . "\r\n";

	$content .= "--" . MULTIPART_BOUNDRY . "--";

	return $content;
}
