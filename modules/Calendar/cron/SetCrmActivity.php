<?php
/**
 * @package YetiForce.Cron
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
require_once 'include/main/WebUI.php';
$current_user = Users::getActiveAdminUser();
$db = PearDatabase::getInstance();
$query = 'SELECT vtiger_crmentity.`crmid`, vtiger_crmentity.`setype` FROM vtiger_crmentity INNER JOIN vtiger_entity_stats ON vtiger_entity_stats.crmid = vtiger_crmentity.crmid WHERE vtiger_crmentity.`deleted` = 0 && vtiger_entity_stats.crmactivity IS NOT NULL';
$result = $db->query($query);
while ($row = $db->getRow($result)) {
	Calendar_Record_Model::setCrmActivity(array_flip([$row['crmid']]), $row['setype']);
}

