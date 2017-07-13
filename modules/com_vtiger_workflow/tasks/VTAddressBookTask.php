<?php
/**
 * VTAddressBookTask class
 * @package YetiForce.Workflow
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
require_once('modules/com_vtiger_workflow/VTWorkflowUtils.php');
require_once('modules/Users/Users.php');

class VTAddressBookTask extends VTTask
{

	public $executeImmediately = false;

	public function getFieldNames()
	{
		return array('test');
	}

	/**
	 * Execute task
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function doTask($recordModel)
	{
		$db = PearDatabase::getInstance();
		$moduleName = $recordModel->getModuleName();
		$entityId = $recordModel->getId();

		$users = $name = '';
		$table = OSSMail_AddressBook_Model::TABLE;
		$metainfo = \App\Module::getEntityInfo($moduleName);
		foreach ($metainfo['fieldnameArr'] as $entityName) {
			$name .= ' ' . $recordModel->get($entityName);
		}

		$usersIds = \App\Fields\Owner::getUsersIds();
		foreach ($usersIds as &$userId) {
			if (\App\Privilege::isPermitted($moduleName, 'DetailView', $entityId, $userId)) {
				$users .= ',' . $userId;
			}
		}
		$db->delete($table, 'id = ?', [$entityId]);

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$fields = $moduleModel->getFieldsByType('email');
		foreach ($fields as $field) {
			$fieldname = $field->getName();
			if (!empty($recordModel->get($fieldname))) {
				$db->insert($table, ['id' => $entityId, 'email' => $recordModel->get($fieldname), 'name' => trim($name), 'users' => $users]);
			}
		}
		OSSMail_AddressBook_Model::createABFile();
	}

	/**
	 * Function to get contents of this task
	 * @param Vtiger_Record_Model $recordModel
	 * @return bool
	 */
	public function getContents($recordModel)
	{
		$this->contents = true;
		return $this->contents;
	}
}
