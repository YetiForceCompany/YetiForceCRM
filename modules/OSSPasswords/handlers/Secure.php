<?php

/**
 * Protects your password when displaying in history.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */
class OSSPasswords_Secure_Handler
{
	/**
	 * EntityAfterSave handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterSave(App\EventHandler $eventHandler)
	{
		if ($eventHandler->getRecordModel()->getPreviousValue('password')) {
			$result = (new \App\Db\Query())->select(['basic.id'])->from('vtiger_modtracker_basic basic')
				->leftJoin('vtiger_modtracker_detail detail', 'basic.id = detail.id')
				->where(['basic.module' => $eventHandler->getModuleName(), 'basic.whodid' => \App\User::getCurrentUserId(), 'detail.fieldname' => 'password'])
				->andWhere(['>', 'changedon', date('Y-m-d')])
				->orderBy(['basic.id' => SORT_DESC])->limit(1)->one();

			if ($result) {
				$conf = Vtiger_Record_Model::getCleanInstance($eventHandler->getModuleName())->getConfiguration();
				$where = ['id' => $result['id'], 'fieldname' => 'password'];
				if ((int) $conf['register_changes'] === 1) {
					\App\Db::getInstance()->createCommand()->update('vtiger_modtracker_detail', ['postvalue' => '**********'], $where)->execute();
				} else {
					\App\Db::getInstance()->createCommand()->update('vtiger_modtracker_detail', ['postvalue' => '**********', 'prevalue' => '**********'], $where)->execute();
				}
			}
		}
	}
}
