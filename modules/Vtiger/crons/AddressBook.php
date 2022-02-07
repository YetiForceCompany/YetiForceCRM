<?php
/**
 * Address book cron file.
 *
 * @package   Cron
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Vtiger_AddressBook_Cron class.
 */
class Vtiger_AddressBook_Cron extends \App\CronHandler
{
	/** {@inheritdoc} */
	public function process()
	{
		\App\Log::trace('Start create AddressBook');

		$limit = App\Config::performance('CRON_MAX_NUMBERS_RECORD_ADDRESS_BOOK_UPDATER');
		$db = \App\Db::getInstance();
		$dbCommand = $db->createCommand();
		$currentUser = Users::getActiveAdminUser();
		$usersIds = \App\Fields\Owner::getUsersIds();
		$i = ['rows' => [], 'users' => \count($usersIds)];
		$l = 0;
		$break = false;
		$table = OSSMail_AddressBook_Model::TABLE;
		$last = OSSMail_AddressBook_Model::getLastRecord();
		$workflows = (new App\Db\Query())->select(['module_name', 'task'])->from('com_vtiger_workflows')
			->leftJoin('com_vtiger_workflowtasks', 'com_vtiger_workflowtasks.workflow_id = com_vtiger_workflows.workflow_id')
			->where(['like', 'task', 'VTAddressBookTask'])
			->indexBy('module_name')->all();
		foreach ($workflows as $row) {
			$task = (array) unserialize($row['task']);
			$moduleName = $row['module_name'];
			if (empty($task['active']) || (false !== $last && $last['module'] != $moduleName)) {
				continue;
			}
			$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
			if (!$moduleModel->isActive()) {
				continue;
			}
			$i['rows'][$moduleName] = 0;
			$fields = $moduleModel->getFieldsByType('email', true);
			if (empty($fields)) {
				continue;
			}
			$this->updateLastActionTime();
			$emailFields = array_keys($fields);
			$metainfo = \App\Module::getEntityInfo($moduleName);
			$queryFields = array_merge(['id'], $metainfo['fieldnameArr'], $emailFields);

			$queryGenerator = new App\QueryGenerator($moduleName, $currentUser->id);
			$queryGenerator->setFields($queryFields);
			if (false !== $last) {
				$queryGenerator->addCondition('id', $last['record'], 'a');
			}
			$query = $queryGenerator->createQuery();
			$emailCondition = ['or'];
			foreach ($emailFields as $fieldName) {
				$emailCondition[] = ['<>', $fieldName, ''];
			}
			$query->andWhere($emailCondition)->limit($limit + 1);
			$dataReaderRows = $query->createCommand()->query();
			while ($row = $dataReaderRows->read()) {
				$users = $name = '';
				foreach ($metainfo['fieldnameArr'] as $entityName) {
					$name .= ' ' . $row[$entityName];
					unset($row[$entityName]);
				}
				$record = reset($row);
				foreach ($usersIds as $userId) {
					if (\App\Privilege::isPermitted($moduleName, 'DetailView', $record, $userId)) {
						$users .= ',' . $userId;
					}
				}
				$added = [];
				$dbCommand->delete($table, ['id' => $record])->execute();
				foreach ($emailFields as $fieldName) {
					if (!empty($row[$fieldName]) && !\in_array($row[$fieldName], $added)) {
						$added[] = $row[$fieldName];
						$dbCommand->insert($table, ['id' => $record, 'email' => $row[$fieldName], 'name' => trim($name), 'users' => $users])->execute();
					}
				}
				++$i['rows'][$moduleName];
				++$l;
				if ($limit == $l) {
					OSSMail_AddressBook_Model::saveLastRecord($record, $moduleName);
					$break = true;
					break;
				}
			}
			$dataReaderRows->close();
			if (!$break && false !== $last) {
				OSSMail_AddressBook_Model::clearLastRecord();
			}
			$last = false;
		}
		OSSMail_AddressBook_Model::createABFile();
		\App\Log::trace(App\Utils::varExport($i));
		\App\Log::trace('End create AddressBook');
	}
}
