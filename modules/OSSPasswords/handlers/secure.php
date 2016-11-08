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
require_once 'include/events/VTEntityDelta.php';
require_once( 'modules/Users/Users.php' );
require_once( 'modules/Users/models/Record.php' );

class SECURE extends VTEventHandler
{

	public function handleEvent($eventName, $entityData)
	{
		$moduleName = $entityData->getModuleName();
		if ($moduleName == 'OSSPasswords') {
			if ($eventName == 'vtiger.entity.aftersave.final') {
				$result = (new \App\Db\Query())->select(['basic.id'])->from('vtiger_modtracker_basic basic')
						->leftJoin('vtiger_modtracker_detail detail', 'basic.id = detail.id')
						->where(['basic.module' => 'OSSPasswords', 'basic.whodid' => \App\User::getCurrentUserId(), 'detail.fieldname' => 'password'])
						->andWhere(['>', 'changedon', $currentDate])
						->orderBy(['basic.id' => SORT_DESC])->limit(1)->one();
				
				if ($result) {
					$toUpdate = $result['id'];
					$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
					$conf = $recordModel->getConfiguration();
					$where = ['id' => $toUpdate, 'fieldname' => 'password'];
					if ($conf['register_changes'] === 1)
						\App\Db::getInstance ()->createCommand()->update('vtiger_modtracker_detail', ['postvalue' => '**********'], $where)->execute();
					else
						\App\Db::getInstance()->createCommand()->update('vtiger_modtracker_detail', ['postvalue' => '**********', 'prevalue' => '**********'], $where)->execute();
				}
			}
		}
	}
}
