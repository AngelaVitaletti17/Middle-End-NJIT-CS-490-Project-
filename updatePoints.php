<?php
if (isset($_POST)){
	$url = 'https://web.njit.edu/~nho2/CS490/RC/exampoint.php';
	$curlPoints = curl_init();
	$curlOptions = array(CURLOPT_URL => $url,
				     CURLOPT_POST => 1,
				     CURLOPT_FOLLOWLOCATION => 0,
				     CURLOPT_RETURNTRANSFER => 1,
				     CURLOPT_POSTFIELDS => http_build_query($_POST),
				     CURLOPT_HEADER => 0);
	curl_setopt_array($curlPoints, $curlOptions);
	$response = curl_exec($curlPoints);
	curl_close($curlPoints);	

	echo json_encode($response);
}
?>