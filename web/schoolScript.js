var nonce;
var wrong_password = "Vale kasutajanimi või salasõna";
var usersAll;
var status_interval = null;

var webpage = new Object();
webpage.main;
webpage.left_header;
webpage.login;
webpage.status_page;
webpage.users;
webpage.getfile;
webpage.rules_page;
webpage.timeprofiles_page;
webpage.timeprofiles_sub_page;

var webstatic = new Object();
webstatic.getTimeprofiles;
webstatic.getRules;
webstatic.getFiles;


// Get all static files
function getFileId(name, nr, run_init){
	var xmlhttp;
	if(window.XMLHttpRequest){// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp=new XMLHttpRequest();
	}else{// code for IE6, IE5
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	xmlhttp.onreadystatechange=function(){
		if (xmlhttp.readyState==4 && xmlhttp.status==200){
			if(nr == 1){webpage.main = xmlhttp.responseText;}
			if(nr == 2){webpage.left_header = xmlhttp.responseText;}
			if(nr == 3){webpage.login = xmlhttp.responseText;}
			if(nr == 4){webpage.status_page = xmlhttp.responseText;}
			if(nr == 5){webpage.users = xmlhttp.responseText;}
			if(nr == 6){webpage.getfile = xmlhttp.responseText;}
			if(nr == 7){webpage.rules_page = xmlhttp.responseText;}
			if(nr == 8){webpage.timeprofiles_page = xmlhttp.responseText;}
			if(run_init){
				login_page();	
			}
		}
	}
	xmlhttp.open("GET", name, true);
	xmlhttp.send();
}

function parse(json_response){
	if(json_response.nonce){
		nonce = json_response.nonce;
	}
	if((json_response.password != "ok") && (json_response.password)){
		login_status(wrong_password);
	}
	if(json_response.auth != "true" && (json_response.auth)){
	}
	if(json_response.auth == "true" && (json_response.auth)){
		main_page();
	}
	if(json_response.rpiFreeMemory){
		var check = document.getElementById("freeMemory");
		if(check){
			document.getElementById("freeMemory").innerHTML = json_response.rpiFreeMemory;
		}
	}
	if(json_response.rpiUptime){
		var check = document.getElementById("rpiUptime");
		if(check){
			document.getElementById("rpiUptime").innerHTML = json_response.rpiUptime;
		}
	}
	if(json_response.allUsers){
		users_update(json_response);
	}
	if(json_response.rpiDate){
		var check = document.getElementById("rpiDate");
		if(check){
			document.getElementById("rpiDate").innerHTML = json_response.rpiDate;
		}
	}
	if(json_response.rpiLoad){
		var check = document.getElementById("rpiLoad");
		if(check){
			document.getElementById("rpiLoad").innerHTML = json_response.rpiLoad;
		}
	}
	if(json_response.deleteUser == "true"){
		users_page();
	}
	if(json_response.updateUser == "true"){
		edit_close();
	}
	if(json_response.createUser == "true"){
		edit_close();
	}
	if(json_response.restart == "true"){
		login_page();
	}
	if(json_response.deleteFile == "true"){
		get_file();
	}else if(json_response.deleteFile == "false"){
		get_file();
	}
	if(json_response.updatedToggleEnableRule == "true"){
		status_page();
	}
	if(json_response.allFiles){
		load_files(json_response);
	}
	if(json_response.allRules){
		status_show_rules(json_response);
	}
	if(json_response.updateRule == "true"){
		rules_updated();
	}
	if(json_response.deleteRule == "true"){
		rules_updated();
	}
	if(json_response.createRule == "true"){
		rules_updated();
	}
	if(json_response.Rules){
		rules_show_rules(json_response);
	}
	if(json_response.timeprofiles){
		parse_timeprofiles(json_response);
	}
}

/*******************************	Load all statics files	**************************************/
function main_init(){
	getFileId("main.html", 1, null);
	getFileId("left_header.html", 2, null);
	getFileId("login.html", 3, null);
	getFileId("status_page.html", 4, null);
	getFileId("users.html", 5, null);
	getFileId("getfile.html", 6, null);
	getFileId("rulepage.html", 7, null);
	getFileId("timeprofile.html", 8, "start");
}


/*******************************	login page	**********************************************/
function login_status(i){
	document.getElementById("feedback").innerHTML = i;
}

function login_page(){
	if(status_interval){
		clearInterval(status_interval);
	}
	document.getElementById("body").innerHTML=webpage.login;
	status_interval = setInterval(function(){get_rpistatus()},10000);
	var getStatus = {};
	getStatus.connection = "getStatus";
	var get_status = new sendDataToServer();        
	get_status.sendPostToServer(getStatus);
}


function login_submit(){
	var login = {};
	var tempPassword;
	var tempPassword1;
	var name;
	
	name =  document.getElementById("loginUserName").value;
	tempPassword =  document.getElementById("loginPassword").value;
	tempPassword1 = "" + CryptoJS.SHA1(name + tempPassword);
	login.password = "" + CryptoJS.HmacSHA1(tempPassword1, nonce);
	var log_in = new sendDataToServer();        
        log_in.sendPostToServer(login);
}

/*	logout	*/

function log_out(){
	var logout = {};
	logout.logout = "true";
	var log_out = new sendDataToServer();        
        log_out.sendPostToServer(logout);
}

/*********************************	Main page	**************************************************/

function main_page(){
	document.getElementById("body").innerHTML=webpage.main;
	document.getElementById("left_header").innerHTML=webpage.left_header;
	status_page();	
}

/******************************	*	user page	*******************************************8*/
function users_update(json_response){
	var tmp_string = "";
	var check = document.getElementById("loadUser");
	if(check){
		usersAll = json_response.allUsers;
		tmp_string = "<hr><table id='userTable' border='0'><tr>" + 
			"<td>Lubatud</td>" +
			"<td>Nimi</td>" +
			"<td>Kasutajanimi</td>" +
			"</td></tr>";
		for(var i = 0; i < json_response.allUsers.length; i++){
			if(json_response.allUsers[i].enabled == "true"){
				var j = "Jah";
			}else{
				var j = "Ei"
			}
			tmp_string += 
				"<tr><td>" + j + 
				"</td><td>" + json_response.allUsers[i].name + 
				"</td><td>"+ json_response.allUsers[i].username +
				"</td><td>" +
				"<input type='button' onclick='edit_user(" + json_response.allUsers[i].id + ")' value='Muuda' />" + 
				"<input type='button' onclick='delete_user(" + json_response.allUsers[i].id + ")' value='Kustuta' />" + 
				"</td></tr>";

		}
		tmp_string += "</td></tr>" + "<tr><td>"+ "</td><td>" + "</td><td>" + "</td><td>" +
		"<input type='button' onclick='create_user()' value='Loo uus' />";
		tmp_string += "</table><hr>";
		document.getElementById("loadUser").innerHTML = tmp_string;
	}
}

function edit_user(userID){
	var temp = 0;
	var enable_check;
	document.getElementById('loadUser').innerHTML = "";
	for(var i = 0; i < usersAll.length; i++){
		if(usersAll[i].id == userID){
			if(usersAll[i].enabled == 'true'){
				enable_check = 'checked';
			}else{
				enable_check = "";
			}
			temp = 1;
			tmp_string = "<hr><table id='userTable' border='0'><tr>" +
                                        "<td>Lubatud</td>" +
                                        "<td>Nimi</td>" +
                                        "<td>Kasutajanimi</td>" +
                                        "<td>Parool</td>" +
                                        "<td>Parool uuesti</td>" +
                                        "</td></tr>" +
					"<td><input id='editEnabled' type='checkbox'" + enable_check +"/></td>" +
					"<td><input id='editName' type='text' value='" + usersAll[i].name  + "'/></td>" +
					"<td><input id='editUsername' value='" + usersAll[i].username + "' type='text'/></td>" +
					"<td><input id='editPassword1'  type='password'/></td>" +
					"<td><input id='editPassword2' type='password'/></td>" +
					"</td></tr>" +
					"</table><hr>" +
					"<input type='button' value='Salvesta' onclick='user_save(" + usersAll[i].id + ")'/>" +
					"<input type='button' value='Sulge' onclick='edit_close()'/>";
					document.getElementById('editUser').innerHTML = tmp_string;
		}
	}
	if(temp == 0){
		document.getElementById('editUser').innerHTML = "";
		users_page();
	}
}

function create_user(){
	var temp = 0;
	var enable_check;
	document.getElementById('loadUser').innerHTML = "";
	tmp_string = "<hr><table id='userTable' border='0'><tr>" +
			"<td>Lubatud</td>" +
			"<td>Nimi</td>" +
			"<td>Kasutajanimi</td>" +
			"<td>Parool</td>" +
			"<td>Parool uuesti</td>" +
			"</td></tr>" +
			"<td><input id='createEnabled' type='checkbox' checked/></td>" +
			"<td><input id='createName' type='text'/></td>" +
			"<td><input id='createUsername'  type='text'/></td>" +
			"<td><input id='createPassword1'  type='password'/></td>" +
			"<td><input id='createPassword2' type='password'/></td>" +
			"</td></tr>" +
			"</table><hr>" +
			"<input type='button' value='Salvesta' onclick='create_save()'/>" +
			"<input type='button' value='Sulge' onclick='edit_close()'/>";
			document.getElementById('editUser').innerHTML = tmp_string;
}

function delete_user(userID){
	var deleteUser = {};
	var temp;
	deleteUser.deleteUser = {};
	deleteUser.deleteUser.id = userID;
	for(var i = 0; i < usersAll.length; i++){
		if(usersAll[i].id == userID){
			temp = window.confirm("Kas olete kindel et soovite kustutada kasutaja \n" + usersAll[i].name);
			if(temp){
				var delete_user = new sendDataToServer();        
			        delete_user.sendPostToServer(deleteUser);
			}else{

			}
		}
	}
}

function user_save(userID){
	if(document.getElementById('editPassword1').value.length < 6){
		window.alert("Salasõna peab olema vähemalt 6 tähemärki pikk");
	}else if(document.getElementById('editPassword1').value == document.getElementById('editPassword2').value){
		if(document.getElementById('editUsername').value.length > 3){
			var updateUser = {};
			var gen_pass;
			updateUser.updateUser = {};
			updateUser.updateUser.id = userID;
			gen_pass = ""  + document.getElementById('editUsername').value + document.getElementById('editPassword1').value;
			updateUser.updateUser.password = "" + CryptoJS.SHA1(gen_pass);
			updateUser.updateUser.username = document.getElementById('editUsername').value;
			updateUser.updateUser.name = document.getElementById('editName').value;
			updateUser.updateUser.enabled = document.getElementById('editEnabled').checked;
			var update_user = new sendDataToServer();        
		        update_user.sendPostToServer(updateUser);

		}else{
			window.alert("Kasutajanimi peab olema vähemalt 4 tähemärki pikk");
		}
	}else{
		window.alert("Paroolid ei kattu");
	}
}

function create_save(){
	if(document.getElementById('createPassword1').value.length < 6){
		window.alert("Salasõna peab olema vähemalt 6 tähemärki pikk");
	}else if(document.getElementById('createPassword1').value == document.getElementById('createPassword2').value){
		if(document.getElementById('createUsername').value.length > 3){
			var createUsers = {};
			var gen_pass;
			createUsers.createUser = {};
			gen_pass = ""  + document.getElementById('createUsername').value + document.getElementById('createPassword1').value;
			createUsers.createUser.password = "" + CryptoJS.SHA1(gen_pass);
			createUsers.createUser.username = document.getElementById('createUsername').value;
			createUsers.createUser.name = document.getElementById('createName').value;
			createUsers.createUser.enabled = document.getElementById('createEnabled').checked;
			var create_users = new sendDataToServer(); 	
			create_users.sendPostToServer(createUsers);
		}else{
			window.alert("Kasutajanimi peab olema vähemalt 4 tähemärki pikk");
		}
	}else{
		window.alert("Paroolid ei kattu");
	}
}

function edit_close(){
	document.getElementById('editUser').innerHTML = "";
	users_page();
}

function users_page(){
	document.getElementById("right_header").innerHTML=webpage.users;
	var getUsers = {};
	getUsers.getUsers = 'true';
	var get_users = new sendDataToServer(); 	
	get_users.sendPostToServer(getUsers);
}




/***********************************	       Status page             *********************************************/
function get_rpistatus(){
	var getStatus = {};
	getStatus.rpistatus = 'true';
	var rpi_status = new sendDataToServer(); 	
	rpi_status.sendPostToServer(getStatus);
}

function set_loop(){
	setInterval(function(){get_rpistatus()},10000);
}

function status_page(){
	document.getElementById("right_header").innerHTML=webpage.status_page;
	var getRules = {};
        getRules.getRules = 'true';
	var get_rules = new sendDataToServer(); 	
	get_rules.sendPostToServer(getRules);
	get_rpistatus();
}

function status_show_rules(json_response){
	var check = document.getElementById("ruleStatus");
	if(check){
		//var rulesAll = json_response.allUsers;
		var tmp_string = "<table id='rulesTable' border='0'><tr>" + 
			"<td>Lubatud</td>" +
			"<td>Reegili nimi</td>" +
			"</td></tr>";
		for(var i = 0; i < json_response.allRules.length; i++){
			if(json_response.allRules[i].enabled == "true"){
				var j = "Jah";
			}else{
				var j = "Ei"
			}
			tmp_string += 
				"<tr><td>" + j + 
				"</td><td>" + json_response.allRules[i].name + 
				"</td><td>" +
				"<input type='button' onclick='status_rule_active(" + json_response.allRules[i].id + ")' value='Muuda' />" + 
				"</td></tr>";
		}
		tmp_string += "</table><hr>";
		document.getElementById("ruleStatus").innerHTML = tmp_string;
	}
}

function status_rule_active(id){
	var toggleEnableRule = {};
	toggleEnableRule.toggleEnableRule = {};
	toggleEnableRule.toggleEnableRule.id = {};
	toggleEnableRule.toggleEnableRule.id = id;
	var toggle_EnableRule = new sendDataToServer();
	toggle_EnableRule.sendPostToServer(toggleEnableRule);
}

/**********************************	Rules	******************************************/
function rules_page(){
	document.getElementById("right_header").innerHTML=webpage.rules_page;
	var queryTimeprofile = {};
        queryTimeprofile.queryTimeprofile = 'true';
	var query_Timeprofile = new sendDataToServer(); 	
	query_Timeprofile.sendPostToServer(queryTimeprofile);
	var queryRules = {};
        queryRules.queryRules = 'true';
	var query_rules = new sendDataToServer(); 	
	query_rules.sendPostToServer(queryRules);
	var getFiles = {};
	getFiles.getFiles = 'true';
	var get_f = new sendDataToServer(); 	
	get_f.sendPostToServer(getFiles);

}

function rules_show_rules(json_response){
	var check = document.getElementById("loadRules");
	if(check){
		webstatic.getRules = json_response.Rules;
		var tmp_string = "<hr><table id='rulesTable' border='0'><tr>" + 
			"<td>Nimi</td>" +
			"<td>Ajaprofiil</td>" +
			"<td>Track</td>" +
			"</td></tr>";
		for(var i = 0; i < json_response.Rules.length; i++){
			tmp_string += 
				"<tr><td>" + json_response.Rules[i].name + "</td>" + 
				"<td>" + json_response.Rules[i].timeprofileName + "</td>" +
				"<td>" + json_response.Rules[i].track + "</td>" +
				"<td>" + "<input type='button' onclick='rule_edit(" + json_response.Rules[i].id + ")' value='Muuda' /></td>" + 
				"<td>" + "<input type='button' onclick='rule_delete(" + json_response.Rules[i].id + ")' value='Kustuta' />" + 
				"</td></tr>";
		}
		tmp_string += "</table><hr>" + 
				"<table id='createRulesTable' border='0'><tr>" +
				"<td>" + "<input type='button' onclick='rule_create_new()' value='Loo uus' />" + 
				"</td></tr>";
		document.getElementById("loadRules").innerHTML = tmp_string;
	}
}

function rule_edit(ruleID){
	var enable_check;
	var tmp_string;
	document.getElementById('loadRules').innerHTML = "";
	for(var i = 0; i < webstatic.getRules.length; i++){
		if(webstatic.getRules[i].id == ruleID){
			tmp_string = "<hr><table id='userTable' border='0'><tr>" +
                                        "<td>Nimi</td>" +
                                        "<td>Ajaprofiil</td>" +
                                        "<td>Track</td>" +
                                        "</td></tr>" +
					"<td><input id='ruleName' type='text' value='" + webstatic.getRules[i].name  + "'/></td>" +
					"<td> <select id=ruleTimeprofileID>";
			var temp_id = new Array();
			var temp_true = 1;
			for(var j = 0; j < webstatic.getTimeprofiles.length; j++){
				for(var k = 0; k < temp_id.length; k++){
					temp_true = 1;
					if (temp_id[k] == webstatic.getTimeprofiles[j].timeprofileID){
						temp_true = 0;
						break;
					}
				}
				if(temp_true){	
					tmp_string += "<option value='" + webstatic.getTimeprofiles[j].timeprofileID  + "'>" + webstatic.getTimeprofiles[j].name  + "</option>";
					temp_id.push(webstatic.getTimeprofiles[j].timeprofileID);
				}
				temp_true = 0;
			}
			tmp_string += "</select></td><td> <select id=ruleTrack>";
			for(var j = 0; j < webstatic.getFiles.allFiles.length; j++){
				tmp_string += "<option value='" + webstatic.getFiles.allFiles[j].filename  + "'>" + webstatic.getFiles.allFiles[j].filename  + "</option>";
			}
			tmp_string +=	"</select></td></tr>" +
					"</table><hr>" +
					"<input type='button' value='Salvesta' onclick='rule_save(" + webstatic.getRules[i].id + ")'/>" +
					"<input type='button' value='Sulge' onclick='rules_page()'/>";
					document.getElementById('loadRules').innerHTML = tmp_string;
		}
	}
}

function rule_create_new(){
	var enable_check;
	var tmp_string;
	document.getElementById('loadRules').innerHTML = "";
	tmp_string = "<hr><table id='userTable' border='0'><tr>" +
			"<td>Nimi</td>" +
			"<td>Ajaprofiil</td>" +
			"<td>Track</td>" +
			"</td></tr>" +
			"<td><input id='ruleName' type='text' value=''/></td>" +
			"<td> <select id=ruleTimeprofileID>";
	var temp_id = new Array();
	var temp_true = 1;
	for(var j = 0; j < webstatic.getTimeprofiles.length; j++){
		for(var k = 0; k < temp_id.length; k++){
			temp_true = 1;
			if (temp_id[k] == webstatic.getTimeprofiles[j].timeprofileID){
				temp_true = 0;
				break;
			}
		}
		if(temp_true){	
			tmp_string += "<option value='" + webstatic.getTimeprofiles[j].timeprofileID  + "'>" + webstatic.getTimeprofiles[j].name  + "</option>";
			temp_id.push(webstatic.getTimeprofiles[j].timeprofileID);
		}
		temp_true = 0;
	}
	tmp_string += "</select></td><td> <select id=ruleTrack>";
	for(var j = 0; j < webstatic.getFiles.allFiles.length; j++){
		tmp_string += "<option value='" + webstatic.getFiles.allFiles[j].filename  + "'>" + webstatic.getFiles.allFiles[j].filename  + "</option>";
	}
	tmp_string +=	"</select></td></tr>" +
			"</table><hr>" +
			"<input type='button' value='Salvesta' onclick='rule_create_save()'/>" +
			"<input type='button' value='Sulge' onclick='rules_page()'/>";
	document.getElementById('loadRules').innerHTML = tmp_string;
}

function rule_create_save(){
	var rule_create_response = {};
	rule_create_response.createRule = {};
	if(document.getElementById('ruleName').value.length < 1){
		window.alert("Reegli nimi sisestamata");
	}else{
		rule_create_response.createRule.name = document.getElementById('ruleName').value;
		if(document.getElementById('ruleTimeprofileID').value == null){
			window.alert("Ajaprofiil sisestamata");
		}else{
			rule_create_response.createRule.timeprofileID = document.getElementById('ruleTimeprofileID').value;
			if(document.getElementById('ruleTrack').value == null){
				window.alert("Track valimata");
			}else{
				rule_create_response.createRule.track = document.getElementById('ruleTrack').value;
				document.getElementById('loadRules').innerHTML = "Loading";
				var ruleCreateResponse = new sendDataToServer(); 	
				ruleCreateResponse.sendPostToServer(rule_create_response);
			}
			
		}
		
	}
}


function rule_delete(ruleID){
	var temp;
	var rule_response = {};
	rule_response.deleteRule = {};
	rule_response.deleteRule.id = ruleID;
	temp = window.confirm("Kas olete kindel et soovite kustutada reegli \n");
	if(temp){
		var ruleDelete = new sendDataToServer(); 	
		ruleDelete.sendPostToServer(rule_response);
	}
}

function rule_save(ruleID){
	var rule_response = {};
	rule_response.updateRule = {};
	if(document.getElementById('ruleName').value.length < 1){
		window.alert("Reegli nimi sisestamata");
	}else{
		rule_response.updateRule.name = document.getElementById('ruleName').value;
		if(document.getElementById('ruleTimeprofileID').value == null){
			window.alert("Ajaprofiil sisestamata");
		}else{
			rule_response.updateRule.timeprofileID = document.getElementById('ruleTimeprofileID').value;
			if(document.getElementById('ruleTrack').value == null){
				window.alert("Track valimata");
			}else{
				rule_response.updateRule.track = document.getElementById('ruleTrack').value;
				rule_response.updateRule.id = ruleID;
				var ruleResponse = new sendDataToServer(); 	
				ruleResponse.sendPostToServer(rule_response);
			}
			
		}
		
	}
}
function rules_updated(){
	rules_page();
}
/*********************************	Timeprofiles	********************************/
function parse_timeprofiles(json_response){
	webstatic.getTimeprofiles = json_response.timeprofiles;
}

function timeprofiles_page(){
	document.getElementById("right_header").innerHTML=webpage.timeprofiles_page;
	var Timeprofile = {};
        Timeprofile.queryTimeprofile = 'true';
	var queryTimeprofile = new sendDataToServer(); 	
	queryTimeprofile.sendPostToServer(Timeprofile);
	show_timeprofile();
}

function show_timeprofile(){
	var check = document.getElementById("loadTimeprofile");
	if(check){
		var tmp_string = "<hr><table id='timeprofileTable' border='0'><tr>" + 
		"<td>Nimi</td>" +
		"</td></tr>";
		var temp_id = new Array();
		var temp_true = 1;
		for(var j = 0; j < webstatic.getTimeprofiles.length; j++){
			for(var k = 0; k < temp_id.length; k++){
				temp_true = 1;
				if (temp_id[k] == webstatic.getTimeprofiles[j].timeprofileID){
					temp_true = 0;
					break;
				}
			}
			if(temp_true){	
				tmp_string += "<tr><td>" + webstatic.getTimeprofiles[j].name  + "</td>" +
						"<td>" + "<input type='button' onclick='timeprofile_edit(" + webstatic.getTimeprofiles[j].timeprofileID + ")' value='Muuda' />" +
						"</td></tr>";
				temp_id.push(webstatic.getTimeprofiles[j].timeprofileID);
			}
			temp_true = 0;
		}
		tmp_string += "</table><hr>" + 
				"<table id='createTimeprofile' border='0'><tr>" +
				"<td>" + "<input type='button' onclick='timeprofile_create_new()' value='Loo uus' />" + 
				"</td></tr>";
		document.getElementById("loadTimeprofile").innerHTML = tmp_string;
	}
}

function timeprofile_edit(timeprofileID){
	var tmp_string;
	var j = 0;
	tmp_string = "";
	document.getElementById('loadTimeprofile').innerHTML = "";
	for(var i = 0; i < webstatic.getTimeprofiles.length; i++){
		if(webstatic.getTimeprofiles[i].timeprofileID == timeprofileID){
			tmp_string += "<hr><FORM NAME='boxes'>" +
                                        "<input id='" + j + "_name' type='text' value='" + webstatic.getTimeprofiles[i].name  + "'/>";
			break;
		}
	}
	webstatic.timeprofiles_sub_page = [];
	tmp_string += "<hr><table id='userTable' border='0'><tbody id='tp_val'>";
	for(i = 0; i < webstatic.getTimeprofiles.length; i++){
		if(webstatic.getTimeprofiles[i].timeprofileID == timeprofileID){
			webstatic.timeprofiles_sub_page[j] = "<tr>";
			webstatic.timeprofiles_sub_page[j] += "<td><input type='checkbox' value='mon' name='tp_values'" + ((webstatic.getTimeprofiles[i].mon == 'true') ? 'checked' : '')  +"/>E</td>" ;
			webstatic.timeprofiles_sub_page[j] += "<td><input type='checkbox' value='tue' name='tp_values'" + ((webstatic.getTimeprofiles[i].tue == 'true') ? 'checked' : '')  +"/>T</td>" ;
			webstatic.timeprofiles_sub_page[j] += "<td><input type='checkbox' value='wed' name='tp_values'" + ((webstatic.getTimeprofiles[i].wed == 'true') ? 'checked' : '')  +"/>K</td>" ;
			webstatic.timeprofiles_sub_page[j] += "<td><input type='checkbox' value='thu' name='tp_values'" + ((webstatic.getTimeprofiles[i].thu == 'true') ? 'checked' : '')  +"/>N</td>" ;
			webstatic.timeprofiles_sub_page[j] += "<td><input type='checkbox' value='fri' name='tp_values'" + ((webstatic.getTimeprofiles[i].fri == 'true') ? 'checked' : '')  +"/>R</td>" ;
			webstatic.timeprofiles_sub_page[j] += "<td><input type='checkbox' value='sat' name='tp_values'" + ((webstatic.getTimeprofiles[i].sat == 'true') ? 'checked' : '')  +"/>L</td>" ;
			webstatic.timeprofiles_sub_page[j] += "<td><input type='checkbox' value='sun' name='tp_values'" + ((webstatic.getTimeprofiles[i].sun == 'true') ? 'checked' : '')  +"/>P</td>" ;
			webstatic.timeprofiles_sub_page[j] += "<td><input type='button' value='Kustuta' onclick='timeprofile_delete_row(" + j + ")'/></td></tr>";
			j++;
		}
	}
	for(var i = 0; i < webstatic.timeprofiles_sub_page.length; i++){
		tmp_string += webstatic.timeprofiles_sub_page[i];
	}
	tmp_string += "</tr></tbody></table><hr>" +
			"<p><input type='button' value='Lisa rida' onclick='timeprofile_add_row()'/></p>" + 
			"<p><input type='button' value='Salvesta' onclick='timeprofile_save()'/>" +
			"<input type='button' value='Sulge' onclick='timeprofile_close()'/></p></form>";

	document.getElementById("loadTimeprofile").innerHTML = tmp_string;
}

function timeprofile_delete_row(id){
	var tmp_string ="";
	webstatic.timeprofiles_sub_page[id] = "";
	document.getElementById("tp_val").innerHTML = "";
	tmp_string += "<table id='userTable' border='0'>";
	for(var i = 0; i < webstatic.timeprofiles_sub_page.length; i++){
                tmp_string += webstatic.timeprofiles_sub_page[i];
        }
	tmp_string += "</tr>";
	document.getElementById("tp_val").innerHTML = tmp_string;
}

function timeprofile_add_row(id){
	var i;
	var tmp_string ="";
	i = webstatic.timeprofiles_sub_page.length;
	tmp_string = "<tr>";
	tmp_string += "<td><input type='checkbox' value='mon' name='tp_values'/>E</td>";
	tmp_string += "<td><input type='checkbox' value='tue' name='tp_values'/>T</td>";
	tmp_string += "<td><input type='checkbox' value='wed' name='tp_values'/>K</td>";
	tmp_string += "<td><input type='checkbox' value='thu' name='tp_values'/>N</td>";
	tmp_string += "<td><input type='checkbox' value='fri' name='tp_values'/>R</td>";
	tmp_string += "<td><input type='checkbox' value='sat' name='tp_values'/>L</td>";
	tmp_string += "<td><input type='checkbox' value='sun' name='tp_values'/>P</td>";
	tmp_string += "<td><input type='button' value='Kustuta' onclick='timeprofile_delete_row(" + i + ")'/></td></tr>";
	webstatic.timeprofiles_sub_page[i] = tmp_string;
	document.getElementById("tp_val").innerHTML = "";
	tmp_string = "";
	tmp_string += "<table id='userTable' border='0'>";
	for(var i = 0; i < webstatic.timeprofiles_sub_page.length; i++){
                tmp_string += webstatic.timeprofiles_sub_page[i];
        }
	document.getElementById("tp_val").innerHTML = tmp_string;
}

function timeprofile_close(){
	timeprofiles_page();
}

/***********************************	Files	******************************************/
function get_file(){
	document.getElementById("right_header").innerHTML=webpage.getfile;
	var getFiles = {};
	getFiles.getFiles = 'true';
	var get_f = new sendDataToServer(); 	
	get_f.sendPostToServer(getFiles);
}

function load_files(json_response){
	var check = document.getElementById("loadFile");
	webstatic.getFiles = json_response;
	if(check){
		document.getElementById("loadFile").innerHTML = "Loading...";
		var tmp_string = "<table id='track_files' border='0'><hr><tr>" +
                        "<td>Nimi</td>" +
                        "</td></tr>";
		for(var i = 0; i < json_response.allFiles.length; i++){
			tmp_string += 
				"</td><td>" + json_response.allFiles[i].filename + 
				"</td><td>" +
				"<input type='button' onclick='delete_track_file(\"" + json_response.allFiles[i].filename + "\");' value='Kustuta' />" + 
				"</td></tr>";
		}
		tmp_string += "</table><hr>";
		document.getElementById("loadFile").innerHTML = tmp_string;
	}
}
function delete_track_file(name){
	var deleteFile = {};
	var temp;
	deleteFile.deleteFile = {};
	deleteFile.deleteFile.filename = name;
	temp = window.confirm("Kas olete kindel et soovite kustutada faili \n" + name + "!");
	if(temp){
		document.getElementById("loadFile").innerHTML="Loading..";
		var delete_file = new sendDataToServer();        
		delete_file.sendPostToServer(deleteFile);
	}else{
		
	}
}

function fileUpload(form, action_url, div_id) {
	// Create the iframe...
	var iframe = document.createElement("iframe");
	iframe.setAttribute("id", "upload_iframe");
	iframe.setAttribute("name", "upload_iframe");
	iframe.setAttribute("width", "0");
	iframe.setAttribute("height", "0");
	iframe.setAttribute("border", "0");
	iframe.setAttribute("style", "width: 0; height: 0; border: none;");

	// Add to document...
	form.parentNode.appendChild(iframe);
	window.frames['upload_iframe'].name = "upload_iframe";

	iframeId = document.getElementById("upload_iframe");

	// Add event...
	var eventHandler = function () {

		if (iframeId.detachEvent) iframeId.detachEvent("onload", eventHandler);
		else iframeId.removeEventListener("load", eventHandler, false);

		// Message from server...
		if (iframeId.contentDocument) {
			content = iframeId.contentDocument.body.innerHTML;
		} else if (iframeId.contentWindow) {
			content = iframeId.contentWindow.document.body.innerHTML;
		} else if (iframeId.document) {
			content = iframeId.document.body.innerHTML;
		}

		document.getElementById(div_id).innerHTML = content;
		get_file();

		// Del the iframe...
		setTimeout('iframeId.parentNode.removeChild(iframeId)', 250);
	}

	if (iframeId.addEventListener) iframeId.addEventListener("load", eventHandler, true);
	if (iframeId.attachEvent) iframeId.attachEvent("onload", eventHandler);

	// Set properties of form...
	form.setAttribute("target", "upload_iframe");
	form.setAttribute("action", action_url);
	form.setAttribute("method", "post");
	form.setAttribute("enctype", "multipart/form-data");
	form.setAttribute("encoding", "multipart/form-data");

	// Submit the form...
	form.submit();

	document.getElementById(div_id).innerHTML = "Uploading...";
}

//***********************************************************************************************
function sendDataToServer(){
	this.sendPostToServer = function(obj){
		var xmlhttp;
		var jsonString = "jsonString=" + JSON.stringify(obj);
		if (window.XMLHttpRequest){// code for IE7+, Firefox, Chrome, Opera, Safari
                	xmlhttp=new XMLHttpRequest();
		}else{// code for IE6, IE5
			xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
		}
		xmlhttp.open("POST","server.php", false);
		xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
		/*xmlhttp.onreadystatechange=function(){
			if (xmlhttp.readyState==4 && xmlhttp.status==200){
				var response = xmlhttp.responseText;
				parse(JSON.parse(response));        
			}
		}*/
		xmlhttp.send(jsonString);
		if (xmlhttp.status==200){
			var response = xmlhttp.responseText;
			parse(JSON.parse(response));        
		}
	}		
}

