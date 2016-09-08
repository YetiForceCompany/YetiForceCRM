<?php
$log = LoggerManager::getLogger();
$log->debug('Start create AddressBoock');
$db = PearDatabase::getInstance();
$tables = [
	'Contacts' => 'vtiger_contactsbookmails',
	'OSSEmployees' => 'vtiger_ossemployeesbookmails',
	'Accounts' => 'vtiger_accountbookmails',
	'Leads' => 'vtiger_leadbookmails',
	'Vendors' => 'vtiger_vendorbookmails'
];
$currentUser = Users::getActiveAdminUser();
$usersIds = \includes\fields\Owner::getUsersIds();
$i = ['tables' => 0, 'rows' => 0, 'users' => count($usersIds)];
foreach ($tables as $moduleName => $table) {
	$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
	if (!$moduleModel->isActive()) {
		continue;
	}
	$emailFields = [];
	$fields = $moduleModel->getFieldsByType('email');
	foreach ($fields as $field) {
		$emailFields[] = $field->getName();
	}
	$metainfo = \includes\Modules::getEntityInfo($moduleName);
	$queryFields = array_merge(['id'], $metainfo['fieldnameArr'], $emailFields);

	$queryGenerator = new QueryGenerator($moduleName, $currentUser);
	$queryGenerator->setFields($queryFields);
	$query = $queryGenerator->getQuery();
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
				$db->insert($table, ['id' => $record, 'email' => $row[$fieldName], 'name' => trim($name), 'users' => trim($users, ',')]);
			}
		}
		$i['rows'] ++;
	}
	$i['tables'] ++;
}
OSSMail_Module_Model::createBookMailsFiles($tables);
$log->debug(vtlib\Functions::varExportMin($i));
$log->debug('End create AddressBoock');
