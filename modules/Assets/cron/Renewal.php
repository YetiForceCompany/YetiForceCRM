<?php
/**
 * Cron updating Assets renewal
 * @package YetiForce.Cron
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
require_once 'include/main/WebUI.php';
$db = PearDatabase::getInstance();

$renewal = ['PLL_RENEWED', 'PLL_NOT_RENEWED', 'PLL_NOT_APPLICABLE', 'PLL_WAITING_FOR_VERIFICATION', 'PLL_WAITING_FOR_ACCEPTANCE'];
$result = $db->pquery('SELECT vtiger_assets.assetsid FROM vtiger_assets INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_assets.assetsid WHERE vtiger_crmentity.deleted = 0 AND assets_renew NOT IN (' . $db->generateQuestionMarks($renewal) . ')', $renewal);
while (($recordId = $db->getSingleValue($result)) !== false) {
	$recordModel = Vtiger_Record_Model::getInstanceById($recordId, 'Assets');
	$recordModel->updateRenewal();
}
