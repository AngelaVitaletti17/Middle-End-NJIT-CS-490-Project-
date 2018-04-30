<?php
	if (isset($_POST['view'])){
		$postData = 'update='.$_POST['view'];
	}
	else if (isset($_POST['access'])){
		$postData = 'access='.$_POST['view'];
	}
	$url = 'https://web.njit.edu/~nho2/CS490/RC/showExam.php';
	$curlUpdate = curl_init();
	$curlOptions = array(CURLOPT_URL => $url,
						 CURLOPT_POST => 1,
						 CURLOPT_FOLLOWLOCATION => 0,
						 CURLOPT_RETURNTRANSFER => 1,
						 CURLOPT_POSTFIELDS => $postData,
						 CURLOPT_HEADER => 0);
	curl_setopt_array($curlUpdate, $curlOptions);
	$response = curl_exec($curlUpdate);
	header('Content-Type: application/json');
	echo json_encode($response);
	curl_close($curlUpdate);
?>