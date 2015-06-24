<?php
ob_start();
session_start();

function connect(){
	// credentials for loggin into the mysql Server
$host = "localhost";
$user = "root";
$password = "";
$database = "adwords";
	// connection string to the server
$connection = mysqli_connect($host, $user, $password) or die("Error connecting to the SERVER " . mysqli_error());

// selection string for the database
if (!mysqli_select_db($connection, $database)) {
	//create the database if it doesnt exist
	$query = "CREATE DATABASE IF NOT EXISTS adwords";
	mysqli_query($connection, $query);
	$database = "adwords";
}
mysqli_select_db($connection, $database);

return $connection;
}


//this functions check for the existence of a table in a database, returns true or false
function does_table_exist($this_table) {
	$connection = connect();
	//$this_table = "personal_details";
	$checktable = mysqli_query($connection, "SHOW TABLES LIKE '$this_table'");
	$table_exists = mysqli_num_rows($checktable) > 0;
	return $table_exists;
}

//this function creates a new database table, the first parameter is the table name, while the secon is an array containing the fields.
function create_new_table($table_name, $rows) {
	$connection = connect();
	$expanded_array = "";
	foreach ($rows as $key => $value) {
		$expanded_array = $expanded_array . $key . " " . $value . ",";
	}
	$query = "CREATE TABLE IF NOT EXISTS `$table_name` (" . substr($expanded_array, 0, -1) . ")";
	$run = mysqli_query($connection, $query);
	return $run;
}

//function for adding entries to a a table
function populate_database_table($table_name, $rows_of_data) {
	$connection = connect();
	$expanded_keys = "";
	$expanded_values = "";
	foreach ($rows_of_data as $key => $value) {
		$expanded_keys = $expanded_keys . $key . ",";
		$expanded_values = $expanded_values . "'" . $value . "',";
	}
	$query = "INSERT INTO `$table_name`(" . substr($expanded_keys, 0, -1) . ") values(" . substr($expanded_values, 0, -1) . ")";
	$run = mysqli_query($connection, $query);
	return $run;
}

//this function fetches user details from a table where the the query_by fied matches value provided provided matches a valid entry

function get_from_db($what_to_get, $table_name, $query_by ='', $value='') {
	$connection = connect();
		$find = $what_to_get;
		$expanded_find ="";
		foreach ($find as $key => $value1) {
		$expanded_find = $expanded_find . $value1 . ",";
		}
		$what_to_get = substr($expanded_find, 0, -1);
		
	if(($query_by!='')and($value!='')) {
		$query = "SELECT $what_to_get FROM `$table_name` WHERE `$query_by` = '$value'";
	
		$result = mysqli_query($connection, $query);
		$count = mysqli_num_rows($result);
		if ($count==0) {
			return false;
			echo "<div class='error' style='padding:2px;'>No Records found </div>";
		} else {
			//return array
			$return_value = array();
			while($line = mysqli_fetch_assoc($result)){
				$return_value[]=$line;
			}
			mysqli_free_result($result);
			return $return_value;
		}
	}
	else {
			$query = "SELECT $what_to_get FROM `$table_name`";
	
		$result = mysqli_query($connection, $query);
		$count = mysqli_num_rows($result);
		//echo $count;
	if ($count<1) {
			return false;
		} else {
			//return array
			$return_value = array();
			while($line = mysqli_fetch_assoc($result)){
				$return_value[]=$line;
			}
			mysqli_free_result($result);
			return $return_value;
		}
	}
}

//update a database table
function update_db($what_to_update, $table_name, $query_by ='', $value='')
{
	$connection = connect();
	$find = $what_to_update;
		$expanded_find ="";
		
		foreach ($find as $key => $value1) {
		$expanded_find = $expanded_find .$key ."='" . $value1 . "',";
		}
		$what_to_update = substr($expanded_find, 0, -1);
		
	if(mysqli_query($connection, "UPDATE `$table_name` SET $what_to_update WHERE `$query_by` = '$value'"))
		return TRUE;
	else
		return FALSE;
}
?>