<?php

function containsIdField($string) {
	
	if(startWith($string, 'id')) {
		return true;
	}
	
	if(endWith($string, 'id')) {
		return true;
	}
	
	return false;
}

function startsWith($string, $prefix, $caseSensitive = false) {
    if(!$caseSensitive) {
        return stripos($string, $prefix, 0) === 0;
    }
    return strpos($string, $prefix, 0) === 0;
}

function endsWith($string, $postfix, $caseSensitive = false) {
    $expectedPostition = strlen($string) - strlen($postfix);

    if(!$caseSensitive) {
        return strripos($string, $postfix, 0) === $expectedPostition;
    }
    return strrpos($string, $postfix, 0) === $expectedPostition;
}



?>