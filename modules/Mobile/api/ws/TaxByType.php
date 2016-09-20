<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class Mobile_WS_TaxByType extends Mobile_WS_Controller
{

	function process(Mobile_API_Request $request)
	{
		$current_user = vglobal('current_user');
		$response = new Mobile_API_Response();
		$current_user = $this->getActiveUser();

		$taxType = $request->get('taxType');

		$result = $this->getTaxDetails($taxType);
		$response->setResult($result);

		return $response;
	}

	protected function getTaxDetails($taxType)
	{
		$adb = PearDatabase::getInstance();
		$tableName = $this->getTableName($taxType);
		$result = $adb->pquery("SELECT * FROM $tableName WHERE deleted = 0", array());
		$rowCount = $adb->num_rows($result);
		if ($rowCount) {
			for ($i = 0; $i < $rowCount; $i++) {
				$row = $adb->query_result_rowdata($result, $i);
				$recordDetails[] = $row;
			}
		}
		return $recordDetails;
	}

	protected function getTableName($taxType)
	{
		switch ($taxType) {
			case 'inventory':
				return 'vtiger_inventorytaxinfo';
				break;
		}
	}
}
