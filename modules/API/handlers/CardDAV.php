<?php

/**
 * Api CardDAV Handler Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class API_CardDAV_Handler
{
	const DELTA_FIELDS = [
		'Contacts' => ['firstname', 'lastname', 'email', 'secondary_email', 'phone', 'mobile'],
		'OSSEmployees' => ['name', 'last_name', 'business_phone', 'business_mail', 'private_phone', 'private_mail'],
	];
	const UPDATE_DETAIL = [
		'Contacts' => ['vtiger_contactdetails', 'contactid'],
		'OSSEmployees' => ['vtiger_ossemployees', 'ossemployeesid'],
	];

	/**
	 * EntityAfterSave handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterSave(App\EventHandler $eventHandler)
	{
		$moduleName = $eventHandler->getModuleName();
		$recordModel = $eventHandler->getRecordModel();
		$isNew = $recordModel->isNew();
		if ($isNew) {
			return true;
		}
		$delta = $recordModel->getPreviousValue();
		foreach (static::DELTA_FIELDS[$moduleName] as &$fieldName) {
			if (isset($delta[$fieldName])) {
				$info = static::UPDATE_DETAIL[$moduleName];
				\App\Db::getInstance()->createCommand()
					->update($info[0], ['dav_status' => 1], [$info[1] => $recordModel->getId()])
					->execute();

				return true;
			}
		}
	}
}
