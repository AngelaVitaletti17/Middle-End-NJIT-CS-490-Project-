<?php
	if (isset($_POST['qids'])){
		$postData = [];
		$delim = ",\n";
		$token = strtok($_POST['qids'], $delim);
		while ($token){
			array_push($postData, $token);
			$token = strtok($delim);
		}
		$postDataQ = http_build_query($postData);
		$url = 'https://web.njit.edu/~nho2/CS490/RC/makeExam.php';
		$curlMake = curl_init();
		$curlOptions = array(CURLOPT_URL => $url,
				     CURLOPT_POST => 1,
				     CURLOPT_FOLLOWLOCATION => 0,
				     CURLOPT_RETURNTRANSFER => 1,
				     CURLOPT_POSTFIELDS => $postDataQ,
				     CURLOPT_HEADER => 0);
		curl_setopt_array($curlMake, $curlOptions);
		$response = curl_exec($curlMake);
		header('Content-Type: application/json');
		echo json_encode($response);
		curl_close($curlMake); 
	}
?>