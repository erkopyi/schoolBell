<?php
if(isset($_POST['jsonString'])){
	$json_parse = json_decode($_POST['jsonString']);
	if(isset($json_parse->updateVer)){
		$rpi_pull = shell_exec("git pull");
		echo nl2br($rpi_pull);
	}else if(isset($json_parse->getLog)){
		$rpi_dmesg = shell_exec("dmesg | tail -n 25");
		echo nl2br($rpi_dmesg);
	}else if(isset($json_parse->pingInternet)){
		$rpi_dmesg = shell_exec("ping neti.ee -c 4");
		echo nl2br($rpi_dmesg);
	}

}
?>
