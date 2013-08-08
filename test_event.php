<?php

	include('adodb/adodb.inc.php');
	$dbdriver = 'postgres';
	$server = 'localhost';
	$user = 'postgres';
	$password = 'post2010';
	$database = 'crm6rc';
	$sql = 'insert into vtiger_eventhandlers (eventhandler_id, event_name, handler_path, handler_class, cond, is_active, dependent_on) values (?,?,?,?,?, 1, ?)';
	$p1 = 43;
	$p2 = 1;
	$p3 = false;
	$p4 = 110;
	$p5 = 0;
	
	
	$params = array(1, 'vtiger.entity.aftersave','modules/SalesOrder/RecurringInvoiceHandler.php', 'RecurringInvoiceHandler',null,'[]');
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