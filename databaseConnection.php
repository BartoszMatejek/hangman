<?php
	function connectToDatabase(){
		$servername = "localhost";
		$username = "user";
		$password = "password";
		$dbname = "dbname";


		// Create connection
		$connection = mysqli_connect($servername, $username, $password, $dbname);
		
		// Check connection
		if (!$connection) {
			die("Connection failed: " . mysqli_connect_error());
		} else {
			return $connection;
		}
	}
	
	function getRandomWordFromDatabase(){
		$connection = connectToDatabase();
		
		$sql = "SELECT CONVERT(CAST(word as BINARY) USING utf8) as word FROM words
				ORDER BY RAND()
				LIMIT 1";
		$result = mysqli_query($connection, $sql);
		
		if (mysqli_num_rows($result) > 0) {
			// output data of each row
			while($row = mysqli_fetch_assoc($result)) {
				$word = $row["word"];
				
				return $word;
			}
		} else {
			echo "0 results";
		}
	}
?>