<?php
	//Check to see if we're getting POST
	if (isset($_POST)){
		$postData = '';
		foreach($_POST as $key => $value){
			if ($postData == '')
				$postData .= $key.'='.urlencode($value);
			else if (!preg_match("/[a-zA-Z0-9\s\:]*/", $value)){				
				$postData .= '&'.$key.'='.urlencode($value);
			}
			else
				$postData .= '&'.$key.'='.urlencode($value);
		}
		$url = 'https://web.njit.edu/~nho2/CS490/RC/addquestion.php';
		$curlAddQuestion = curl_init();
		$curlOptions = array(CURLOPT_URL => $url,
							 CURLOPT_POST => 1,
							 CURLOPT_FOLLOWLOCATION => 0,
							 CURLOPT_RETURNTRANSFER => 1,
							 CURLOPT_POSTFIELDS => $postData,
							 CURLOPT_HEADER => 0);
		curl_setopt_array($curlAddQuestion, $curlOptions);
		$response = curl_exec($curlAddQuestion);
		header('Content-Type: application/json');
		$responseMessage->successTest = $response;
		echo json_encode($responseMessage);
		curl_close($curlAddQuestion);
	}
?>