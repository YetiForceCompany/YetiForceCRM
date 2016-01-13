<?php

/**
 * Base for mail scanner action
 * @package YetiForce.MailScanner
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSMailScanner_BaseScannerAction_Model
{

	public function findEmailPrefix($moduleName, $subject)
	{
		$moduleModel = Settings_Vtiger_CustomRecordNumberingModule_Model::getInstance($moduleName);
		$moduleData = $moduleModel->getModuleCustomNumberingData();
		$redex = '/' . $moduleData['prefix'] . '([0-9]*)/';
		preg_match($redex, $subject, $match);
		if ($match[0] != NULL) {
			return $match[0];
		} else {
			return false;
		}
	}
}
