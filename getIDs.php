<?php
	if (isset($_POST['allstu'])){
		$postData = $_POST['allstu'];
		$url = 'https://web.njit.edu/~nho2/CS490/RC/getstuid.php';
		$curlGetIds = curl_init();
		$curlOptions = array(CURLOPT_URL => $url,
							 CURLOPT_POST => 1,
							 CURLOPT_FOLLOWLOCATION => 0,
							 CURLOPT_RETURNTRANSFER => 1,
							 CURLOPT_POSTFIELDS => $postData,
							 CURLOPT_HEADER => 0);
		curl_setopt_array($curlGetIds, $curlOptions);
		$response = curl_exec($curlGetIds);
		header('Content-Type: application/json');
		echo json_encode($response);
		curl_close($curlGetIds);
	}
?>