<?php

/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
*
 ********************************************************************************/

// TTF Font names
DEFINE("FF_COURIER",'Courier New');
DEFINE("FF_VERDANA",'Verdana');
DEFINE("FF_TIMES",'Times New Roman');
DEFINE("FF_COMIC",'Comic');
DEFINE("FF_ARIAL",'Arial');
DEFINE("FF_GEORGIA",'Georgia');
DEFINE("FF_TREBUCHE",'Trebuc');
DEFINE("FF_DEJAVUSAN",'DejaVuSans');

// Chinese font
DEFINE("FF_SIMSUN",'Simsun');
DEFINE("FF_CHINESE",'Chinese');
DEFINE("FF_BIG5",'Big5');

DEFINE("FF_FONT1",'Vera');

/**This function is used to get the font name when a language code is given
* Param $locale - language code
* Return type string - font name
*/
function calculate_font_name($locale)

{
	global $log;
	$log->debug("Entering calculate_font_name(".$locale.") method ...");

	switch($locale)
	{
		case 'cn_zh':
			$log->debug("Exiting calculate_font_name method ...");
			return FF_SIMSUN;
		case 'tw_zh':
			if(!function_exists('iconv')){
				echo " Unable to display traditional Chinese on the graphs.<BR>The function iconv does not exists please read more about <a href='http://us4.php.net/iconv'>iconv here</a><BR>";
				$log->debug("Exiting calculate_font_name method ...");
				return FF_DEJAVUSAN;

			}
			else
			{
				$log->debug("Exiting calculate_font_name method ...");
				 return FF_CHINESE;
			}
		default:
			$log->debug("Exiting calculate_font_name method ...");
			return FF_DEJAVUSAN;
	}

	$log->debug("Exiting calculate_font_name method ...");
	return FF_DEJAVUSAN;
}

/**This function is used to generate the n colors.
* Param $count - number of colors to generate
* Param $start - value of first color
* Param $step - color increment to apply
* Return type array - array of n colors values
*/

function color_generator($count = 1, $start = '33CCFF', $step = '221133')
{
	global $log;
	$log->debug("Entering color_generator(".$count.",".$start.",".$step.") method ...");
	// explode color strings to RGB array
	if($start{0} == "#") $start = substr($start,1);
	if($step{0} == "#") $step = substr($step,1);
	// pad shorter strings with 0
	$start = substr($start."000000",0,6);
	$step = substr($step."000000",0,6);
	$colors = array(hexdec(substr($start,0,2)),hexdec(substr($start,2,2)),hexdec(substr($start,4,2)));
	$steps = array(hexdec(substr($step,0,2)),hexdec(substr($step,2,2)),hexdec(substr($step,4,2)));
	// buils $count colours adding $step to $start
	$result = array();
	for($i=1; $i<=$count; $i++){
		array_push($result,"#".dechex($colors[0]).dechex($colors[1]).dechex($colors[2]));
		for($j=0; $j<3; $j++) {
			$colors[$j] += $steps[$j];
			if($colors[$j] > 0xFF) $colors[$j] -= 0xFF;
		}
	}
	$log->debug("Exiting color_generator method ...");
	return $result;
}

/**This function is used to define the optimum spacin for tick marks on an axis
* Param $max - maximum value of axis
* Return type array - array of 2 values major and minor spacing
*/

function get_tickspacing($max = 10)
{
	global $log,$app_strings;
	$log->debug("Entering get_tickspacing(".$max.") method ...");
	$result = array(1,1);
	
	// normalize $max to get value between 1 and 10
	$coef = pow(10,floor(log10($max)));
	if($coef == 0)
	{
		$data=0;
		echo "<h3>".$app_strings['NO_DATA_AVAILABLE_WITH_SPECIFIED_PERIOD']."</h3>";
		$log->debug("Exiting get_tickspacing method ...");
		return $data;
	}
	$normalized = $max / $coef;
	
	if($normalized < 1.5){
		$result[0] = 0.2;
		$result[1] = 0.1;
	}
	elseif($normalized < 5){
		$result[0] = 0.5;
		$result[1] = 0.1;
	}
	elseif($normalized < 10){
		$result[0] = 1.0;
		$result[1] = 0.5;
	}
	$result[0] *= $coef;
	$result[1] *= $coef;
	$log->debug("Exiting get_tickspacing method ...");
	return $result;
}
?>
