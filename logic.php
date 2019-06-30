<?php

	function getAnswerWithoutLetters($word){
		$newWord = "";
		for ($i = 0; $i < strlen($word); $i++){
			$newWord = $newWord . "_";
		}
		
		return $newWord;
	}
	
	function guess($answer, $letter, $currentWord){
		for ($i = 0; $i < strlen($answer); $i++){
			if ($answer[$i] == $letter){
				$currentWord[$i] = $letter;
			}
		}
		return $currentWord;
	}
	
	function checkIfWordContainsLetter($answer, $letter){
		for ($i = 0; $i < strlen($answer); $i++){
			if ($answer[$i] == $letter){
				return true;
			}
		}
		return false;
	}
	
	function addToWrongWords($letter, $array){
		$array_push($letter);
	}
	
?>