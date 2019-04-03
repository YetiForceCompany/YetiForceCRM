<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Vtiger_Image_UIType extends Vtiger_MultiImage_UIType
{
	/**
	 * {@inheritdoc}
	 */
	public static $defaultLimit = 1;

	/**
	 * {@inheritdoc}
	 */
	public function getApiDisplayValue($value, bool $record = false, $recordModel = false, bool $rawText = false, $length = false)
	{
		$value = \App\Json::decode($value);
		$returnValue = '';
		if ($value) {
			$returnValue = \App\Json::encode(base64_encode(file_get_contents(current($value)['path'])));
		}
		return $returnValue;
	}
}
