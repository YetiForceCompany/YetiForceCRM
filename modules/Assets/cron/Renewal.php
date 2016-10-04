<?php
/**
 * Cron updating Assets renewal
 * @package YetiForce.Cron
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
require_once 'include/main/WebUI.php';
$db = PearDatabase::getInstance();

$renewal = ['PLL_PLANNED', 'PLL_WAITING_FOR_RENEWAL', ''];
$query = 'SELECT 
			vtiger_assets.assetsid 
		  FROM
			vtiger_assets 
			INNER JOIN vtiger_crmentity 
			  ON vtiger_crmentity.crmid = vtiger_assets.assetsid 
		  WHERE vtiger_crmentity.deleted = 0 
			AND assets_renew IN (%s) OR assets_renew IS NULL';
$query = sprintf($query, $db->generateQuestionMarks($renewal));
$result = $db->pquery($query, $renewal);
while (($recordId = $db->getSingleValue($result)) !== false) {
	$recordModel = Vtiger_Record_Model::getInstanceById($recordId, 'Assets');
	$recordModel->updateRenewal();
}
