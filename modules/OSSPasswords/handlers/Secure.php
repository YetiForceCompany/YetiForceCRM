<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * Contributor(s): YetiForce.com.
 * *********************************************************************************************************************************** */

class OSSPasswords_Secure_Handler
{
	/**
	 * EntityAfterSave handler function
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterSave(App\EventHandler $eventHandler){
		if ($eventHandler->getRecordModel()->getChanges('password')) {
			$result = (new \App\Db\Query())->select(['basic.id'])->from('vtiger_modtracker_basic basic')
					->leftJoin('vtiger_modtracker_detail detail', 'basic.id = detail.id')
					->where(['basic.module' => $eventHandler->getModuleName(), 'basic.whodid' => \App\User::getCurrentUserId(), 'detail.fieldname' => 'password'])
					->andWhere(['>', 'changedon', date('Y-m-d')])
					->orderBy(['basic.id' => SORT_DESC])->limit(1)->one();

			if ($result) {
				$conf = Vtiger_Record_Model::getCleanInstance($eventHandler->getModuleName())->getConfiguration();
				$where = ['id' => $result['id'], 'fieldname' => 'password'];
				if ($conf['register_changes'] === 1)
					\App\Db::getInstance()->createCommand()->update('vtiger_modtracker_detail', ['postvalue' => '**********'], $where)->execute();
				else
					\App\Db::getInstance()->createCommand()->update('vtiger_modtracker_detail', ['postvalue' => '**********', 'prevalue' => '**********'], $where)->execute();
			}
		}
	}
}
