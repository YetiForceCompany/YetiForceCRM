<?php
/**
 * Refreshing relationships mail cron file
 * @package YetiForce.Cron
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
$db = PearDatabase::getInstance();
$scanerModel = Vtiger_Record_Model::getCleanInstance('OSSMailScanner');
$result = $db->query("SELECT vtiger_ossmailview.*,roundcube_users.actions FROM vtiger_ossmailview INNER JOIN roundcube_users ON roundcube_users.user_id = vtiger_ossmailview.rc_user WHERE vtiger_ossmailview.verify = 1");
while ($relationRow = $db->getRow($result)) {
	$scanerModel->bindMail($row);
	$db->update('vtiger_ossmailview', [
		'verify' => 0
		], 'ossmailviewid = ?', [$row['ossmailviewid']]
	);
}
$bindByEmail = ['Leads', 'Accounts', 'Partners', 'Vendors', 'Competition', 'Contacts', 'OSSEmployees'];
$bindByPrefix = ['Campaigns', 'HelpDesk', 'Project', 'SSalesProcesses'];
$result = $db->query('SELECT * FROM s_yf_mail_relation_updater');
while ($relationRow = $db->getRow($result)) {
	$db->delete('vtiger_ossmailview_relation', 'crmid = ?', [$relationRow['crmid']]);
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
		$where[] = "subject LIKE '%[$recordNumber]%'";
	} elseif ($bind == 'email') {
		$fieldModels = $recordModel->getModule()->getFieldsByType('email');
		foreach ($fieldModels as $fieldName => $fieldModel) {
			if (!$recordModel->isEmpty($fieldName)) {
				$email = $recordModel->get($fieldName);
				$where[] = "from_email = '$email' OR to_email = '$email' OR cc_email = '$email' OR bcc_email = '$email' ";
			}
		}
	}
	if (!empty($where)) {
		$query = 'SELECT vtiger_ossmailview.*,roundcube_users.actions FROM vtiger_ossmailview INNER JOIN roundcube_users ON roundcube_users.user_id = vtiger_ossmailview.rc_user WHERE ';
		$query .= implode(' OR ', $where);
		$resultMail = $db->query($query);
		if ($db->getRowCount($resultMail)) {
			while ($row = $db->getRow($resultMail)) {
				$scanerModel->bindMail($row);
			}
		}
	}
	$db->delete('s_yf_mail_relation_updater', 'crmid = ?', [$relationRow['crmid']]);
}
