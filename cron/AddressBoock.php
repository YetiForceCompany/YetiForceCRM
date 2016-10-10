<?php
/**
 * Address boock cron file
 * @package YetiForce.Cron
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
$log = LoggerManager::getLogger();
$log->debug('Start create AddressBoock');

$limit = AppConfig::performance('CRON_MAX_NUMERS_RECORD_ADDRESS_BOOCK_UPDATER');
$db = PearDatabase::getInstance();
$currentUser = Users::getActiveAdminUser();
$usersIds = \includes\fields\Owner::getUsersIds();
$i = ['rows' => [], 'users' => count($usersIds)];
$l = 0;
$break = false;
$table = OSSMail_AddressBoock_Model::TABLE;
$last = OSSMail_AddressBoock_Model::getLastRecord();

$query = 'SELECT DISTINCT module_name FROM `com_vtiger_workflows` LEFT JOIN `com_vtiger_workflowtasks` ON com_vtiger_workflowtasks.workflow_id = com_vtiger_workflows.workflow_id WHERE `task` LIKE \'%VTAddressBookTask%\'';
$mainResult = $db->query($query);
while (($moduleName = $db->getSingleValue($mainResult)) !== false) {
	if ($last !== false && $last['module'] != $moduleName) {
		continue;
	}
	$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
	if (!$moduleModel->isActive()) {
		continue;
	}
	$i['rows'][$moduleName] = 0;
	$emailFields = [];
	$fields = $moduleModel->getFieldsByType('email');
	if (empty($fields)) {
		continue;
	}
	foreach ($fields as $field) {
		$emailFields[] = $field->getName();
	}
	$metainfo = \includes\Modules::getEntityInfo($moduleName);
	$queryFields = array_merge(['id'], $metainfo['fieldnameArr'], $emailFields);

	$queryGenerator = new QueryGenerator($moduleName, $currentUser);
	$queryGenerator->setFields($queryFields);
	if ($last !== false) {
		$queryGenerator->addCondition('id', $last['record'], 'a');
	}
	$query = $queryGenerator->getQuery();
	$emailCondition = [];
	foreach ($emailFields as &$fieldName) {
		$emailCondition[] = "$fieldName <> ''";
	}
	$query .= ' AND (' . implode(' OR ', $emailCondition);
	$query .= ') LIMIT ' . ($limit + 1);
	$result = $db->query($query);
	while ($row = $db->getRow($result)) {
		$users = $name = '';
		foreach ($metainfo['fieldnameArr'] as $entityName) {
			$name .= ' ' . $row[$entityName];
			unset($row[$entityName]);
		}
		$record = reset($row);
		foreach ($usersIds as &$userId) {
			if (\includes\Privileges::isPermitted($moduleName, 'DetailView', $record, $userId)) {
				$users .= ',' . $userId;
			}
		}
		$db->delete($table, 'id = ?', [$record]);
		foreach ($emailFields as &$fieldName) {
			if (!empty($row[$fieldName])) {
				$db->insert($table, ['id' => $record, 'email' => $row[$fieldName], 'name' => trim($name), 'users' => $users]);
			}
		}
		$i['rows'][$moduleName] ++;
		$l++;
		if ($limit == $l) {
			OSSMail_AddressBoock_Model::saveLastRecord($record, $moduleName);
			$break = true;
			break;
		}
	}
	if (!$break && $last !== false) {
		OSSMail_AddressBoock_Model::clearLastRecord();
	}
	$last = false;
}
OSSMail_AddressBoock_Model::createABFile();
$log->debug(vtlib\Functions::varExportMin($i));
$log->debug('End create AddressBoock');
