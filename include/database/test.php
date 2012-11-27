<?php

$string = "CREATE TABLE `vtiger_mailmanager_mailrecord` (
  `userid` int(11) default NULL,
  `mfrom` varchar(255) default NULL,
  `mto` varchar(255) default NULL,
  `mcc` varchar(500) default NULL,
  `mbcc` varchar(500) default NULL,
  `mdate` varchar(20) default NULL,
  `msubject` varchar(500) default NULL,
  `mbody` text,
  `mcharset` varchar(10) default NULL,
  `misbodyhtml` int(1) default NULL,
  `mplainmessage` int(1) default NULL,
  `mhtmlmessage` int(1) default NULL,
  `muniqueid` varchar(500) default NULL,
  `mbodyparsed` int(1) default NULL,
  `muid` int(11) default NULL,
  `lastsavedtime` int(11) default NULL,
  KEY `userid_lastsavedtime_idx` (`userid`,`lastsavedtime`),
  KEY `userid_muid_idx` (`userid`,`muid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

$string2 = "CREATE TABLE `vtiger_fieldformulas` (
  `expressionid` int(11) NOT NULL default '0',
  `modulename` varchar(100) default NULL,
  `expression_engine` text,
  PRIMARY KEY  (`expressionid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

$string3="CREATE TABLE `vtiger_webforms` (
  `id` int(19) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `publicid` varchar(100) NOT NULL,
  `enabled` int(1) NOT NULL DEFAULT '1',
  `targetmodule` varchar(50) NOT NULL,
  `description` varchar(250) DEFAULT NULL,
  `ownerid` int(19) NOT NULL,
  `returnurl` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `webformname` (`name`),
  UNIQUE KEY `publicid` (`id`),
  KEY `webforms_webforms_id_idx` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

$string4="CREATE TABLE `vtiger_webforms_field` (
  `id` int(19) NOT NULL AUTO_INCREMENT,
  `webformid` int(19) NOT NULL,
  `fieldname` varchar(50) NOT NULL,
  `neutralizedfield` varchar(50) NOT NULL,
  `defaultvalue` varchar(200) DEFAULT NULL,
  `required` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `webforms_webforms_field_idx` (`id`),
  KEY `fk_1_vtiger_webforms_field` (`webformid`),
  KEY `fk_2_vtiger_webforms_field` (`fieldname`),
  CONSTRAINT `fk_1_vtiger_webforms_field` FOREIGN KEY (`webformid`) REFERENCES `vtiger_webforms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_3_vtiger_webforms_field` FOREIGN KEY (`fieldname`) REFERENCES `vtiger_field` (`fieldname`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

$result_str = ConvertPsqlCreateQuery($string4);

echo "Result: $result_str \r\n";

	function ConvertPsqlCreateQuery($sqlquery) {
		
		$pg_query = strtoupper($sqlquery);
		if(preg_match('/(CREATE TABLE)/', $pg_query)) {
		
   			$pg_query = str_replace("`", "", $pg_query );
    		$pg_query = str_replace("INT(11) NOT NULL AUTO_INCREMENT", "SERIAL NOT NULL", $pg_query );
    		$pg_query = str_replace("INT(19) NOT NULL AUTO_INCREMENT", "SERIAL NOT NULL", $pg_query );
			$pg_query = str_replace("INT NOT NULL PRIMARY KEY AUTO_INCREMENT", "SERIAL NOT NULL PRIMARY KEY", $pg_query );
   			$pg_query = str_replace("INT(11)", "INTEGER", $pg_query );
   			$pg_query = str_replace("INT(10)", "INTEGER", $pg_query );
   			$pg_query = str_replace("INT(1)", "INTEGER", $pg_query );
   			$pg_query = str_replace("INT(19)", "INTEGER", $pg_query );
   			$pg_query = str_replace("DATETIME", "TIMESTAMP", $pg_query );
			//echo "HERE 0: $pg_query";
		
			// Remove everything after ENGINE
			$pos = strpos( $pg_query, "ENGINE=");
			if($pos !== false) {
				$pg_query = substr($pg_query, 0, $pos);
			}
			//echo "HERE 1: $pg_query";
			
			$tok = strtok($pg_query, ",");
			$result_query = "";
			$key = "";
			$isKeyTag = false;
			while ($tok !== false) {
				if(StringStartWith(trim($tok), "KEY")) {
					// To be enhanced later to handle index creation
    				$key = $tok;
					$pos = strpos( $tok, ")");
					if($pos == false) {
						$isKeyTag = true;
					}
				} else if(StringStartWith(trim($tok), "UNIQUE KEY")) {
					// Skip the unique index tag
					$pos = strpos( $tok, "(");
					if($pos !== false) {
						$tok = substr($tok, $pos+1);
					}
					$pos = strpos( $tok, ")");
					if($pos !== false) {
						$tok = substr($tok, 0, $pos);
					}
					
					$result_query .= ",UNIQUE(".$tok.")";
				} else if (strpos($tok, "FK_3_VTIGER_WEBFORMS_FIELD") !== false) {
					// skip
				} else if($isKeyTag) {
					$pos = strpos( $tok, ")");
					if($pos !== false) {
						$isKeyTag = false;						
					}
				} else {
					// Strip off "UNIQUE KEY"
					if(StringEndWith(trim($tok), "UNIQUE KEY")) {
						$pos = strpos( $tok, "UNIQUE KEY");
						if($pos !== false) {
							$tok = substr($tok, 0, $pos);
						}
					}
					
					if($result_query == "") {
						$result_query .= $tok;
					} else {
						$result_query .= ",".$tok;
					}
				}
   		 		$tok = strtok(",");
			}
			
			$open_brace_count = substr_count($result_query, '(');
			$close_brace_count = substr_count($result_query, ')');
			
			while($open_brace_count > $close_brace_count) {
				$result_query .= ")";
				$close_brace_count += 1;
			}
	
			return $result_query;
		} else {
			return $sqlquery;
		}
	}
	
	function stringStartWith($string, $prefix, $caseSensitive = false) {
		if(!$caseSensitive) {
			return stripos($string, $prefix, 0) === 0;
		}
		return strpos($string, $prefix, 0) === 0;
	}

	function StringEndWith($string, $postfix, $caseSensitive = false) {
		$expectedPostition = strlen($string) - strlen($postfix);
	
		if(!$caseSensitive) {
			return strripos($string, $postfix, 0) === $expectedPostition;
		}
		return strrpos($string, $postfix, 0) === $expectedPostition;
	}


?>
