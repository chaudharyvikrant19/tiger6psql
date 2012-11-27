<?php

$string = "2x7";


$result_str = doCalculation($string);

echo "Result: $result_str \r\n";

	function doCalculation($entityId) {
		if (strpos($entityId, "x") !== false || strpos($entityId, "X") !== false ) {
			$tmp = str_replace("x", "*", $entityId );
			eval("\$entityId = $tmp;");
		}
		
		return $entityId;
	}
	
?>
