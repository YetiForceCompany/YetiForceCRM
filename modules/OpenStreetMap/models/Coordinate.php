<?php

/**
 * Coordiante model.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OpenStreetMap_Coordinate_Model extends \App\Base
{
	/**
	 * Radius earth.
	 */
	const EARTH_RADIUS = 6378137;

	/**
	 * Function to get instance.
	 *
	 * @return \self
	 */
	public static function getInstance()
	{
		return new self();
	}

	/**
	 * The function return the border coordinates for the point.
	 *
	 * @param array $coordinates
	 * @param int   $radius
	 *
	 * @return float[]
	 */
	private function getMargins($coordinates, $radius)
	{
		$earthRadius = static::EARTH_RADIUS;
		$lat = $coordinates['lat'];
		$long = $coordinates['lon'];
		$radius *= 1000;

		return [
			'latMax' => $lat + rad2deg($radius / $earthRadius),
			'latMin' => $lat - rad2deg($radius / $earthRadius),
			'lonMax' => $long + rad2deg($radius / $earthRadius / cos(deg2rad($lat))),
			'lonMin' => $long - rad2deg($radius / $earthRadius / cos(deg2rad($lat))),
		];
	}

	/**
	 * Function to get coordinates of center point.
	 *
	 * @return array
	 */
	public function getCoordinatesCenter()
	{
		$coordinatesCenter = [];
		if (!$this->isEmpty('lat') && !$this->isEmpty('lon')) {
			$coordinatesCenter = [
				'lat' => $this->get('lat'),
				'lon' => $this->get('lon'),
			];
		}
		if ($searchValue = $this->get('searchValue')) {
			$coordinatesCenter = \App\Map\Coordinates::getInstance()->getCoordinatesByValue($searchValue);
		}
		$this->set('coordinatesCenter', $coordinatesCenter);
		return $coordinatesCenter;
	}

	/**
	 * Function get label in popup.
	 *
	 * @param int $crmid
	 *
	 * @return string
	 */
	public function getLabelsToPopupById($crmid)
	{
		$recodMetaData = \vtlib\Functions::getCRMRecordMetadata($crmid);
		$moduleName = $recodMetaData['setype'];
		$queryGenerator = new App\QueryGenerator($moduleName);
		$fields = App\Config::module('OpenStreetMap', 'mapPinFields');
		$queryGenerator->setFields($fields[$moduleName]);
		$queryGenerator->addNativeCondition(['vtiger_crmentity.crmid' => $crmid]);
		$row = $queryGenerator->createQuery()->one();
		$html = '';
		foreach ($row as $value) {
			if (!empty($value)) {
				$html .= \App\Purifier::encodeHtml($value) . '<br />';
			}
		}
		return $html;
	}

	/**
	 * Function to get coordinates for record.
	 *
	 * @param int $recordId
	 *
	 * @return array
	 */
	public function readCoordinates($recordId)
	{
		$dataReader = (new App\Db\Query())->from('u_#__openstreetmap')
			->where(['crmid' => $recordId])
			->createCommand()->query();
		$popup = self::getLabelsToPopupById($recordId);
		$coordinates = [];
		while ($row = $dataReader->read()) {
			if (!empty($row['lat'] && !empty($row['lon']))) {
				$coordinates[] = [
					'lat' => $row['lat'],
					'lon' => $row['lon'],
					'label' => $popup,
					'color' => '#000',
				];
			}
		}
		$dataReader->close();
		return $coordinates;
	}

	/**
	 * Function to get content in popup.
	 *
	 * @param array  $data
	 * @param string $moduleName
	 *
	 * @return string
	 */
	public function getLabelToPopupByArray($data, $moduleName)
	{
		$html = '<b><a href="index.php?module=' . $moduleName . '&view=Detail&record=' . $data['crmid'] . '"><span class="description">';
		$fields = App\Config::module('OpenStreetMap', 'mapPinFields');
		foreach ($fields[$moduleName] as $fieldName) {
			if (!empty($data[$fieldName])) {
				$html .= \App\Purifier::encodeHtml($data[$fieldName]) . '<br />';
			}
		}
		$html .= '</span></a></b><input type=hidden class="coordinates" data-lon="' . $data['lon'] . '" data-lat="' . $data['lat'] . '">';
		$html .= '<button class="btn btn-success btn-xs startTrack marginTB3 mr-3"><span class="fas fa-truck mr-2"></span>' . \App\Language::translate('LBL_START') . '</button>';
		$html .= '<button class="btn btn-danger btn-xs endTrack marginTB3"><span class="fas fa-flag-checkered mr-2"></span>' . \App\Language::translate('LBL_END') . '</button><br />';
		$html .= '<button class="btn btn-warning btn-xs indirectPoint marginTB3 mr-3"><span class="fas fa-flag mr-2"></span>' . \App\Language::translate('LBL_INDIRECT_POINT', 'OpenStreetMap') . '</button>';
		return $html . '<button class="btn btn-primary btn-xs searchInRadius marginTB3"><span class="fas fa-arrows-to-dot mr-2"></span>' . \App\Language::translate('LBL_SEARCH_IN_RADIUS', 'OpenStreetMap') . '</button>';
	}

	public static $colors = [];

	/**
	 * Draws color.
	 *
	 * @staticvar int $indexColor
	 *
	 * @param string $value
	 *
	 * @return string color
	 */
	private function getMarkerColor($value)
	{
		static $indexColor = 0;
		if (empty($value)) {
			return '#000';
		}
		if (isset(self::$colors[$value])) {
			return self::$colors[$value];
		}
		$defaultColors = ['ff0000', 'ff00de', '7000ff', '001eff', '00c2ff', '00ff45', 'ff9b00', '961d5f',
			'FF79E1', 'FF73B9', 'FE67EB', 'E77AFE', 'D97BFD', 'A27AFE', 'FF8A8A', 'FF86E3', 'FF86C2',
			'FE8BF0', 'EA8DFE', 'DD88FD', 'AD8BFE', 'FF9797', 'FF97E8', 'FF97CB', 'FE98F1', 'ED9EFE',
			'E29BFD', 'B89AFE', 'FFA8A8', 'FFACEC', 'FFA8D3', 'FEA9F3', 'EFA9FE', 'E7A9FE', 'C4ABFE',
			'FFBBBB', 'FFACEC', 'FFBBDD', 'FFBBF7', 'F2BCFE', 'EDBEFE', 'D0BCFE', 'FFCECE', 'FFC8F2',
			'FFC8E3', 'FFCAF9', 'F5CAFF', 'F0CBFE', 'DDCEFF', 'FFDFDF', 'FFDFF8', 'FFDFEF', 'FFDBFB',
			'F9D9FF', 'F4DCFE', 'E6DBFF', 'FFECEC', 'FFEEFB', 'FFECF5', 'FFEEFD', 'FDF2FF', 'FAECFF',
			'F1ECFF', 'FFF2F2', 'FFFEFB', 'FFF9FC', 'FFF9FE', 'FFFDFF', 'FDF9FF', 'FBF9FF', '800080',
			'872187', '9A03FE', '892EE4', '3923D6', '2966B8', '23819C', 'BF00BF', 'BC2EBC', 'A827FE',
			'9B4EE9', '6755E3', '2F74D0', '2897B7', 'DB00DB', 'D54FD5', 'B445FE', 'A55FEB', '8678E9',
			'4985D6', '2FAACE', 'F900F9', 'DD75DD', 'BD5CFE', 'AE70ED', '9588EC', '6094DB', '44B4D5',
			'FF4AFF', 'DD75DD', 'C269FE', 'AE70ED', 'A095EE', '7BA7E1', '57BCD9', 'FF86FF', 'E697E6',
			'CD85FE', 'C79BF2', 'B0A7F1', '8EB4E6', '7BCAE1', 'FFA4FF', 'EAA6EA', 'D698FE', 'CEA8F4',
			'BCB4F3', 'A9C5EB', '8CD1E6', 'FFBBFF', 'EEBBEE', 'DFB0FF', 'DBBFF7', 'CBC5F5', 'BAD0EF',
			'A5DBEB', 'FFCEFF', 'F0C4F0', 'E8C6FF', 'E1CAF9', 'D7D1F8', 'CEDEF4', 'B8E2EF', 'FFDFFF',
			'F4D2F4', 'EFD7FF', 'EDDFFB', 'E3E0FA', 'E0EAF8', 'C9EAF3', 'FFECFF', 'F4D2F4', 'F9EEFF',
			'F5EEFD', 'EFEDFC', 'EAF1FB', 'DBF0F7', 'FFF9FF', 'FDF9FD', 'FEFDFF', 'FEFDFF', 'F7F5FE',
			'F8FBFE', 'EAF7FB', '5757FF', '62A9FF', '62D0FF', '06DCFB', '01FCEF', '03EBA6', '01F33E',
			'6A6AFF', '75B4FF', '75D6FF', '24E0FB', '1FFEF3', '03F3AB', '0AFE47', '7979FF', '86BCFF',
			'8ADCFF', '3DE4FC', '5FFEF7', '33FDC0', '4BFE78', '8C8CFF', '99C7FF', '99E0FF', '63E9FC',
			'74FEF8', '62FDCE', '72FE95', '9999FF', '99C7FF', 'A8E4FF', '75ECFD', '92FEF9', '7DFDD7',
			'8BFEA8', 'AAAAFF', 'A8CFFF', 'BBEBFF', '8CEFFD', 'A5FEFA', '8FFEDD', 'A3FEBA', 'BBBBFF',
			'BBDAFF', 'CEF0FF', 'ACF3FD', 'B5FFFC', 'A5FEE3', 'B5FFC8', 'CACAFF', 'D0E6FF', 'D9F3FF',
			'C0F7FE', 'CEFFFD', 'BEFEEB', 'CAFFD8', 'E1E1FF', 'DBEBFF', 'ECFAFF', 'C0F7FE', 'E1FFFE',
			'BDFFEA', 'EAFFEF', 'EEEEFF', 'ECF4FF', 'F9FDFF', 'E6FCFF', 'F2FFFE', 'CFFEF0', 'EAFFEF',
			'F9F9FF', 'F9FCFF', 'FDFEFF', 'F9FEFF', 'FDFFFF', 'F7FFFD', 'F9FFFB', '1FCB4A', '59955C',
			'48FB0D', '2DC800', '59DF00', '9D9D00', 'B6BA18', '27DE55', '6CA870', '79FC4E', '32DF00',
			'61F200', 'C8C800', 'CDD11B', '4AE371', '80B584', '89FC63', '36F200', '66FF00', 'DFDF00',
			'DFE32D', '7CEB98', '93BF96', '99FD77', '52FF20', '95FF4F', 'FFFFAA', 'EDEF85', '93EEAA',
			'A6CAA9', 'AAFD8E', '6FFF44', 'ABFF73', 'FFFF84', 'EEF093', 'A4F0B7', 'B4D1B6', 'BAFEA3',
			'8FFF6F', 'C0FF97', 'FFFF99', 'F2F4B3', 'BDF4CB', 'C9DECB', 'CAFEB8', 'A5FF8A', 'D1FFB3',
			'FFFFB5', 'F5F7C4', 'D6F8DE', 'DBEADC', 'DDFED1', 'B3FF99', 'DFFFCA', 'FFFFC8', 'F7F9D0',
			'E3FBE9', 'E9F1EA', 'EAFEE2', 'D2FFC4', 'E8FFD9', 'FFFFD7', 'FAFBDF', 'E3FBE9', 'F3F8F4',
			'F1FEED', 'E7FFDF', 'F2FFEA', 'FFFFE3', 'FCFCE9', 'FAFEFB', 'FBFDFB', 'FDFFFD', 'F5FFF2',
			'FAFFF7', 'FFFFFD', 'FDFDF0', 'BABA21', 'C8B400', 'DFA800', 'DB9900', 'FFB428', 'FF9331',
			'FF800D', 'E0E04E', 'D9C400', 'F9BB00', 'EAA400', 'FFBF48', 'FFA04A', 'FF9C42', 'E6E671',
			'E6CE00', 'FFCB2F', 'FFB60B', 'FFC65B', 'FFAB60', 'FFAC62', 'EAEA8A', 'F7DE00', 'FFD34F',
			'FFBE28', 'FFCE73', 'FFBB7D', 'FFBD82', 'EEEEA2', 'FFE920', 'FFDD75', 'FFC848', 'FFD586',
			'FFC48E', 'FFC895', 'F1F1B1', 'FFF06A', 'FFE699', 'FFD062', 'FFDEA2', 'FFCFA4', 'FFCEA2',
			'F4F4BF', 'FFF284', 'FFECB0', 'FFE099', 'FFE6B5', 'FFD9B7', 'FFD7B3', 'F7F7CE', 'FFF7B7',
			'FFF1C6', 'FFEAB7', 'FFEAC4', 'FFE1C6', 'FFE2C8', 'F9F9DD', 'FFF9CE', 'FFF5D7', 'FFF2D2',
			'FFF2D9', 'FFEBD9', 'FFE6D0', 'FBFBE8', 'FFFBDF', 'FFFAEA', 'FFF9EA', 'FFF7E6', 'FFF4EA',
			'FFF1E6', 'FEFEFA', 'FFFEF7', 'FFFDF7', 'FFFDF9', 'FFFDF9', 'FFFEFD', 'FFF9F4', 'D1D17A',
			'C0A545', 'C27E3A', 'C47557', 'B05F3C', 'C17753', 'B96F6F', 'D7D78A', 'CEB86C', 'C98A4B',
			'CB876D', 'C06A45', 'C98767', 'C48484', 'DBDB97', 'D6C485', 'D19C67', 'D29680', 'C87C5B',
			'D0977B', 'C88E8E', 'E1E1A8', 'DECF9C', 'DAAF85', 'DAA794', 'CF8D72', 'DAAC96', 'D1A0A0',
			'E9E9BE', 'E3D6AA', 'DDB791', 'DFB4A4', 'D69E87', 'E0BBA9', 'D7ACAC', 'EEEECE', 'EADFBF',
			'E4C6A7', 'E6C5B9', 'DEB19E', 'E8CCBF', 'DDB9B9', 'E9E9C0', 'EDE4C9', 'E9D0B6', 'EBD0C7',
			'E4C0B1', 'ECD5CA', 'E6CCCC', 'EEEECE', 'EFE7CF', 'EEDCC8', 'F0DCD5', 'EACDC1', 'F0DDD5',
			'ECD9D9', 'F1F1D6', 'F5EFE0', 'F2E4D5', 'F5E7E2', 'F0DDD5', 'F5E8E2', 'F3E7E7', 'F5F5E2',
			'F9F5EC', 'F9F3EC', 'F9EFEC', 'F5E8E2', 'FAF2EF', 'F8F1F1', 'FDFDF9', 'FDFCF9', 'FCF9F5',
			'FDFAF9', 'FDFAF9', 'FCF7F5', 'FDFBFB', 'F70000', 'B9264F', '990099', '74138C', '0000CE',
			'1F88A7', '4A9586', 'FF2626', 'D73E68', 'B300B3', '8D18AB', '5B5BFF', '25A0C5', '5EAE9E',
			'FF5353', 'DD597D', 'CA00CA', 'A41CC6', '7373FF', '29AFD6', '74BAAC', 'FF7373', 'E37795',
			'D900D9', 'BA21E0', '8282FF', '4FBDDD', '8DC7BB', 'FF8E8E', 'E994AB', 'FF2DFF', 'CB59E8',
			'9191FF', '67C7E2', 'A5D3CA', 'FFA4A4', 'EDA9BC', 'F206FF', 'CB59E8', 'A8A8FF', '8ED6EA',
			'C0E0DA', 'FFB5B5', 'F0B9C8', 'FF7DFF', 'D881ED', 'B7B7FF', 'A6DEEE', 'CFE7E2', 'FFC8C8',
			'F4CAD6', 'FFA8FF', 'EFCDF8', 'C6C6FF', 'C0E7F3', 'DCEDEA', 'FFEAEA', 'F8DAE2', 'FFC4FF',
			'EFCDF8', 'DBDBFF', 'D8F0F8', 'E7F3F1', 'FFEAEA', 'FAE7EC', 'FFE3FF', 'F8E9FC', 'EEEEFF',
			'EFF9FC', 'F2F9F8', 'FFFDFD', 'FEFAFB', 'FFFDFF', 'FFFFFF', 'FDFDFF', 'FAFDFE', 'F7FBFA', ];

		$color = '#' . $defaultColors[$indexColor];
		++$indexColor;
		self::$colors[$value] = $color;

		return $color;
	}

	/**
	 * Function to get coordinates for many records.
	 *
	 * @param int[] $records Array with id of records
	 *
	 * @return array
	 */
	public function readCoordinatesByRecords(array $records)
	{
		$moduleModel = $this->get('srcModuleModel');
		$groupByField = $this->get('groupBy');
		$coordinatesCenter = $this->get('coordinatesCenter');
		$radius = $this->get('radius');
		$moduleName = $moduleModel->getName();
		$fields = App\Config::module('OpenStreetMap', 'mapPinFields');
		$fields = $fields[$moduleName];
		if (!empty($groupByField)) {
			$fields[] = $groupByField;
		}
		$queryGenerator = new App\QueryGenerator($moduleName);
		$queryGenerator->setFields($fields);
		$queryGenerator->setCustomColumn('u_#__openstreetmap.lat');
		$queryGenerator->setCustomColumn('u_#__openstreetmap.lon');
		$queryGenerator->setCustomColumn('vtiger_crmentity.crmid');
		$queryGenerator->addJoin(['LEFT JOIN', 'u_#__openstreetmap', 'vtiger_crmentity.crmid = u_#__openstreetmap.crmid']);
		$query = $queryGenerator->createQuery();
		$andWhere = ['and', ['vtiger_crmentity.crmid' => $records], ['u_#__openstreetmap.type' => 'a']];
		if (!empty($coordinatesCenter) && !empty($radius)) {
			$margins = self::getMargins($coordinatesCenter, $radius);
			$andWhere[] = ['<', 'u_#__openstreetmap.lat', $margins['latMax']];
			$andWhere[] = ['>', 'u_#__openstreetmap.lat', $margins['latMin']];
			$andWhere[] = ['<', 'u_#__openstreetmap.lon', $margins['lonMax']];
			$andWhere[] = ['>', 'u_#__openstreetmap.lon', $margins['lonMin']];
		}
		$dataReader = $query->andWhere($andWhere)->createCommand()->query();
		$coordinates = [];
		while ($row = $dataReader->read()) {
			if (!empty($row['lat'] && !empty($row['lon']))) {
				$coordinates[] = [
					'recordId' => $row['crmid'],
					'lat' => $row['lat'],
					'lon' => $row['lon'],
					'label' => self::getLabelToPopupByArray($row, $moduleName),
					'color' => self::getMarkerColor($row[$groupByField] ?? '')
				];
			}
		}
		$dataReader->close();

		return $coordinates;
	}

	/**
	 * Get coordinates for select records.
	 *
	 * @return array
	 */
	public function getCoordinatesCustomView()
	{
		$selectedIds = $this->get('selectedIds');
		if ('all' == $selectedIds) {
			return $this->readAllCoordinatesFromCustomeView();
		}
		if (!empty($selectedIds)) {
			return $this->readCoordinatesByRecords(Vtiger_Mass_Action::getRecordsListFromRequest($this->get('request')));
		}
		return [];
	}

	/**
	 * Get coordinates for all records in the listview.
	 *
	 * @return array
	 */
	public function readAllCoordinatesFromCustomeView()
	{
		$moduleModel = $this->get('srcModuleModel');
		$moduleName = $moduleModel->getName();
		$excludedIds = $this->get('excluded_ids');
		$searchValue = $this->get('search_value');
		$operator = $this->get('operator');
		$groupByField = $this->get('groupBy');
		$coordinatesCenter = $this->get('coordinatesCenter');
		$radius = $this->get('radius');
		$fields = App\Config::module('OpenStreetMap', 'mapPinFields');
		$fields = $fields[$moduleName];
		if (!empty($groupByField)) {
			$fields[] = $groupByField;
			$fieldModel = Vtiger_Field_Model::getInstance($groupByField, $moduleModel);
			$groupByFieldColumn = $fieldModel->get('column');
		}
		$queryGenerator = new App\QueryGenerator($moduleName);
		$queryGenerator->initForCustomViewById($this->get('viewname'));
		$queryGenerator->setFields($fields);
		$queryGenerator->setCustomColumn('u_#__openstreetmap.lat');
		$queryGenerator->setCustomColumn('u_#__openstreetmap.lon');
		$queryGenerator->setCustomColumn('vtiger_crmentity.crmid');
		if ($advancedConditions = $this->get('advancedConditions')) {
			$queryGenerator->setAdvancedConditions($advancedConditions);
		}
		$queryGenerator->addJoin(['LEFT JOIN', 'u_#__openstreetmap', 'u_#__openstreetmap.crmid = vtiger_crmentity.crmid']);
		if (!empty($searchValue) && $operator) {
			$queryGenerator->addCondition($this->get('search_key'), $searchValue, $operator);
		}
		$searchParams = $this->getArray('search_params');
		if (empty($searchParams)) {
			$searchParams = [];
		}
		foreach ($searchParams as $key => $value) {
			if (empty($value)) {
				unset($searchParams[$key]);
			}
		}
		$transformedSearchParams = $queryGenerator->parseBaseSearchParamsToCondition($searchParams);
		$queryGenerator->parseAdvFilter($transformedSearchParams);
		$queryGenerator->addNativeCondition(['u_#__openstreetmap.type' => 'a']);
		if ($excludedIds && !empty($excludedIds) && \is_array($excludedIds) && \count($excludedIds) > 0) {
			$queryGenerator->addNativeCondition(['not in', 'vtiger_crmentity.crmid', $excludedIds]);
		}
		if (!empty($coordinatesCenter) && !empty($radius)) {
			$margins = self::getMargins($coordinatesCenter, $radius);
			$queryGenerator->addNativeCondition([
				'and',
				['<', 'u_#__openstreetmap.lat', $margins['latMax']],
				['>', 'u_#__openstreetmap.lat', $margins['latMin']],
				['<', 'u_#__openstreetmap.lon', $margins['lonMax']],
				['>', 'u_#__openstreetmap.lon', $margins['lonMin']],
			]);
		}
		$dataReader = $queryGenerator->createQuery()->createCommand()->query();
		$coordinates = [];
		while ($row = $dataReader->read()) {
			if (!empty($row['lat'] && !empty($row['lon']))) {
				$coordinates[] = [
					'recordId' => $row['crmid'],
					'lat' => $row['lat'],
					'lon' => $row['lon'],
					'label' => self::getLabelToPopupByArray($row, $moduleName),
					'color' => self::getMarkerColor($row[$groupByFieldColumn]),
				];
			}
		}
		$dataReader->close();
		return $coordinates;
	}

	/**
	 * Get total count of records in the clipboard.
	 *
	 * @return array
	 */
	public function getCachedRecords()
	{
		$db = \App\Db::getInstance();
		$dataReader = (new App\Db\Query())->select(['count' => 'COUNT(*)', 'module_name'])
			->from('u_#__openstreetmap_cache')->where(['user_id' => Users_Privileges_Model::getCurrentUserModel()->getId()])
			->groupBy('module_name')
			->createCommand($db)->query();
		$records = [];
		while ($row = $dataReader->read()) {
			$records[$row['module_name']] = $row['count'];
		}
		$dataReader->close();

		return $records;
	}

	/**
	 * Get coordinates for records from the clipboard.
	 *
	 * @return array
	 */
	public function readCoordinatesCache()
	{
		$modules = $this->get('cache');
		$currentUser = Users_Privileges_Model::getCurrentUserModel();
		$userId = $currentUser->getId();
		$db = \App\Db::getInstance();
		$coordinates = [];
		foreach ($modules as $moduleName) {
			$records = (new App\Db\Query())
				->select(['crmids'])
				->from('u_#__openstreetmap_cache')
				->where(['user_id' => $userId, 'module_name' => $moduleName])
				->createCommand($db)->queryColumn();
			if (!empty($records)) {
				$this->set('srcModuleModel', Vtiger_Module_Model::getInstance($moduleName));
				$coordinates[$moduleName] = $this->readCoordinatesByRecords($records);
			}
		}
		return $coordinates;
	}

	/**
	 * Save records to clipboard.
	 *
	 * @param array $records Array with records id
	 */
	public function saveCache($records)
	{
		$moduleName = $this->get('moduleName');
		$userId = Users_Privileges_Model::getCurrentUserModel()->getId();
		$insertedData = [];
		foreach ($records as $recordId) {
			$insertedData[] = [$userId, $moduleName, $recordId];
		}
		App\Db::getInstance()->createCommand()
			->batchInsert('u_#__openstreetmap_cache', ['user_id', 'module_name', 'crmids'], $insertedData)
			->execute();
	}

	/**
	 * Removes records in the clipbord.
	 */
	public function deleteCache()
	{
		$moduleName = $this->get('moduleName');
		\App\Db::getInstance()->createCommand()
			->delete('u_#__openstreetmap_cache', ['module_name' => $moduleName, 'user_id' => Users_Privileges_Model::getCurrentUserModel()->getId()])
			->execute();
	}

	/**
	 * Function to set all records from module to cache.
	 */
	public function saveAllRecordsToCache()
	{
		$moduleName = $this->get('moduleName');
		$queryGenerator = new App\QueryGenerator($moduleName);
		$queryGenerator->setFields(['id']);
		$dataReader = $queryGenerator->createQuery()->createCommand()->query();
		$records = [];
		while ($row = $dataReader->read()) {
			$records[] = $row['id'];
		}
		$this->deleteCache();
		$this->saveCache($records);

		return $dataReader->count();
	}

	/**
	 * Adding records to the clipboard.
	 *
	 * @param type $record
	 */
	public function addCache($record)
	{
		$moduleName = $this->get('moduleName');
		if (!(new \App\Db\Query())->from('u_#__openstreetmap_cache')
			->where(['crmids' => $record])->exists()) {
			App\Db::getInstance()->createCommand()->insert('u_#__openstreetmap_cache', [
				'module_name' => $moduleName,
				'user_id' => Users_Privileges_Model::getCurrentUserModel()->getId(),
				'crmids' => $record,
			])->execute();
		}
	}
}
