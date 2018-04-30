<?php
	if (isset($_POST)){
		$postData = 'level='.$_POST['selc2'].'&topic='.$_POST['selc'];
		$url = 'https://web.njit.edu/~nho2/CS490/RC/showquestion.php';
		$curlShowQuestion = curl_init();
		$curlOptions = array(CURLOPT_URL => $url,
				     CURLOPT_POST => 1,
				     CURLOPT_FOLLOWLOCATION => 0,
				     CURLOPT_RETURNTRANSFER => 1,
				     CURLOPT_POSTFIELDS => $postData,
				     CURLOPT_HEADER => 0);
		curl_setopt_array($curlShowQuestion, $curlOptions);
		$response = curl_exec($curlShowQuestion);	
		header('Content-Type: application/json');
		echo json_encode($response);
		curl_close($curlShowQuestion);
	}
?>