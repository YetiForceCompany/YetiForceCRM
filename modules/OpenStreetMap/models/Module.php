<?php

/**
 * Module Model
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class OpenStreetMap_Module_Model extends Vtiger_Module_Model
{

	public function isAllowModules($moduleName)
	{
		return in_array($moduleName, AppConfig::module($this->getName(), 'ALLOW_MODULES'));
	}

	private static function getCoordinates($address)
	{
		$url = AppConfig::module('OpenStreetMap', 'ADDRESS_TO_SEARCH') . '/?';
		$data = [
			'format' => 'json',
			'addressdetails' => 1,
			'limit' => 1
		];
		$url .= http_build_query(array_merge($data, $address));
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 10,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
		));
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		if ($err) {
			return false;
		} else {
			return json_decode($response, true);
		}
	}

	public static function getCoordinatesByRecord($recordModel)
	{
		$coordinates = [];
		foreach (['a', 'b', 'c'] as $numAddress) {
			$address = [
				'state' => $recordModel->get('addresslevel2' . $numAddress),
				'county' => $recordModel->get('addresslevel5' . $numAddress),
				'city' => $recordModel->get('addresslevel4' . $numAddress),
				'street' => $recordModel->get('addresslevel8' . $numAddress) . ' ' . $recordModel->get('buildingnumber' . $numAddress),
				'country' => $recordModel->get('addresslevel1' . $numAddress),
			];
			$coordinatesDetails = self::getCoordinates($address);
			if ($coordinatesDetails === false)
				break;
			if (!empty($coordinatesDetails)) {
				$coordinatesDetails = reset($coordinatesDetails);
				$coordinates [$numAddress] = [
					'lat' => $coordinatesDetails['lat'],
					'lon' => $coordinatesDetails['lon']
				];
			}
		}
		return $coordinates;
	}

	public static function getLabelsToPopupById($crmid){
		$currentUserModel = Users_Privileges_Model::getCurrentUserModel();
		$recodMetaData = \vtlib\Functions::getCRMRecordMetadata($crmid);
		$moduleName = $recodMetaData['setype'];
		$queryGenerator = new QueryGenerator($moduleName, $currentUserModel);
		$fields = AppConfig::module('OpenStreetMap', 'FIELDS_IN_POPUP');
		$queryGenerator->setFields($fields[$moduleName]);
		
		$query = $queryGenerator->getQuery();
		$db = PearDatabase::getInstance();
		$result = $db->query($query);
		$row = $db->getRow($result);
		$html = '';
		foreach ($row as $fieldName => $value){
			if(!empty($value)){
				$html .=  $value . '<br>';
			}
		}
		return $html;
	}
	public static function readCoordinates($recordId)
	{
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT * FROM u_yf_openstreetmap WHERE crmid = ?', [$recordId]);
		$popup =  self::getLabelsToPopupById($recordId);
		$coordinates = [];
		if ($row = $db->getRow($result)) {
			foreach (['a', 'b', 'c'] as $numAddress) {
				$latName = 'lat' . $numAddress;
				$lonName = 'lon' . $numAddress;
				if (!empty($row[$latName] && !empty($row[$lonName]))) {
					$coordinates[] = [$row[$latName], $row[$lonName], $popup];
				}
			}
		}
		return $coordinates;
	}
}
