<?php
session_start();
if(isset($_POST['jsonString'])){
	$json_parse = json_decode($_POST['jsonString']);
	$match = 0;
	if($json_parse->{connection} == "getStatus"){
		$match = 1;
		if(isset($_SESSION['connection'])){
			$_SESSION['nonce'] = RandomString(); 
			echo json_encode(array('connection' => true, 'auth' => $_SESSION['auth'], 'nonce' => $_SESSION['nonce']));
		}else{
			$_SESSION['auth'] = "false";
			$_SESSION['nonce'] = RandomString(); 
			$_SESSION['connection'] = true;
			echo json_encode(array('connection' => false, 'auth' => $_SESSION['auth'], 'nonce' => $_SESSION['nonce']));
		}
	}
	//CREATE TABLE rules(id INTEGER PRIMARY KEY, enabled boolean, name TEXT, timeprofileID INTEGER, track TEXT, remarks TEXT);
	if(($json_parse->{'getRules'} == "true") && ($_SESSION['auth'] == 'true')){
		$dbhandle = new SQLite3('db_school.db');
		$results = $dbhandle->query("SELECT * FROM rules");	
		if($results){
			$rules_array = array();
			$i = 0;
                        while ($row = $results->fetchArray()){
				$match = 1;
				$rules_array[$i]->{'name'} = $row['name'];
				$rules_array[$i]->{'id'} = $row['id'];
				$rules_array[$i]->{'enabled'} = $row['enabled'];
				$rules_array[$i]->{'track'} = $row['track'];
				$rules_array[$i]->{'remarks'} = $row['remarks'];
				$rules_array[$i]->{'timeprofileID'} = $row['timeprofileID'];
				$i++;
			}
			echo json_encode(array('allRules' => $rules_array));
		}
		$dbhandle->close();
	}
	if(($json_parse->{'queryTimeprofile'} == "true") && ($_SESSION['auth'] == 'true')){
		$dbhandle = new SQLite3('db_school.db');
		$results = $dbhandle->query("SELECT * FROM timeprofile");	
		if($results){
			$timeprofile_array = array();
			$i = 0;
                        while ($row = $results->fetchArray()){
				$match = 1;
				$timeprofile_array[$i]->{'name'} = $row['name'];
				$timeprofile_array[$i]->{'id'} = $row['id'];
				$timeprofile_array[$i]->{'timeprofileID'} = $row['timeprofileID'];
				$timeprofile_array[$i]->{'hour'} = $row['hour'];
				$timeprofile_array[$i]->{'minute'} = $row['minute'];
				$timeprofile_array[$i]->{'mon'} = $row['mon'];
				$timeprofile_array[$i]->{'tue'} = $row['tue'];
				$timeprofile_array[$i]->{'wed'} = $row['wed'];
				$timeprofile_array[$i]->{'thu'} = $row['thu'];
				$timeprofile_array[$i]->{'fri'} = $row['fri'];
				$timeprofile_array[$i]->{'sat'} = $row['sat'];
				$timeprofile_array[$i]->{'sun'} = $row['sun'];
				$i++;
			}
			echo json_encode(array('timeprofiles' => $timeprofile_array));
		}
		$dbhandle->close();
	}
	if(($json_parse->{'queryRules'} == "true") && ($_SESSION['auth'] == 'true')){
		$dbhandle = new SQLite3('db_school.db');
		$results = $dbhandle->query("SELECT * FROM rules");	
		if($results){
			$rules_array = array();
			$rules_profile = null;
			$i = 0;
                        while ($row = $results->fetchArray()){
				$rules_array[$i]->{'name'} = $row['name'];

				$res_time = $dbhandle->query("SELECT * FROM timeprofile where timeprofileID = {$row['timeprofileID']}");	
				if($res_time){
					while ($row1 = $res_time->fetchArray()){
						$rules_profile = $row1[name];
						break;
					}	
					$rules_array[$i]->{'timeprofileName'} = $rules_profile;
					$match = 1;
				}
				
				$rules_array[$i]->{'timeprofileID'} = $row['timeprofileID'];
				$rules_array[$i]->{'id'} = $row['id'];
				$rules_array[$i]->{'enabled'} = $row['enabled'];
				$rules_array[$i]->{'track'} = $row['track'];
				$rules_array[$i]->{'remarks'} = $row['remarks'];
				$i++;
			}
			echo json_encode(array('Rules' => $rules_array));
		}
		$dbhandle->close();
	}	
	if(($json_parse->{'getUsers'} == "true") && ($_SESSION['auth'] == 'true')){
		$dbhandle = new SQLite3('db_school.db');
		$results = $dbhandle->query("SELECT * FROM users");	
		if($results){
			$users_array = array();
			$i = 0;
                        while ($row = $results->fetchArray()){
				$match = 1;
				$users_array[$i]->{'name'} = $row['name'];
				$users_array[$i]->{'id'} = $row['id'];
				$users_array[$i]->{'enabled'} = $row['enabled'];
				$users_array[$i]->{'username'} = $row['username'];
				$i++;
			}
			echo json_encode(array('allUsers' => $users_array));
		}
		$dbhandle->close();
	}
	if(($json_parse->{'deleteUser'}) && ($_SESSION['auth'] == 'true')){
		$myerror;	
		$match = 1;
		$dbhandle = new SQLite3('db_school.db');
		$myerror = $dbhandle->exec("delete  from users  where id = {$json_parse->{'deleteUser'}->{'id'}}");
		if(!$myerror){
			echo json_encode(array('deleteUser' => 'false'));
		}else{
			echo json_encode(array('deleteUser' => 'true'));
		}
		$dbhandle->close();
	}
	if(($json_parse->{'updateUser'}) && ($_SESSION['auth'] == 'true')){
		$myerror;	
		$match = 1;
		$dbhandle = new SQLite3('db_school.db');
		$enabled;
		if($json_parse->{'updateUser'}->{'enabled'} == true){
			$enabled = 'true';
		}else{
			$enabled = 'false';
		}
		$myerror = $dbhandle->exec("update  users set enabled = '$enabled', password = '{$json_parse->{'updateUser'}->{'password'}}', name = '{$json_parse->{'updateUser'}->{'name'}}', username = '{$json_parse->{'updateUser'}->{'username'}}' where id = {$json_parse->{'updateUser'}->{'id'}}");
		if(!$myerror){
			echo json_encode(array('updateUser' => 'false'));
		}else{
			echo json_encode(array('updateUser' => 'true'));
		}
		$dbhandle->close();
	}
	if(($json_parse->{'createUser'}) && ($_SESSION['auth'] == 'true')){
		$myerror;	
		$match = 1;
		$dbhandle = new SQLite3('db_school.db');
		$enabled;
		if($json_parse->{'createUser'}->{'enabled'} == true){
			$enabled = 'true';
		}else{
			$enabled = 'false';
		}
		$myerror = $dbhandle->exec("insert  into users (enabled, password, name, username) values  ('$enabled', '{$json_parse->{'createUser'}->{'password'}}', '{$json_parse->{'createUser'}->{'name'}}', '{$json_parse->{'createUser'}->{'username'}}')");
		if(!$myerror){
			echo json_encode(array('createUser' => 'false'));
		}else{
			echo json_encode(array('createUser' => 'true'));
		}
		$dbhandle->close();
	}
	
	if(($json_parse->{'toggleEnableRule'}) && ($_SESSION['auth'] == 'true')){
		$myerror;
		$match = 1;
		$status = "";
		$dbhandle = new SQLite3('db_school.db');
		$results = $dbhandle->query("SELECT * FROM rules where id = {$json_parse->{'toggleEnableRule'}->{'id'}}");
		
                if($results){
                        while ($row = $results->fetchArray()){
				if($row['enabled'] == 'true'){
					$status = 'false';
				}else{
					$status = 'true';
				}
			}
			$myerror = $dbhandle->exec("update rules set enabled = '$status' where id = {$json_parse->{'toggleEnableRule'}->{'id'}}");
			if(!$myerror){
				echo json_encode(array('updatedToggleEnableRule' => 'false'));
			}else{
				echo json_encode(array('updatedToggleEnableRule' => 'true'));
			}
		}else{
			echo json_encode(array('updatedToggleEnableRule' => 'false'));
		}
		$dbhandle->close();
	}
	if(($json_parse->{'password'}) && (isset($_SESSION['nonce']))){
		$i = 0;
		$match = 1;
		$dbhandle = new SQLite3('db_school.db');
		$results = $dbhandle->query("SELECT * FROM users");
		if($results){
                        while ($row = $results->fetchArray()){
				$server_password = hash_hmac ( "sha1", $row['password'] , $_SESSION['nonce']);
				if(($server_password == $json_parse->{password}) && ($row['enabled'] == 'true')){
					$_SESSION['auth'] = "true";
					$_SESSION['userID'] = $row['id'];
					$_SESSION['userName'] = $row['name'];
					echo json_encode(array('password' => 'ok', 'auth' => $_SESSION['auth']));
					$i = 1;
				}			
			}
		}
		if($i == 0){
			echo json_encode(array('password' => 'error'));
		}
		$dbhandle->close();
	}
	if(($json_parse->{logout}) && (isset($_SESSION['auth']))){
		if($json_parse->{logout} == 'true'){
			$match = 1;
			session_destroy();
			echo json_encode(array('restart' => 'true'));
		}
	}
	if(($json_parse->{rpistatus}) && ($_SESSION['auth'] == 'true')){
		if($json_parse->{rpistatus} == 'true'){
			$match = 1;
			$rpi_load = shell_exec("cat /proc/loadavg | awk '{print $1 }'");
			$rpi_date = date('Y-m-d H:i:s');
			$rpi_freeMemory = shell_exec("free -h | awk 'NR==3 {print $4 }'");
			$ut = strtok( exec( "cat /proc/uptime" ), "." );
			$days = sprintf( "%2d", ($ut/(3600*24)) );
			$hours = sprintf( "%2d", ( ($ut % (3600*24)) / 3600) );
			//$min = sprintf( "%2d", ($ut % (3600*24) % 3600)/60  );
			//$sec = sprintf( "%2d", ($ut % (3600*24) % 3600)%60  );
			if($days == 1){
				$paev = " päev";
			}else{
				$paev = " päeva";
			}
			$rpi_uptime = $hours . " tundi ja " . $days . $paev; 
			echo json_encode(array('rpiDate' => $rpi_date, 'rpiFreeMemory' => $rpi_freeMemory, 'rpiUptime' => $rpi_uptime, 'rpiLoad' => $rpi_load));
		}
	}
	if(($json_parse->{getFiles}) && ($_SESSION['auth'] == 'true')){
		if($json_parse->{getFiles} == 'true'){
			if ($handle = opendir('../files/')){
				$match = 1;
				$allFiles = array();
				$i = 0;
			    	while (false !== ($entry = readdir($handle))) {
					if ($entry != "." && $entry != "..") {
					    	$allFiles[$i]->{'filename'} = $entry;
						$i++;
					}
			    	}
				echo json_encode(array('allFiles' => $allFiles));
			}
    			closedir($handle);
		}
	}
	if(($json_parse->{deleteFile}) && ($_SESSION['auth'] == 'true')){
		if($json_parse->{deleteFile}->{filename}){
			$match = 1;
			$check = unlink("../files/" . $json_parse->{deleteFile}->{filename});
			if($check){
				echo json_encode(array('deleteFile' => 'true'));
			}else{
				echo json_encode(array('deleteFile' => 'error'));
			}
		}
	}
	if(!$match){
		echo json_encode(array('error' => 'notFound'));
	}
}else if(is_uploaded_file($_FILES['upload_file']['tmp_name'])){
	$uploaddir = '../files/';
	if(is_dir($uploaddir)){
		if (is_uploaded_file($_FILES['upload_file']['tmp_name'])) {
			$name = $_FILES['upload_file']['name'];
			$uploaddir = $uploaddir . $name;
			$result = move_uploaded_file($_FILES['upload_file']['tmp_name'],$uploaddir);
			if ($result == 1){ 
				echo "<p>File uploaded.</p>";
			}else { 
				echo("<p>Error uploading file.  Please contact an administrator</p>");
			}
		}else{
			echo("<p>Error uploading file.  Please contact an administrator 1 </p>");

		}
	}else{
		echo("<p>Error uploading file.  Please contact an administrator 2 </p>");

	}
}

Function RandomString(){
	$characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$randstring = '';
	$random = '';
	
	for ($j = 0; $j < 10; $j++) {
		    $randstring = "";
		for ($i = 0; $i < 10; $i++) {
		    $randstring = $characters[rand(0, (strlen($characters) -1 ))];
		}
		$random .=  $randstring;
	}
	return $random;
}
?>

