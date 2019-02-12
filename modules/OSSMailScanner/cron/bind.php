<?php
/**
 * Refreshing relationships mail cron file.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
$dbCommand = App\Db::getInstance()->createCommand();
$scanerModel = Vtiger_Record_Model::getCleanInstance('OSSMailScanner');
$dataReader = (new App\Db\Query())->select([
	'vtiger_ossmailview.*',
	'roundcube_users.actions',
])->from('vtiger_ossmailview')
	->innerJoin('roundcube_users', 'roundcube_users.user_id = vtiger_ossmailview.rc_user')->where(['vtiger_ossmailview.verify' => 1])
	->createCommand()->query();

while ($row = $dataReader->read()) {
	$scanerModel->bindMail($row);
	$dbCommand->update('vtiger_ossmailview', ['verify' => 0], ['ossmailviewid' => $row['ossmailviewid']])->execute();
}
$dataReader->close();
$dataReader = (new App\Db\Query())->from('s_#__mail_relation_updater')->createCommand()->query();
$bindByEmail = ['Leads', 'Accounts', 'Partners', 'Vendors', 'Competition', 'Contacts', 'OSSEmployees'];
$bindByPrefix = ['Campaigns', 'HelpDesk', 'Project', 'SSalesProcesses'];
while ($relationRow = $dataReader->read()) {
	$dbCommand->delete('vtiger_ossmailview_relation', ['crmid' => $relationRow['crmid']])->execute();
	$moduleName = \App\Module::getModuleName($relationRow['tabid']);
	$bind = false;
	if (in_array($moduleName, $bindByEmail)) {
		$bind = 'email';
	}
	if (in_array($moduleName, $bindByPrefix)) {
		$bind = 'prefix';
	}
	if ($bind === false) {
		continue;
	}
	$recordModel = Vtiger_Record_Model::getInstanceById($relationRow['crmid'], $moduleName);
	$where = [];
	if ($bind == 'prefix') {
		$recordNumber = $recordModel->getRecordNumber();
		if (empty($recordNumber)) {
			continue;
		}
		$where = ['like', 'vtiger_ossmailview.subject', "[{$recordNumber}]"];
	} elseif ($bind == 'email') {
		$where = ['or'];
		$fieldModels = $recordModel->getModule()->getFieldsByType('email');
		foreach ($fieldModels as $fieldName => $fieldModel) {
			if (!$recordModel->isEmpty($fieldName)) {
				$email = $recordModel->get($fieldName);
				$where[] = ['from_email' => $email];
				$where[] = ['to_email' => $email];
				$where[] = ['cc_email' => $email];
				$where[] = ['bcc_email' => $email];
			}
		}
	}
	if (!empty($where)) {
		$dataReaderMail = (new App\Db\Query())->select(['vtiger_ossmailview.*', 'roundcube_users.actions'])
			->from('vtiger_ossmailview')
			->innerJoin('roundcube_users', 'roundcube_users.user_id = vtiger_ossmailview.rc_user')
			->where($where)->createCommand()->query();
		while ($row = $dataReaderMail->read()) {
			$scanerModel->bindMail($row);
		}
		$dataReaderMail->close();
	}
	$dbCommand->delete('s_#__mail_relation_updater', ['crmid' => $relationRow['crmid']])->execute();
}
$dataReader->close();
