<?php
	if (isset($_POST['uid']) && isset($_POST['review'])){
		$postData = 'review='.$_POST['review'].'&uid='.$_POST['uid'];
		$url = 'https://web.njit.edu/~nho2/CS490/RC/reviewgrade.php';
		$curlGGrades = curl_init();
		$curlOptions = array(CURLOPT_URL => $url,
				     CURLOPT_POST => 1,
				     CURLOPT_FOLLOWLOCATION => 0,
				     CURLOPT_RETURNTRANSFER => 1,
				     CURLOPT_POSTFIELDS => $postData,
				     CURLOPT_HEADER => 0);
		curl_setopt_array($curlGGrades, $curlOptions);
		$response = curl_exec($curlGGrades);	
		header('Content-Type: application/json');
		echo json_encode($response);
		curl_close($curlGGrades);
	}

?>