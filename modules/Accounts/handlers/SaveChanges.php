<?php

/**
 * Save Changes Handler Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Accounts_SaveChanges_Handler
{
	/**
	 * EntityAfterSave handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterSave(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		if ($recordModel->getPreviousValue('active') !== $recordModel->get('active')) {
			$isExists = (new \App\Db\Query())->from('u_#__crmentity_last_changes')
				->where(['crmid' => $recordModel->getId(), 'fieldname' => 'active'])
				->exists();
			if ($isExists) {
				App\Db::getInstance()->createCommand()->update('u_#__crmentity_last_changes', [
					'date_updated' => date('Y-m-d H:i:s'),
					'user_id' => App\User::getCurrentUserId(),
					], ['crmid' => $recordModel->getId(), 'fieldname' => 'active'])->execute();
			} else {
				App\Db::getInstance()->createCommand()->insert('u_#__crmentity_last_changes', [
					'user_id' => App\User::getCurrentUserId(),
					'crmid' => $recordModel->getId(),
					'fieldname' => 'active',
					'date_updated' => date('Y-m-d H:i:s'),
				])->execute();
			}
		}
	}
}
