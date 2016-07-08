<?php
/**
 * Cron updating SoldServices renewal
 * @package YetiForce.Cron
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
require_once 'include/main/WebUI.php';
$db = PearDatabase::getInstance();

$renewal = ['PLL_RENEWED', 'PLL_NOT_RENEWED', 'PLL_NOT_APPLICABLE', 'PLL_WAITING_FOR_VERIFICATION', 'PLL_WAITING_FOR_ACCEPTANCE'];
$query = sprintf('SELECT 
					vtiger_osssoldservices.osssoldservicesid 
				  FROM
					vtiger_osssoldservices 
					INNER JOIN vtiger_crmentity 
					  ON vtiger_crmentity.crmid = vtiger_osssoldservices.osssoldservicesid 
				  WHERE vtiger_crmentity.deleted = 0 
					AND osssoldservices_renew NOT IN (%s)', $db->generateQuestionMarks($renewal));
$result = $db->pquery($query, $renewal);
while (($recordId = $db->getSingleValue($result)) !== false) {
	$recordModel = Vtiger_Record_Model::getInstanceById($recordId, 'OSSSoldServices');
	$recordModel->updateRenewal();
}
