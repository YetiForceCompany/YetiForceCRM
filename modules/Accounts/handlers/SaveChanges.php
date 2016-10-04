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
		$moduleName = $data->getModuleName();
		$vtEntityDelta = new VTEntityDelta();
		$delta = $vtEntityDelta->getEntityDelta($moduleName, $data->getId(), true);
		if (isset($delta['active'])) {
			$db = PearDatabase::getInstance();
			$query = 'SELECT (1) FROM u_yf_crmentity_last_changes WHERE crmid = ? && fieldname = ?';
			$userModel = Users_Privileges_Model::getCurrentUserModel();
			if ($db->getRow($db->pquery($query, [$data->getId(), 'active']))) {
				$db->update('u_yf_crmentity_last_changes', [
					'date_updated' => date('Y-m-d H:i:s'),
					'user_id' => $userModel->getId(),
					], 'crmid = ? && fieldname = ?', [$data->getId(), 'active']);
			} else {
				$params = [
					'user_id' => $userModel->getId(),
					'crmid' => $data->getId(),
					'fieldname' => 'active',
					'date_updated' => date('Y-m-d H:i:s'),
				];
				$db->insert('u_yf_crmentity_last_changes', $params);
			}
		}
	}
}
