<?php session_start();
?>
<?php
	header( "Content-type: application/json; charset=utf-8" );
	header("Access-Control-Allow-Origin: *");
	include 'databaseConnection.php';
	include 'logic.php';
	
	function setNewWord(){
		$randomWord = getRandomWordFromDatabase();
		while (in_array($randomWord, $_SESSION['guessed_words'])){
			$randomWord = getRandomWordFromDatabase();
		}
		$currentProgress = getAnswerWithoutLetters($randomWord);
		$missedLetters = array();
		$remainingGuesses = 6;
		$_SESSION['word'] = $randomWord;
		$_SESSION['currentProgress'] = $currentProgress;
		$_SESSION['missedLetters'] = $missedLetters;
		$_SESSION['remainingGuesses'] = $remainingGuesses;
	}
	
	function open_session() {
		$_SESSION['is_open'] = TRUE;
	}

	function close_session() {
		session_write_close();
		$_SESSION['is_open'] = FALSE;
	}

	function destroy_session() {
		session_destroy();
		$_SESSION['is_open'] = FALSE;
	}

	function session_is_open() {
		if ($_SESSION['is_open'] != null){
			return true;
		}
	}
	
	function initializeGame(){
		$randomWord = getRandomWordFromDatabase();
		$currentProgress = getAnswerWithoutLetters($randomWord);
		$missedLetters = array();
		$remainingGuesses = 6;
		$points = 0;
		$_SESSION['word'] = $randomWord;
		$_SESSION['currentProgress'] = $currentProgress;
		$_SESSION['missedLetters'] = $missedLetters;
		$_SESSION['remainingGuesses'] = $remainingGuesses;
		$_SESSION['points'] = $points;
		$_SESSION['guessed_words'] = array();
	}
	if(!isset($_SESSION['is_open'])) {
        initializeGame();
		$_SESSION['is_open'] = true;
    }
	
	$request_method=$_SERVER["REQUEST_METHOD"];
	
	switch($request_method)
	{
		case 'GET':
			$output = array(
			"letters" => $_SESSION['currentProgress'],
			"wrong_letters" => $_SESSION['missedLetters'],
			"remaining_guesses" => $_SESSION['remainingGuesses'],
			"points" => $_SESSION['points']
			);
			
			echo json_encode($output);
			break;
		case "POST":
			$json = file_get_contents('php://input');
			$obj = json_decode($json);
			$properties = get_object_vars($obj);
			$letter = $properties['letter'];
			$letter = strtoupper($letter);
			if(ctype_alpha($letter) || preg_match('/^[A-PR-UWY-ZĄĆĘŁŃÓŚŹŻ]*$/iu',$letter)){
				$letter = strtoupper($letter);
				if(checkIfWordContainsLetter($_SESSION['word'], $letter)){
					$currentProgress = guess($_SESSION['word'],$letter,$_SESSION['currentProgress']);
					$_SESSION['currentProgress'] = $currentProgress;
					$_SESSION['points'] = $_SESSION['points'] +1; 
					if($_SESSION['currentProgress'] == ($_SESSION['word'])){
						$output = array(
								"success" => "true",
								"correct_word" => $_SESSION['word'],
								"points" => $_SESSION['points'],
								"letters" => $_SESSION['currentProgress'],
								"wrong_letters" => $_SESSION['missedLetters'],
								"remaining_guesses" => $_SESSION['remainingGuesses'],
								"points" => $_SESSION['points']
							);
						echo json_encode($output);
						array_push($_SESSION['guessed_words'], $_SESSION['word']);
						setNewWord();
						break;
					}
					else {
						$output = array(
									"letters" => $_SESSION['currentProgress'],
									"wrong_letters" => $_SESSION['missedLetters'],
									"remaining_guesses" => $_SESSION['remainingGuesses'],
									"points" => $_SESSION['points']
								);
					echo json_encode($output);
					break;
					}
						
				} else{
					if (in_array($letter, $_SESSION['missedLetters']) || strpos($_SESSION['currentProgress'], $letter) !== false) {
						$output = array(
						"message" => "Wprowadzałeś już tę literę!"
						);
						echo json_encode($output);
						break;
					} else{
						$letter = strtoupper($letter);
						array_push($_SESSION['missedLetters'], $letter);
					}
					$_SESSION['remainingGuesses'] = $_SESSION['remainingGuesses'] - 1;
					if($_SESSION['remainingGuesses'] == 0){
						$output = array(
								"word" => $_SESSION['word'],
								"remaining_guesses" => $_SESSION['remainingGuesses'],
								"wrong_letters" => $_SESSION['missedLetters']
								);
						echo json_encode($output);
						destroy_session();
						break;
					}else {
						$output = array(	
								"letters" => $_SESSION['currentProgress'],
								"wrong_letters" => $_SESSION['missedLetters'],
								"remaining_guesses" => $_SESSION['remainingGuesses'],
								"points" => $_SESSION['points']
					);
					echo json_encode($output);
					break;
					}
					
				}
			} else{
				$output = array(
						"message" => "Wprowadzono nieprawidłowy znak. Możesz wprowadzić wyłącznie litery!"
						);
				echo json_encode($output);
			}
			break;
		default:
			// Invalid Request Method
			header("HTTP/1.0 405 Method Not Allowed");
			break;
	}
?>