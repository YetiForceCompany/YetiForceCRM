<?php
/**
 * VTAddressBookTask class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
require_once 'modules/com_vtiger_workflow/VTWorkflowUtils.php';
require_once 'modules/Users/Users.php';

class VTAddressBookTask extends VTTask
{
	public $executeImmediately = false;

	public function getFieldNames()
	{
		return ['test'];
	}

	/**
	 * Execute task.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function doTask($recordModel)
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		$moduleName = $recordModel->getModuleName();
		$entityId = $recordModel->getId();
		$users = $name = '';
		$table = OSSMail_AddressBook_Model::TABLE;
		$metainfo = \App\Module::getEntityInfo($moduleName);
		foreach ($metainfo['fieldnameArr'] as $entityName) {
			$name .= ' ' . $recordModel->get($entityName);
		}
		$usersIds = \App\Fields\Owner::getUsersIds();
		foreach ($usersIds as $userId) {
			if (\App\Privilege::isPermitted($moduleName, 'DetailView', $entityId, $userId)) {
				$users .= ',' . $userId;
			}
		}
		$dbCommand->delete($table, ['id' => $entityId])->execute();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$fields = $moduleModel->getFieldsByType('email');
		foreach ($fields as $field) {
			$fieldname = $field->getName();
			if (!empty($recordModel->get($fieldname))) {
				$dbCommand->insert($table, ['id' => $entityId, 'email' => $recordModel->get($fieldname), 'name' => trim($name), 'users' => $users])->execute();
			}
		}
	}

	/**
	 * Function to get contents of this task.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @return bool
	 */
	public function getContents($recordModel)
	{
		$this->contents = true;

		return $this->contents;
	}
}
