<?php
/**
 * Cron updating SoldServices renewal
 * @package YetiForce.Cron
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
require_once 'include/main/WebUI.php';
$db = PearDatabase::getInstance();

$renewal = ['PLL_PLANNED', 'PLL_WAITING_FOR_RENEWAL', ''];
$query = sprintf('SELECT 
					vtiger_osssoldservices.osssoldservicesid 
				  FROM
					vtiger_osssoldservices 
					INNER JOIN vtiger_crmentity 
					  ON vtiger_crmentity.crmid = vtiger_osssoldservices.osssoldservicesid 
				  WHERE vtiger_crmentity.deleted = 0 
					AND osssoldservices_renew IN (%s) OR osssoldservices_renew IS NULL', $db->generateQuestionMarks($renewal));
$result = $db->pquery($query, $renewal);
while (($recordId = $db->getSingleValue($result)) !== false) {
	$recordModel = Vtiger_Record_Model::getInstanceById($recordId, 'OSSSoldServices');
	$recordModel->updateRenewal();
}
