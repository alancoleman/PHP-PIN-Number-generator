<!DOCTYPE html>
<!--[if IE 8]> 				 
<html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> 
<html class="no-js" lang="en"> <!--<![endif]-->
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width" />
	<title>PIN Number Application</title>
	<link rel="stylesheet" href="css/normalize.css" />
	<link rel="stylesheet" href="css/foundation.css" />
	<script src="js/vendor/custom.modernizr.js"></script>
</head>
<body>
	<div class="row">
		<div class="large-12 columns">
			<h2>PIN Number application</h2>
			<p>A random 4 digit PIN Number with integers repeated no more than twice</p>
			<hr />
			<!-- Forms to trigger PIN Number creation -->
			<form name="randomNumGeneratorForm" action="index.php" method="post">
				<input type="hidden" name="working" value="0">
				<input type="hidden" name="run" value="true">
				<input type="submit" value="Generate Pin" class="button">
			</form>
			<form name="randomNumGeneratorForm" action="index.php#pin" method="post">
				<input type="hidden" name="working" value="1">
				<input type="hidden" name="run" value="true">
				<input type="submit" value="Generate Pin (See working)" class="button">
			</form>
<?php
// set showDevWorking variable depending on form selection
if (empty($_POST['working'])){
	$showDevWorking = 0;		//set variable to display working
} else {
	if ($_POST['working'] == 1){
		$showDevWorking = 1;	//set variable to display working
	} else {
		$showDevWorking = 0;	//set variable to display working
	}
}

$randomNumberLength 		= 4;				//set variable for length of number
$randomNumberTolerance 		= 3;				//set variable for amount identical integers to be tolerated
$databaseMaxRecords 		= 10;				//set initial variable to be used in calculation of database records
$databaseMaxRecordsWeigh 	= ($randomNumberTolerance) * 9 * 9 + $randomNumberLength; //calculate the number of records that our tolerance will make impossible
for ($i = 1; $i < $randomNumberLength; $i++){				//loop through length
	$databaseMaxRecords = $databaseMaxRecords * 10;			//set random number and concatenate
}
$databaseMaxRecords = ($databaseMaxRecords - $databaseMaxRecordsWeigh); // calculate the maximum amout of database records

if ($showDevWorking == 1){
	echo "<p>Max amount of database records: " . $databaseMaxRecords . "</p>";
	echo "<hr />";
}

if (empty($_POST['run'])){
	echo "<p>Plese use the buttons to display a new PIN Number</p>";
} else {

	$pincheckArray = queryPinNumbers();  // run queryPinNumbers function to pull all used PIN numbers into an array
	// run getPin function carrying arguments across
	$attemptPin = getPin($randomNumberLength,$randomNumberTolerance,$pincheckArray,$showDevWorking);
	if ($showDevWorking == 1){
		echo "<p>PIN Number: " . $attemptPin . "</p>";
	}

	$pinNumber = $attemptPin; // Set PIN Number

	updateDatabase($pinNumber,$databaseMaxRecords,$showDevWorking); // Update database with new pin number and display results
	echo "<a name='pin'></a>";
	echo "<hr/><div id='pin'><h2><span class='radius success label'>PIN: " . $pinNumber . "</span></h2></div>";
}

// database connection function
function dbconnect()
	{
	# LOCAL
	$username 			= "root";
	$password 			= "";
	$database 			= "pin";
	$hostname 			= "localhost";
	# LIVE

	try {   
		# MySQL with PDO_MYSQL  
		$set = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
		$set->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		return $set; 
	}  
	catch(PDOException $e) {  
		echo $e->getMessage();  
	}
}
// queryPinNumbers function to pull all used PIN numbers into an array
function queryPinNumbers()
	{
	// Query database for used pin numbers to check new random number against
	$dbh = dbconnect();
	$checksql 		= "SELECT pin FROM number";
	$pincheck 		= $dbh->query($checksql); 
	$pincheck->setFetchMode(PDO::FETCH_ASSOC);
	$numpincheck  	= $pincheck->rowCount(); 
	// if results exist write to an array
	if ($numpincheck > 0) {
		while($row = $pincheck->fetch(PDO::FETCH_ASSOC)) {   	
			$pincheckArray[] = $row;
		}		
	} else {
		$pincheckArray[] = null;
	}
	
	//	Close database connection
	$dbh = null; 
	// return array
	return $pincheckArray;
}
// class containing function(s) for PIN Number creation
class Pin_index 
	{
	// Function to create and validate PIN Number
	public function randomNumGenerator($randomNumberLength,$randomNumberTolerance,$pincheckArray,$showDevWorking)
	{
		do
		{
			do
			{
				$stringCountBoo = 0;				//set variable
				$number = ''; 						//set variable
				for ($i = 0; $i < $randomNumberLength; $i++){	//loop through length
					$number .= rand(0,9);			//set random number and concatenate
				}
				if ($showDevWorking == 1){
					echo "<p>Raw PIN Number: " . $number. "</p>";
					echo "<p>Raw PIN Number (and number of times integer occurs): ";
				}
				for($i=0;$i<strlen($number);$i++) 	//set random number and concatenate
				{ 
					$stringCount = 0; 				//set variable
					$stringCount = substr_count($number, $number[$i]); //count the amount of each integer in the PIN
					if ($showDevWorking == 1){
						echo $number[$i]; 
						echo "(" . $stringCount . ")";
					}
					if ($stringCount == $randomNumberTolerance) {		//check integer account against tolerance variable
						$stringCountBoo = 0;
						echo "<p>Number: " . $number[$i] . " found : " . $stringCount . " times.</p>";
						break;
					}else{
						$stringCountBoo = 1;
					}
				} 
			}
			while ($stringCountBoo == 0);  	//continue until $stringCountBoo is satisfied 

			$rawpinNumber = $number; 		//set variable

			if ($showDevWorking == 1){
				echo "</p>";
				echo "<p>Raw PIN Number to match against array: " . $rawpinNumber . "</p>";
				echo "<br />";
			}
			
			foreach($pincheckArray as $val) {   		//loop through array of used PIN Numbers
				if ($val['pin'] == $rawpinNumber) {		//check PIN Number against PIN numbers in the array
					$numpincheck = 1;
					break;
				} else {
					$numpincheck = 0;
				}
			}
			if ($showDevWorking == 1){
				echo "<p>Matched against array = " . $numpincheck . "</p>";
			}
			
			}
		while ($numpincheck !== 0);						//continue until $numpincheck is satisfied 
		return $rawpinNumber; 							//return number as a string	
	}
}

// function to create and check PIN Number
function getPin($randomNumberLength,$randomNumberTolerance,$pincheckArray,$showDevWorking)
	{
	$randomNumber = new Pin_index();
		$rawpinNumber = $randomNumber->randomNumGenerator($randomNumberLength,$randomNumberTolerance,$pincheckArray,$showDevWorking);
		if ($showDevWorking == 1){
			echo "<hr /><p>Raw PIN Number: " . $rawpinNumber . "</p>";
		}
	return $rawpinNumber;	
}
// function to update database with new PIN number
function updateDatabase($pinNumber,$databaseMaxRecords,$showDevWorking)
	{
	$dbh = dbconnect();
	try {
		// Prepare SQL Insert statement
		$insert = "INSERT INTO number (pin) VALUES (:pin)";
		$statement = $dbh->prepare($insert);
		$statement->bindParam(':pin', $pinNumber); 
		$statement->execute();

		$tblupdateid = $dbh->lastInsertId(); 
		if ($showDevWorking == 1){
			echo "<hr />";
			echo "<p>Pin number " . $pinNumber . " added to database</p>";
			echo "<p>Number of records in database: " . $tblupdateid . "</p>";
		}
		
		if ($tblupdateid >= $databaseMaxRecords) {  //check tblupdateid against the databaseMaxRecords var and act accordingly
			deletDatabase($showDevWorking);
		}
	
		// EMPTY RESOURCES
		$statement->closeCursor();
	} catch(PDOException $e) {
		echo 'ERROR: ' . $e->getMessage();
	}

	$dbh = null;
}
// function to delete database records so PIN Number list can start again
function deletDatabase($showDevWorking)
	{
	$dbh = dbconnect();
	try {
		// Prepare SQL delete statement
		$delete = "TRUNCATE TABLE number";
		$statement = $dbh->prepare($delete);
		$statement->execute();


		if ($showDevWorking == 1){
			echo "<hr />";
			echo "<p>Database cleared of all records</p>";
		}
		// EMPTY RESOURCES
		$statement->closeCursor();
	} catch(PDOException $e) {
		echo 'ERROR: ' . $e->getMessage();
	}
	$dbh = null;
	
}
?>
</div>
</div>
</body>
</html>