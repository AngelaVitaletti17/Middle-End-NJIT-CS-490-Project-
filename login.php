<?php
	//Check to see if there is a post request
	if (isset($_POST['username']) && isset($_POST['password'])){
		$uname = $_POST['username'];
		$pword = $_POST['password'];
		$postData = 'username='.$uname.'&password='.$pword;
		$url = 'https://web.njit.edu/~nho2/CS490/RC/backLogin.php';
		$curlToDatabase = curl_init();
		$curlOptions = array(CURLOPT_URL => $url,
							 CURLOPT_POST => 1,
							 CURLOPT_FOLLOWLOCATION => 0,
							 CURLOPT_RETURNTRANSFER => 1,
							 CURLOPT_POSTFIELDS => $postData,
							 CURLOPT_HEADER => 0);
		curl_setopt_array($curlToDatabase, $curlOptions);
		$response = curl_exec($curlToDatabase);
		header('Content-Type: application/json');
		$responseMessage->login = $response;
		echo json_encode($responseMessage);
		curl_close($curlToDatabase);
	}
?>