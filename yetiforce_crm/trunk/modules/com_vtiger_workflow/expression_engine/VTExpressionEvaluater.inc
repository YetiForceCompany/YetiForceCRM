<?php
/*+*******************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ******************************************************************************/
interface VTExpressionEnv{
	function get($var);
}

function __vt_add($arr){
	if(sizeof($arr)==1){
		return $arr[0];
	}else{
        if(strlen(substr(strrchr($arr[0], "."), 1))>strlen(substr(strrchr($arr[1], "."), 1))){
            $maxDigit = strlen(substr(strrchr($arr[0], "."), 1));
        } else {
            $maxDigit = strlen(substr(strrchr($arr[1], "."), 1));
        }
		return bcadd($arr[0], $arr[1] , $maxDigit);
	}
}

function __vt_sub($arr){
	if(sizeof($arr)==1){
		return -$arr[0];
	}else{
        if(strlen(substr(strrchr($arr[0], "."), 1))>strlen(substr(strrchr($arr[1], "."), 1))){
            $maxDigit = strlen(substr(strrchr($arr[0], "."), 1));
        } else {
            $maxDigit = strlen(substr(strrchr($arr[1], "."), 1));
        }
		return bcsub($arr[0], $arr[1], $maxDigit);
	}
}

function __vt_mul($arr){
	return $arr[0]*$arr[1];
}

function __vt_div($arr){
	try {
		return $arr[0]/$arr[1];
	}catch(Exception $e) {
		return 0;
	}
}

function __vt_equals($arr){
	return $arr[0] == $arr[1];
}

function __vt_ltequals($arr) {
	return $arr[0] <= $arr[1];
}

function __vt_gtequals($arr) {
	return $arr[0] >= $arr[1];
}

function __vt_lt($arr) {
	return $arr[0] < $arr[1];
}

function __vt_gt($arr) {
	return $arr[0] > $arr[1];
}

function __vt_concat($arr){
	return implode($arr);
}
/* Date difference between (input times) or (current time and input time)
 *
 * @param Array $a $a[0] - Input time1, $a[1] - Input time2
 * (if $a[1] is not available $a[0] = Current Time, $a[1] = Input time1)
 * @return int difference timestamp
 */

function __vt_time_diff($arr) {

    $time_operand1 = $time_operand2 = 0;
    if(count($arr) > 1) {
            $time_operand1 = $time1 = $arr[0];
            $time_operand2 = $time2 = $arr[1];
    } else {

		// Added as we need to compare with the values based on the user date format and timezone
		global $default_timezone;

        $time_operand1 = date('Y-m-d H:i:s'); // Current time

        $time_operand2 = $arr[0];
    }

    if(empty($time_operand1) || empty($time_operand2)) return 0;

    $time_operand1 = getValidDBInsertDateTimeValue($time_operand1);
	$time_operand2 = getValidDBInsertDateTimeValue($time_operand2);
    
    //to give the difference if it is only time field
    if(empty($time_operand1) && empty($time_operand2)) {
        $pattern = "/([01]?[0-9]|2[0-3]):[0-5][0-9]/";
        if(preg_match($pattern, $time1) && preg_match($pattern, $time2)){
            $timeDiff = strtotime($time1) - strtotime($time2);
            return date('H:i:s', $timeDiff);
        }
    }
    return (strtotime($time_operand1) - strtotime($time_operand2));
}
/**
 * Calculate the time difference (input times) or (current time and input time) and
 * convert it into number of days.
 * @param Array $a $a[0] - Input time1, $a[1] - Input time2
 * (if $a[1] is not available $a[0] = Current Time, $a[1] = Input time1)
 * @return int number of days
 */
function __vt_time_diffdays($arr) {
    $timediff  = __vt_time_diff($arr);
    $days_diff = floor($timediff / (60 * 60 * 24));
    return $days_diff;
}

function __vt_add_days($arr) {

	if (count($arr) > 1) {
		$baseDate = $arr[0];
		$noOfDays = $arr[1];
	} else {
		$noOfDays = $arr[0];
	}
	if($baseDate==null || empty($baseDate)) {
		$baseDate = date('Y-m-d'); // Current date
	}
	preg_match('/\d\d\d\d-\d\d-\d\d/', $baseDate, $match);
	$baseDate = strtotime($match[0]);
	$date = strftime('%Y-%m-%d', $baseDate + ($noOfDays * 24 * 60 * 60));
	return $date;
}

function __vt_sub_days($arr) {

	if (count($arr) > 1) {
		$baseDate = $arr[0];
		$noOfDays = $arr[1];
	} else {
		$noOfDays = $arr[0];
	}
	if($baseDate==null || empty($baseDate)) {
		$baseDate = date('Y-m-d'); // Current date
	}
	preg_match('/\d\d\d\d-\d\d-\d\d/', $baseDate, $match);
	$baseDate = strtotime($match[0]);
	$date = strftime('%Y-%m-%d', $baseDate - ($noOfDays * 24 * 60 * 60));
	return $date;
}

function __vt_get_date($arr) {
	$type = $arr[0];
	switch ($type) {
		case 'today': return date('Y-m-d');
			break;
		case 'tomorrow': return date('Y-m-d', strtotime('+1 day'));
			break;
		case 'yesterday': return date('Y-m-d', strtotime('-1 day'));
			break;
		default : return date('Y-m-d');
			break;
	}
}

function __vt_add_time($arr) {
	if(count($arr) > 1) {
		$baseTime = $arr[0];
		$minutes = $arr[1];
	} else {
		$baseTime = date('H:i:s');
		$minutes = $arr[0];
	}
	$endTime = strtotime("+$minutes minutes", strtotime($baseTime));
	return date('H:i:s',$endTime);
}

function __vt_sub_time($arr) {
	if(count($arr) > 1) {
		$baseTime = $arr[0];
		$minutes = $arr[1];
	} else {
		$baseTime = date('H:i:s');
		$minutes = $arr[0];
	}
	$endTime = strtotime("-$minutes minutes", strtotime($baseTime));
	return date('H:i:s',$endTime);
}

/** END * */
class VTFieldExpressionEvaluater{
	function __construct($expr){

		$this->operators = array(
				'+'  => '__vt_add',
				'-'  => '__vt_sub',
				'*'  => '__vt_mul',
				'/'  => '__vt_div',
				'==' => '__vt_equals',
				'<=' => '__vt_ltequals',
				'>=' => '__vt_gtequals',
				'<' => '__vt_lt',
				'>' => '__vt_gt',
		);
		$this->functions = array(
				'concat'=>'__vt_concat',
				'time_diff' => '__vt_time_diff',
 	            'time_diffdays' => '__vt_time_diffdays',
				'add_days' => '__vt_add_days',
				'sub_days' => '__vt_sub_days',
				'get_date' => '__vt_get_date',
				'add_time' => '__vt_add_time',
				'sub_time' => '__vt_sub_time'
		);

		$this->operations = array_merge($this->functions, $this->operators);
		$this->expr = $expr;

	}

	function evaluate($env){
		$this->env = $env;
		return $this->exec($this->expr);
	}

	function exec($expr){
		if($expr instanceof VTExpressionSymbol){
			return $this->env($expr);
		}else if($expr instanceof VTExpressionTreeNode){
			$op = $expr->getName();
			if($op->value=='if'){
				$params = $expr->getParams();
				$cond = $this->exec($params[0]);
				if($cond){
					return $this->exec($params[1]);
				}else{
					return $this->exec($params[2]);
				}
			}else{
				$params = array_map(array($this, 'exec'), $expr->getParams());
				$func = $this->operations[$op->value];
				return $func($params);
			}
		}else{
			return $expr;
		}
	}

	function env($sym){
		if($this->env) {
			return $this->env->get($sym->value);
		} else {
			return $sym->value;
		}
	}
}
?>