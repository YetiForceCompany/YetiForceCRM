<?php

/**
 * Get modules list action class
 * @package YetiForce.WebserviceAction
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class API_Products_GetImage extends BaseAction
{

	protected $requestMethod = ['GET'];

	public function getAttachments($id)
	{
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT * FROM vtiger_attachments AS va 
			INNER JOIN vtiger_seattachmentsrel AS vs ON vs.attachmentsid = va.attachmentsid 
			INNER JOIN vtiger_products AS vp ON vp.productid = vs.crmid
			WHERE vs.attachmentsid = ? && pos LIKE ?', [$id, '%' . $this->api->app['id'] . '%']);
		return $db->getRow($result);
	}

	public function get($recordId)
	{
		$image = $this->getAttachments($recordId);
		if ($image) {
			$image['base64'] = base64_encode(file_get_contents($image['path'] . $image['attachmentsid'] . '_' . $image['name']));
			return [
				'type' => $image['type'],
				'base64' => $image['base64']
			];
		} else {
			return [];
		}
	}
}
