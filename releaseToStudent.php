<?php
	if (isset($_POST['uid']) && isset($_POST['result'])){
		$postData = 'result='.$_POST['result'].'&uid='.$_POST['uid'];
		$url = 'https://web.njit.edu/~nho2/CS490/RC/studentresult.php';
		$curlGetResults = curl_init();
		$curlOptions = array(CURLOPT_URL => $url,
				     CURLOPT_POST => 1,
				     CURLOPT_FOLLOWLOCATION => 0,
				     CURLOPT_RETURNTRANSFER => 1,
				     CURLOPT_POSTFIELDS => $postData,
				     CURLOPT_HEADER => 0);
		curl_setopt_array($curlGetResults, $curlOptions);
		$response = curl_exec($curlGetResults);	
		header('Content-Type: application/json');
		echo json_encode($response);
		curl_close($curlGetResults);
	}

?>