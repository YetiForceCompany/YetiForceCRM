<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
$DEVELOPER_CONFIG =[
	// Turn the possibility to change generatedtype
	'CHANGE_GENERATEDTYPE' => FALSE,
];
class SysDeveloper {
	static function get($key, $defvalue=FALSE) {
		global $DEVELOPER_CONFIG;
		if(isset($DEVELOPER_CONFIG[$key])) {
			return $DEVELOPER_CONFIG[$key];
		}
		return $defvalue;
	}
	/** Get boolean value */
	static function getBoolean($key, $defvalue=FALSE) {
		return self::get($key, $defvalue);
	}
}
