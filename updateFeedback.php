<?php
	/*This file is called when the professor edits auto-generated comments
	  and grading
	*/
	if (isset($_POST['Exam'])){
		$url = 'https://web.njit.edu/~nho2/CS490/RC/updateData.php';
		$curlLastUpdate = curl_init();
		$curlOptions = array(CURLOPT_URL => $url,
				     CURLOPT_POST => 1,
				     CURLOPT_FOLLOWLOCATION => 0,
				     CURLOPT_RETURNTRANSFER => 1,
				     CURLOPT_POSTFIELDS => http_build_query($_POST['Exam']),
				     CURLOPT_HEADER => 0);
		curl_setopt_array($curlLastUpdate, $curlOptions);
		$response = curl_exec($curlLastUpdate);
		curl_close($curlLastUpdate);	

		echo json_encode($response);	
	}
?>