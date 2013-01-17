<?php
session_start();
if(isset($_POST['jsonString'])){
	$json_parse = json_decode($_POST['jsonString']);
	$match = 0;
	if(isset($json_parse->connection)){
		if($json_parse->connection == "getStatus"){
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
	}
	//CREATE TABLE rules(id INTEGER PRIMARY KEY, enabled boolean, name TEXT, timeprofileID INTEGER, track TEXT, remarks TEXT);
	if((isset($json_parse->updateRule)) && ($_SESSION['auth'] == 'true')){
		$myerror;	
		$match = 1;
		$dbhandle = new SQLite3('db_school.db');
		$enabled;
		$myerror = $dbhandle->exec("update  rules set name = '{$json_parse->{'updateRule'}->{'name'}}', timeprofileID = '{$json_parse->{'updateRule'}->{'timeprofileID'}}', track = '{$json_parse->{'updateRule'}->{'track'}}' where id = {$json_parse->{'updateRule'}->{'id'}}");
		if(!$myerror){
			echo json_encode(array('updateRule' => 'false'));
		}else{
			echo json_encode(array('updateRule' => 'true'));
		}
		$dbhandle->close();
	}
	if((isset($json_parse->deleteRule)) && ($_SESSION['auth'] == 'true')){
		$myerror;	
		$match = 1;
		$dbhandle = new SQLite3('db_school.db');
		$myerror = $dbhandle->exec("delete from  rules  where id = {$json_parse->{'deleteRule'}->{'id'}}");
		if(!$myerror){
			echo json_encode(array('deleteRule' => 'false'));
		}else{
			echo json_encode(array('deleteRule' => 'true'));
		}
		$dbhandle->close();
	}
	if((isset($json_parse->createRule)) && ($_SESSION['auth'] == 'true')){
		$myerror;	
		$match = 1;
		$dbhandle = new SQLite3('db_school.db');
		$enabled;
		$myerror = $dbhandle->exec("insert into rules (name, timeprofileID, track) values ('{$json_parse->{'createRule'}->{'name'}}', '{$json_parse->{'createRule'}->{'timeprofileID'}}', '{$json_parse->{'createRule'}->{'track'}}')");
		if(!$myerror){
			echo json_encode(array('createRule' => 'false'));
		}else{
			echo json_encode(array('createRule' => 'true'));
		}
		$dbhandle->close();
	}
	if((isset($json_parse->getRules)) && ($_SESSION['auth'] == 'true')){
		if($json_parse->getRules == "true"){
			$dbhandle = new SQLite3('db_school.db');
			$results = $dbhandle->query("SELECT * FROM rules");	
			if($results){
				$match = 1;
				$rules_array = [];
				$i = 0;
				while ($row = $results->fetchArray()){
					$rules_array[$i]['name'] = $row['name'];
					$rules_array[$i]['id'] = $row['id'];
					$rules_array[$i]['enabled'] = $row['enabled'];
					$rules_array[$i]['track'] = $row['track'];
					$rules_array[$i]['remarks'] = $row['remarks'];
					$rules_array[$i]['timeprofileID'] = $row['timeprofileID'];
					$i++;
				}
				echo json_encode(array('allRules' => $rules_array));
			}
			$dbhandle->close();
		}
	}
	if((isset($json_parse->timeprofileEdit->configuration)) && ($_SESSION['auth'] == 'true')){
		$myerror;	
		$dbhandle = new SQLite3('db_school.db');
		$results = $dbhandle->query("delete from timeprofile where timeprofileID = {$json_parse->{'timeprofileEdit'}->{'id'}}");	
		if($results){
			$match = 1;
			$dbhandle->close();
			foreach($json_parse->timeprofileEdit->configuration as $a) {
				if($a->mon && $a->tue && $a->wed && $a->thu && $a->fri && $a->sat && $a->sun){
					$dbhandle = new SQLite3('db_school.db');
					$dbhandle->exec("insert into timeprofile values (?,{$json_parse->{'timeprofileEdit'}->{'id'}},{$a->hour},{$a->minute},'{$a->mon}','{$a->tue}','{$a->wed}','{$a->thu}','{$a->fri}','{$a->sat}','{$a->sun}','{$json_parse->{'timeprofileEdit'}->{'name'}}')");
					$dbhandle->close();
				}
			}
			echo json_encode(array('timeprofileEdit' => 'true'));
		}
	}
	if((isset($json_parse->timeprofileSave->configuration)) && ($_SESSION['auth'] == 'true')){
		$myerror;	
		$dbhandle = new SQLite3('db_school.db');
		$results = $dbhandle->query("SELECT * FROM timeprofile order by timeprofileID desc limit 1");	
		if($results){
			$match = 1;
			$dbhandle->close();
			$tmp_value = RandomNumber();
			foreach($json_parse->timeprofileSave->configuration as $a) {
				if($a->mon && $a->tue && $a->wed && $a->thu && $a->fri && $a->sat && $a->sun){
					$dbhandle = new SQLite3('db_school.db');
					$dbhandle->exec("insert into timeprofile values (?,{$tmp_value},{$a->hour},{$a->minute},'{$a->mon}','{$a->tue}','{$a->wed}','{$a->thu}','{$a->fri}','{$a->sat}','{$a->sun}','{$json_parse->{'timeprofileSave'}->{'name'}}')");
					$dbhandle->close();
				}
			}
			echo json_encode(array('timeprofileSave' => 'true'));
		}
	}
	if((isset($json_parse->timeprofileDelete->dodelete)) && ($_SESSION['auth'] == 'true')){
		if($json_parse->timeprofileDelete->dodelete == "true"){
			if($json_parse->timeprofileDelete->id){
				$myerror;	
				$match = 1;
				$dbhandle = new SQLite3('db_school.db');
				$myerror = $dbhandle->exec("delete from  timeprofile  where timeprofileID = {$json_parse->{'timeprofileDelete'}->{'id'}}");
				if(!$myerror){
					echo json_encode(array('timeprofileDelete' => 'false'));
				}else{
					echo json_encode(array('timeprofileDelete' => 'true'));
				}
				$dbhandle->close();
			}
		}
	}
	if((isset($json_parse->queryTimeprofile)) && ($_SESSION['auth'] == 'true')){
		if($json_parse->queryTimeprofile == "true"){
			$dbhandle = new SQLite3('db_school.db');
			$results = $dbhandle->query("SELECT * FROM timeprofile order by timeprofileID, hour, minute");	
			if($results){
				$match = 1;
				$timeprofile_array = [];
				$i = 0;
				while ($row = $results->fetchArray()){
					$timeprofile_array[$i]['name'] = $row['name'];
					$timeprofile_array[$i]['id'] = $row['id'];
					$timeprofile_array[$i]['timeprofileID'] = $row['timeprofileID'];
					$timeprofile_array[$i]['hour'] = $row['hour'];
					$timeprofile_array[$i]['minute'] = $row['minute'];
					$timeprofile_array[$i]['mon'] = $row['mon'];
					$timeprofile_array[$i]['tue'] = $row['tue'];
					$timeprofile_array[$i]['wed'] = $row['wed'];
					$timeprofile_array[$i]['thu'] = $row['thu'];
					$timeprofile_array[$i]['fri'] = $row['fri'];
					$timeprofile_array[$i]['sat'] = $row['sat'];
					$timeprofile_array[$i]['sun'] = $row['sun'];
					$i++;
				}
				echo json_encode(array('timeprofiles' => $timeprofile_array));
			}
			$dbhandle->close();
		}
	}
	if((isset($json_parse->queryRules)) && ($_SESSION['auth'] == 'true')){
		if($json_parse->queryRules == "true"){
			$dbhandle = new SQLite3('db_school.db');
			$results = $dbhandle->query("SELECT * FROM rules");
			$rules_array = [];
			if($results){
				$rules_profile = "";
				$i = 0;
				$match = 1;
				while ($row = $results->fetchArray()){
					$rules_array[$i]["name"] = $row['name'];

					$res_time = $dbhandle->query("SELECT * FROM timeprofile where timeprofileID = {$row['timeprofileID']}");	
					if($res_time){
						while ($row1 = $res_time->fetchArray()){
							$rules_profile = $row1['name'];
							$rules_array[$i]["timeprofileName"] = $rules_profile;
							break;
						}	
					}
					$rules_array[$i]['timeprofileID'] = $row['timeprofileID'];
					$rules_array[$i]['id'] = $row['id'];
					$rules_array[$i]['enabled'] = $row['enabled'];
					$rules_array[$i]['track'] = $row['track'];
					$rules_array[$i]['remarks'] = $row['remarks'];
					$i = $i + 1;
				}
				echo json_encode(array('Rules' => $rules_array, 'rulesResponse' => 'true'));
			}
			$dbhandle->close();
		}
	}	
	if((isset($json_parse->getUsers )) && ($_SESSION['auth'] == 'true')){
		if($json_parse->getUsers == "true"){
			$dbhandle = new SQLite3('db_school.db');
			$results = $dbhandle->query("SELECT * FROM users");	
			if($results){
				$match = 1;
				$users_array = [];
				$i = 0;
				while ($row = $results->fetchArray()){
					$users_array[$i]['name'] = $row['name'];
					$users_array[$i]['id'] = $row['id'];
					$users_array[$i]['enabled'] = $row['enabled'];
					$users_array[$i]['username'] = $row['username'];
					$i++;
				}
				echo json_encode(array('allUsers' => $users_array));
			}
			$dbhandle->close();
		}
	}
	if((isset($json_parse->deleteUser)) && ($_SESSION['auth'] == 'true')){
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
	if((isset($json_parse->updateUser)) && ($_SESSION['auth'] == 'true')){
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
	if((isset($json_parse->createUser)) && ($_SESSION['auth'] == 'true')){
		$myerror;	
		$match = 1;
		$dbhandle = new SQLite3('db_school.db');
		$enabled;
		if($json_parse->createUser->enabled == true){
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
	
	if((isset($json_parse->toggleEnableRule)) && ($_SESSION['auth'] == 'true')){
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
			$myerror = $dbhandle->exec("update rules set enabled = '$status' where id = {$json_parse->toggleEnableRule->id}");
			if(!$myerror){
				echo json_encode(array('updatedToggleEnableRule' => 'false'));
			}else{
				echo json_encode(array('updatedToggleEnableRule' => 'true'));
			}

			
			$results_1 = $dbhandle->query("SELECT * FROM rules where enabled = 'true'");
			$tmp_1 = 0;
                	if($results_1){
				$handle = fopen('cron.txt', 'w');
				while ($row_1 = $results_1->fetchArray()){
					$results_2 = $dbhandle->query("SELECT * FROM timeprofile where timeprofileID = {$row_1['timeprofileID']}");
					if($results_2){
						while ($row_2 = $results_2->fetchArray()){
							$tmp_1 = 1;
							$tmp_string = "";
							$tmp_string = $row_2['minute'] . " " . $row_2['hour'] . " * * ";
							$tmp = 0;
							if($row_2['sun'] == 'true') {$tmp_string = $tmp_string . "0"; $tmp = 1;}
							if($row_2['mon'] == 'true') {$tmp_string = $tmp_string . (($tmp == 1) ? "," : "") . "1"; $tmp = 1;}
							if($row_2['tue'] == 'true') {$tmp_string = $tmp_string . (($tmp == 1) ? "," : "") . "2"; $tmp = 1;}
							if($row_2['wed'] == 'true') {$tmp_string = $tmp_string . (($tmp == 1) ? "," : "") . "3"; $tmp = 1;}
							if($row_2['thu'] == 'true') {$tmp_string = $tmp_string . (($tmp == 1) ? "," : "") . "4"; $tmp = 1;}
							if($row_2['fri'] == 'true') {$tmp_string = $tmp_string . (($tmp == 1) ? "," : "") . "5"; $tmp = 1;}
							if($row_2['sat'] == 'true') {$tmp_string = $tmp_string . (($tmp == 1) ? "," : "") . "6"; $tmp = 1;}
							$tmp_string = $tmp_string . " /data/projects/schoolBell/web/start_track.sh '" . $row_1['track'] . "'\n";
							if ((is_writable('cron.txt')) && ($tmp)) {
								if (!$handle = fopen('cron.txt', 'a')) {
								}else if (fwrite($handle, $tmp_string) === FALSE) {
							    	}else{
									shell_exec("crontab cron.txt");
								}
							    	fclose($handle);
							} else {
							   // echo "The file $filename is not writable";
							}
						}
					}
				}
			}
			if (!$tmp_1){
				shell_exec("crontab -r");
			}
		}else{
			echo json_encode(array('updatedToggleEnableRule' => 'false'));
		}
		$dbhandle->close();
	}
	if((isset($json_parse->play->filename)) && ($_SESSION['auth'] == 'true')){
		$match = 1;
		shell_exec("/data/projects/schoolBell/web/start_track.sh '" . $json_parse->play->filename . "'");
		echo json_encode(array('play' => 'true'));
	}
	if((isset($json_parse->password)) && (isset($_SESSION['nonce']))){
		$i = 0;
		$match = 1;
		$dbhandle = new SQLite3('db_school.db');
		$results = $dbhandle->query("SELECT * FROM users");
		if($results){
                        while ($row = $results->fetchArray()){
				$server_password = hash_hmac ( "sha1", $row['password'] , $_SESSION['nonce']);
				if(($server_password == $json_parse->password) && ($row['enabled'] == 'true')){
					$_SESSION['auth'] = "true";
					$_SESSION['userID'] = $row['id'];
					$_SESSION['userName'] = $row['name'];
					echo json_encode(array('password' => 'ok', 'auth' => $_SESSION['auth']));
					$i = 1;
					break;
				}			
			}
		}
		if($i == 0){
			echo json_encode(array('password' => 'error'));
		}
		$dbhandle->close();
	}
	if((isset($json_parse->logout)) && (isset($_SESSION['auth']))){
		if($json_parse->logout == 'true'){
			$match = 1;
			session_destroy();
			echo json_encode(array('restart' => 'true'));
		}
	}
	if((isset($json_parse->rpistatus)) && (isset($_SESSION['auth']))){
		if(($json_parse->rpistatus == 'true') && ($_SESSION['auth'] == 'true')){
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
			$rpi_uptime = $days . $paev . " ja " . $hours . "tundi"; 
			echo json_encode(array('rpiDate' => $rpi_date, 'rpiFreeMemory' => $rpi_freeMemory, 'rpiUptime' => $rpi_uptime, 'rpiLoad' => $rpi_load));
		}
	}
	if((isset($json_parse->getFiles)) && ($_SESSION['auth'] == 'true')){
		if($json_parse->getFiles == 'true'){
			if ($handle = opendir('../files/')){
				$match = 1;
				$allFiles = [];
				$i = 0;
			    	while (false !== ($entry = readdir($handle))) {
					if ($entry != "." && $entry != "..") {
					    	$allFiles[$i]['filename'] = $entry;
						$i++;
					}
			    	}
				echo json_encode(array('allFiles' => $allFiles));
			}
    			closedir($handle);
		}
	}
	if((isset($json_parse->deleteFile)) && ($_SESSION['auth'] == 'true')){
		if($json_parse->deleteFile->filename){
			$match = 1;
			$check = unlink("../files/" . $json_parse->deleteFile->filename);
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

Function RandomNumber(){
	$characters = "0123456789";
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

