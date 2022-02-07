<?php

/**
 * Api CardDAV Handler Class.
 *
 * @package   Handler
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class API_CardDAV_Handler
{
	/**
	 * @var array CardDav fields structure.
	 */
	const FIELDS = [
		'Contacts' => [
			'firstname', 'lastname', 'email', 'secondary_email', 'phone', 'mobile', 'description', 'jobtitle', 'addresslevel1a', 'addresslevel2a', 'addresslevel3a', 'addresslevel4a', 'addresslevel5a', 'addresslevel6a', 'addresslevel7a', 'addresslevel8a', 'addresslevel1b', 'addresslevel2b', 'addresslevel3b', 'addresslevel4b', 'addresslevel5b', 'addresslevel6b', 'addresslevel7b', 'addresslevel8b', 'localnumbera', 'localnumberb'
		],
		'OSSEmployees' => [
			'name', 'last_name', 'business_phone', 'business_mail', 'private_phone', 'private_mail', 'description', 'company_name', 'street', 'city', 'state', 'code', 'country', 'ship_street', 'ship_city', 'ship_state', 'ship_code', 'ship_country', 'secondary_phone'
		],
	];

	/**
	 * @var array CardDav tables structure.
	 */
	const TABLES = [
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
		foreach (static::FIELDS[$moduleName] as $fieldName) {
			if (isset($delta[$fieldName])) {
				$info = static::TABLES[$moduleName];
				\App\Db::getInstance()->createCommand()
					->update($info[0], ['dav_status' => 1], [$info[1] => $recordModel->getId()])->execute();
				return true;
			}
		}
	}

	/**
	 * EntityAfterDelete handler function.
	 *
	 * @param \App\EventHandler $eventHandler
	 */
	public function entityAfterDelete(App\EventHandler $eventHandler)
	{
		\App\Integrations\Dav\Card::deleteByCrmId($eventHandler->getRecordModel()->getId());
	}

	/**
	 * EntityChangeState handler function.
	 *
	 * @param \App\EventHandler $eventHandler
	 */
	public function entityChangeState(App\EventHandler $eventHandler)
	{
		$moduleName = $eventHandler->getModuleName();
		if (isset(static::TABLES[$moduleName])) {
			$row = static::TABLES[$moduleName];
			\App\Db::getInstance()->createCommand()
				->update($row[0], ['dav_status' => 1], [$row[1] => $eventHandler->getRecordModel()->getId()])->execute();
		}
	}
}
