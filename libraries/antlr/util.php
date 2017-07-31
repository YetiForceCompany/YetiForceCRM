<?php

function strToIntArray($string)
{
	$arr = [];
	for ($i = 0, $n = strlen($string); $i < $n; $i++) {
		$arr[] = ord(substr($string, $i, 1));
	}
	return $arr;
}

function charAt($str, $i)
{
	return ord(substr($str, $i, 1));
}
