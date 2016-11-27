<?php

/**
 * Save Changes Handler Class
 * @package YetiForce.Handlers
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class SaveChanges extends VTEventHandler
{

	public function handleEvent($eventName, $data)
	{
		$vtEntityDelta = new VTEntityDelta();
		$delta = $vtEntityDelta->getEntityDelta($data->getModuleName(), $data->getId(), true);
		if (isset($delta['active'])) {
			$isExists = (new \App\Db\Query())->from('u_#__crmentity_last_changes')
					->where(['crmid' => $data->getId(), 'fieldname' => 'active'])
					->exists();
			if ($isExists) {
				App\Db::getInstance()->createCommand()->update('u_#__crmentity_last_changes', [
					'date_updated' => date('Y-m-d H:i:s'),
					'user_id' => App\User::getCurrentUserId(),
					], ['crmid' => $data->getId(), 'fieldname' => 'active'])->execute();
			} else {
				App\Db::getInstance()->createCommand()->insert('u_#__crmentity_last_changes', [
					'user_id' => App\User::getCurrentUserId(),
					'crmid' => $data->getId(),
					'fieldname' => 'active',
					'date_updated' => date('Y-m-d H:i:s'),
				])->execute();
			}
		}
	}
}
