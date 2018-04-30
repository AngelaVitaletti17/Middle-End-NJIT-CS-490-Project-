#!/usr/local/bin/php
<?php
	//include 'gradeExam.php';
	if(isset($_POST['Exam']) && count($_POST['Exam']) > 0){

		//SEND INFORMATION TO DATABASE
		
		$postData = '';
		$data = $_POST['Exam'];
		$student = '';

		for ($i = 0; $i < count($data); $i++){
			foreach ($data[$i] as $key => $value){
				if ($key == 'uid'){
					$student = $value;
					break;
				}
			}
			if ($student != '') break;
		}

		$postDataArray = [];
		parse_str(http_build_query($_POST), $postDataArray);

		$url = 'https://web.njit.edu/~nho2/CS490/RC/ExamAnswer.php';
		$curlSubmit = curl_init();
		$curlOptions = array(CURLOPT_URL => $url,
				     CURLOPT_POST => 1,
				     CURLOPT_FOLLOWLOCATION => 0,
				     CURLOPT_RETURNTRANSFER => 1,
				     CURLOPT_POSTFIELDS => http_build_query($postDataArray),
				     CURLOPT_HEADER => 0);
		curl_setopt_array($curlSubmit, $curlOptions);
		$response = curl_exec($curlSubmit);
		header('Content-Type: application/json');
		echo json_encode($response);	
		curl_close($curlSubmit);

		//GET INFORMATION FOR GRADING

		$url = 'https://web.njit.edu/~nho2/CS490/RC/grading.php';
		$postData = 'uid='.$student;
		$curlGet = curl_init();
		curl_setopt_array($curlGet, $curlOptions);
		curl_setopt($curlGet, CURLOPT_URL, $url);
		curl_setopt($curlGet, CURLOPT_POSTFIELDS, $postData);
		$response = curl_exec($curlGet);	
		$dataArray = json_decode($response);	
		//array_push($dataArray, $data);
		//echo json_encode(shell_exec("php gradeExam.php ".json_encode($response)));
		
		curl_close($curlGet);

		//Grade That Exam

		//print_r(json_decode($response));
	// Indicies for inner arrays of the multi-dimensional array are as follows:
	// 0 - Student ID
	// 1 - Question ID
	// 2 - Question Topic
	// 3 - Student's answer to question
	// 4 - The original question
	// 5 - Number of points the question is worth
	// 6 - Test cases and answers
	// 7 - The professor's answer to the question
		
	$finalData = array(); //Used to send the final data to the database
	
	//For writing data to file
	$filename = "testProgram.py";	
	$postData = '';
	for ($i = 0; $i < count($dataArray); $i++){
		foreach($dataArray[$i] as $key => $value){
			if($key == 0) $studentId = $value;
			else if ($key == 3) $studentAns = json_decode($value);
			else if ($key == 1) $questionId = $value;
			else if ($key == 5) $questionPoints = $value;
			else if ($key == 7) $profAns = json_decode($value);
			else if ($key == 6) $testCases = json_decode($value);
			else if ($key == 2) $topic = $value;
			else if ($key == 4) $oQ = $value;
		}
		//Begin the grading process
		//Check to see if the function was name correctly if the question asks for a function
		$p = $questionPoints;
		
		//Empty comment
		$comment = ""; 
		
		//Check to see if student filled in an answer
		if (strcmp($studentAns, "") == 0){
			$p = 0;
			$comment = "You did not answer the question";
			$questionEval = array(
				'ucid' => $studentId,
				'quesId' => $questionId,
				'qScore' => $p,
				'iComment' => ${comment}
			);
			array_push($finalData, $questionEval);
			unset($comment);

			continue;
		}

		/*
		  Being of type 'function' does not imply that it will ask to write a function
		  All students are required to write a function for each question, but sometimes
     		  the question will ask for a specific function. If they don't write their own function,
		  we will have to make it behave another way to check the test cases
		*/
		
		//Used for checking function name and will also be used to add function declaration if student didn't
		preg_match('/def (.*?)\(/', $profAns, $pFunctionName);
		preg_match('/def (.*?)\(/', $studentAns, $sFunctionName);
		
		//Check if the problem requires writing a function
		if (preg_match('/write a function/', $oQ) || preg_match('/Write a function/', $oQ)){ //This question requires a function
			if (preg_match('/def (.*?)\(/', $studentAns) == 0 || preg_match('/def (.*?)\(/', $studentAns) == FALSE){ //The student did not even write a function
				$p = $questionPoints - 4; //Five Points off for not declaring a function in a function question!
				$comment = "There was no function declared! Minus 4.";
				//We have to insert one into the file to work. This will be done later, for all questions without functions (for testing)
			
			}
			else if (preg_match('/def (.*?)\(/', $studentAns) == 1){
				$r = strcmp($pFunctionName[1],$sFunctionName[1]);
				if ($r != 0){ //Check if there is some naming error
					$p = $questionPoints - 1; //One Point off for incorrect naming	
					$comment = "The function was named incorrectly, minus 1";
				}
			}
		}

		
		//TESTING
		//$comment .= $sFunctionName[1]." ".$pFunctionName[1];
		
		
		/*Check to see if the question is of type 'loops'
		  Trying to make sure they don't just use the word 'while' or 'for' by checking for a ':'
		  The system can still be tricked, but it's less likely now */
		if ($topic == 'Loops'){
			//While loop question
			preg_match('/while(.*?):/', $profAns, $loop);
			preg_match('/while(.*?):/', $studentAns, $loopS);
			if (count($loop) > 0){ //Did the professor answer contain a while loop?
				if (count($loopS) > 0){ //If it did, the student should have a while loop
					$p -= 4;
					if (strcmp($comment, "") == 0)
						$comment = "No while loop used, minus 4";
					else
						$comment .= "<br> No while loop used, minus 4";
				}
			}
			//For loop question
			preg_match('/for(.*?):/', $profAns, $loop2);
			preg_match('/for(.*?):/', $studentAns, $loopS2);
			if (count($loop2) > 0){ //Did the professor answer contain a for loop?
				if (count($loopS2) > 0){ //If it did, the student should have a for loop
					$p -= 4;
					if (strcmp($comment, "") == 0)
						$comment = "No for loop used, minus 4";
					else
						$comment .= "<br> No for loop used, minus 4";
				}
			}
		}
		
		//Check if it's an If Else question
		if ($topic == 'If Else'){
			preg_match('/if(.*?):/', $profAns, $if);
			preg_match('/if(.*?):/', $studentAns, $ifS);
			if (count($if) > 0){ //Did the professor answer contain an If statement?
				if (count($ifS) > 0){ //If it did, the student should have an If statement
					$p -= 2;
					if (strcmp($comment, "") == 0)
						$comment = "No if statement used, minus 2";
					else
						$comment .= "<br> No if statement used, minus 2";
				}
			}
			preg_match('/else:/', $profAns, $if2);
			preg_match('/else:/', $studentAns, $ifS2);
			if (count($if2) > 0){ //Did the professor answer contain an else statement?
				if (count($ifS2) > 0){ //If it did, the student should have an else statement
					$p -= 1;
					if (strcmp($comment, "") == 0)
						$comment = "No else statement used, minus 1";
					else
						$comment .= "<br> No else statement used. minus 1";
				}
			}
			preg_match('/elif(.*?):/', $profAns, $if3);
			preg_match('/elif(.*?):/', $studentAns, $ifS3);
			if (count($if3) > 0){ //Did the professor answer contain an else if statement?
				if (count($ifS3) > 0){ //If it did, the student should have an else if statement
					$p -= 2;
					if (strcmp($comment, "") == 0)
						$comment = "No else if statement used, minus 2";
					else
						$comment .= "<br> No else if statement used, minus 2";
				}
			}			
		}
		
		//First overwrite contents
		file_put_contents($filename, '');
		$output = [];
		file_put_contents($filename, $studentAns);
		//If the student's answer doesn't have a function, we need to declare one to test the program		
		if (!preg_match('/def (.*?)\(/', $studentAns) && preg_match('/def (.*?)\(/', $profAns)){
			$lines = file($filename);
			preg_match('/(.*?):/', $profAns, $funct); //Get the function the answer contains
			array_unshift($lines, $funct[0]."\n");
			$c = count($lines);
			for ($j = 1; $j < $c; $j++){ //The first line at index 0 is what we just put in. Don't indent.
				$lines[$j] = "\t".$lines[$j]; //Attempt to fix the indentation 
			}
			file_put_contents($filename, '');
			file_put_contents($filename, implode($lines));
			preg_match('/def (.*?)\(/', $funct[0], $sFunctionName); //This is now the student's function name, BUT be sure to note that they will still get points off for misnamed parameters
			$studentAns = file_get_contents($filename);
		}


		//Check to see if it compiles alright
		exec("python ".$filename." 2>&1", $output);
		if (strcmp($output[0], "") != 0){ //There is some error message, initially was 1
			$p -= 3;
			//Let's see what the error is
			preg_match('/:\s*([\S\s]+)/', end($output), $error);
			if (strcmp($error[1], "") != 0){
				if ($comment == '')
					$comment .= "Initial compilation error: ".$error[1].", minus 3"; 
				else
					$comment .= ", Initial compilation error: ".$error[1].", minus 3";
			}
			if (preg_match('/indented/', $error[1])){ //There was an indentation error, let's try and fix it
				$stopper = 0;
				while (strcmp($error[1], "") != 0 && preg_match('/indented/', $error[1]) && $stopper < 20){
					$eL = '';
					for ($y = 0; $y < count($output); $y++){
						preg_match('/line ([\d]+)/', $output[0], $eL); //Get the error line
						if ($eL[0] != "")
							break;
					}
					$lnes = file($filename);
					$lnes[(int)$eL[1] - 1] = "\t".$lnes[(int)$eL[1] - 1];
					file_put_contents($filename, '');
					file_put_contents($filename, implode($lnes));
					$studentAns = file_get_contents($filename);
					//We need to exec again
					$error = '';
					$output = '';
					exec("python ".$filename." 2>&1", $output);
					preg_match('/:\s*([\S\s]+)/', end($output), $error);
					$stopper++;
				}
			}
		}
		//There were no compile errors, let's put in some test cases
		//Let's get an array of the test cases
		$delim = ";\n";
		$tcs = [];
		$token = strtok($testCases, $delim);
		array_push($tcs, $token);
		while ($token != null){
			$token = strtok($delim);
			if ($token != null)
				array_push($tcs, $token);
		}
		
		//Let's get the expected answers and parameters to pass into the program
		$answer = '';
		$passCount = 0;
		$questionCount = 0;
		$originalContent = file_get_contents($filename);
		$prevC = '';
		$pState = false;
		for ($j = 0; $j < count($tcs); $j++){
			//Make sure we start from scratch
			file_put_contents($filename, $originalContent);
			$sParams = '/'.$pFunctionName[1].'\((.*)\)/';
			preg_match($sParams, $tcs[$j], $params);
			//Now that we have the test case parameters, let's pass them into the student's function
			file_put_contents($filename, "\no = ".$sFunctionName[1]."(".$params[1].")\nprint o", FILE_APPEND | LOCK_EX); //Add a function call with the parameters
			$o = [];
			exec("python ".$filename." 2>&1", $o);
			//Check against the right answers
			preg_match('/=\s*([\S\s]+)/', $tcs[$j], $answer);
			
			//Also let's see what the errors were
			if (count($o) > 1){ //There is some error message, one is returned if it's fine
				//Let's see what the error is
				preg_match('/:\s*([\S\s]+)/', end($o), $e);
				if (strcmp($e[1], "") != 0){ 
					if (strcmp($prevC, $e[1]) != 0){
						$p -= 2;
						if ($comment == '')
							$comment .= $e[1].", minus 2";
						else
							$comment .= ", ".$e[1].", minus 2"; 
					$prevC = $e[1];
					}
				}
			}
			
			if ($o[0] == $answer[1]){
				$passCount++;
				//They might have passed the test case, but is it because they have a print statement?
				//Theoretically, this should not work, because a python program will do the print statement and then output "none," but let's be harsh and give them a zero.
				if (preg_match("/print/", $studentAns) && $pState == false){
					$p = 0;
					$pState = true;
					if ($comment == '')
					$comment .= "You used a print statement. Nice try. No credit."; 
				else
					$comment .= ", You used a print statement. Nice try. No credit."; 
				}
			}
			else { //This test case was not passed
				$tcpOff = $questionPoints * 0.15;
				$p -= $tcpOff;
				if ($comment == '')
					$comment .= "Test case ".($j + 1)."(".$tcs[$j].") failed, minus ".$tcpOff; 
				else
					$comment .= ", Test case ".($j + 1)."(".$tcs[$j].") failed, minus ".$tcpOff; 
			}
			$questionCount++;
		}
		//Get the professor's parameters
		$pParams = '/'.$pFunctionName[1].'\((.*)\)/';
		preg_match($pParams, $profAns, $ps);
		$pCases = [];
		$delim2 = ",\n";
		$token2 = strtok($ps[1], $delim2);
		array_push($pCases, $token2);
		while ($token2 != null){
			$token2 = strtok($delim2);
			if ($token2 != null)
				array_push($pCases, $token2);
		}
		//Let's get the student's parameters, as well, to see if they named them correctly.
		$sParams = '/'.$sFunctionName[1].'\((.*)\)/';
		preg_match($sParams, $studentAns, $params);
		$sCases = [];
		$delim3 = ",\n";
		$token3 = strtok($params[1], $delim3);
		array_push($sCases, $token3);
		while ($token3 != null){
			$token3 = strtok($delim3);
			if ($token3 != null)
				array_push($sCases, $token3);
		}
		//Check if the student has the correct number of parameters
		if (count($sCases) != count($pCases)){
			$p -= 2;
			if (strcmp($comment, "") == 0)
				$comment = "Incorrect number of arguments";
			else 
				$comment .= ", Incorrect number of arguments, minus 2";
		}
		$pSize = '';
		if (count($sCases) > count($pCases))
			$pSize = count($pCases);
		else
			$pSize = count($sCases);	
	
		//Check to see if we've subtracted so many points that they now have zero
		if ($p < 0)
			$p = 0;
		
		$comment = str_replace('"', "", $comment);
		$comment = str_replace("'", "", $comment);

			//When all is said and done:
			$questionEval = array(
					'ucid' => $studentId,
					'quesId' => $questionId,
					'qScore' => $p,
					'iComment' => (string)$comment
			);		
			array_push($finalData, $questionEval);
			unset($comment);
		}
		//Post to database
		$url = 'https://web.njit.edu/~nho2/CS490/RC/insertGrade.php';
		$curlGrade = curl_init();
		$curlOptions = array(CURLOPT_URL => $url,
						 CURLOPT_POST => 1,
						 CURLOPT_FOLLOWLOCATION => 0,
						 CURLOPT_RETURNTRANSFER => 1,
						 CURLOPT_POSTFIELDS => http_build_query($finalData),
						 CURLOPT_HEADER => 0);
		curl_setopt_array($curlGrade, $curlOptions);
		$response = curl_exec($curlGrade);
		curl_close($curlGrade);	

		echo $response;
	}
	else {
		echo json_encode("Something went wrong!");		
	}
?>