<?php

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

function initialize_cookie() {
	$name = $_POST['name'];
	
	$cookie = $_COOKIE[$name];
	
	$cookies = '';
	foreach ($http_response_header as $s) {
		if (preg_match('/^Set-Cookie:\s*([^;]+)/', $s, $parts)) {
			$cookies = $cookies . $parts[1] . ';';
		}
	}

	setcookie('cwrc-api', $name . '=' . $cookie, 0, '/');
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

	$cookies = '';
	foreach ($http_response_header as $s) {
		if (preg_match('/^Set-Cookie:\s*([^;]+)/', $s, $parts)) {
			$cookies = $cookies . $parts[1] . ';';
		}
	}

	setcookie('cwrc-api', $cookies, 0, '/');
	//$_SERVER['HTTP_COOKIE'] = $result;
}

function get_login_cookie() {
	$cookies = array();
	$eachCookie = explode(';', $_COOKIE['cwrc-api']);

	foreach ($eachCookie as $val) {
		if (strlen($val) > 0) {
			$parts = explode('=', $val);
			$cookies[$parts[0]] = $parts[1];
		}
	}

	return $cookies;
}

function cwrc_logout() {
	setcookie('cwrc-api', '', 1, '/');
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
