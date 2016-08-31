<?php

/**
 * Save geographical coordinates Handler Class
 * @package YetiForce.Handlers
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class OpenStreetMapHandler extends VTEventHandler
{

	function handleEvent($eventName, $data)
	{
		$recordModel = Vtiger_Record_Model::getInstanceByEntity($data->focus, $data->getId());
		$coordinates = OpenStreetMap_Module_Model::getCoordinatesByRecord($recordModel);
		$db = PearDatabase::getInstance();
		$params = [];
		foreach($coordinates as $typeAddress => $coordinate){
			$params['lat' . $typeAddress] = $coordinate['lat'];
			$params['lon' . $typeAddress] = $coordinate['lon'];
		}
		if($data->focus->mode == 'edit'){
			if(!empty($params)){
				$db->update('u_yf_openstreetmap', $params, 'crmid = ?', [$data->getId()]);
			}
		} else {
			$params['crmid'] = $data->getId();
			$db->insert('u_yf_openstreetmap', $params);
		}
	}
}
