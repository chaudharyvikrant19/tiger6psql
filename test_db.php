<?php

	include('adodb/adodb.inc.php');
	$dbdriver = 'postgres';
	$server = 'localhost';
	$user = 'postgres';
	$password = 'post2010';
	$database = 'crm6rc';
	$sql = 'INSERT INTO vtiger_convertleadmapping(leadfid,accountfid,contactfid,potentialfid,editable) values(?,?,?,?,?)';
	$p1 = 43;
	$p2 = 1;
	$p3 = false;
	$p4 = 110;
	$p5 = 0;
	
	
	$params = array($p1, $p2, $p3, $p4, $p5 );
	$params = convert2Null($params);
	
	$db = ADONewConnection($dbdriver); # eg 'mysql' or 'postgres'
	
	$db->debug = true;
	
	$db->Connect($server, $user, $password, $database);
	
	$result = $db->Execute($sql, $params);
	

	function convert2Null($vals) {
		if(empty($vals)) {
			return $vals;
		}
		 
		for($index = 0; $index < count($vals); $index++) {
			if($vals[$index] === '') {
				$vals[$index] = null;
			} elseif ($vals[$index] === false) {
				$vals[$index] = null;
			}
		}
		 
		return $vals;
	}
	
?>