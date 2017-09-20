<?php

/**
 * OSSMailView ListView model class
 * @package YetiForce.Model
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class OSSMailView_Module_Model extends Vtiger_Module_Model
{

	public function getSettingLinks()
	{
		$settingsLinks = parent::getSettingLinks();
		$layoutEditorImagePath = Vtiger_Theme::getImagePath('LayoutEditor.gif');
		$fieldId = (new App\Db\Query())->select(['fieldid'])
			->from('vtiger_settings_field')
			->where(['name' => 'OSSMailView', 'description' => 'OSSMailView'])
			->scalar();
		$settingsLinks[] = [
			'linktype' => 'LISTVIEWSETTING',
			'linklabel' => 'LBL_MODULE_CONFIGURATION',
			'linkurl' => 'index.php?module=OSSMailView&parent=Settings&view=index&block=4&fieldid=' . $fieldId,
			'linkicon' => $layoutEditorImagePath
		];
		return $settingsLinks;
	}

	public function isPermitted($actionName)
	{
		if ($actionName === 'EditView' || $actionName === 'CreateView') {
			return false;
		} else {
			return ($this->isActive() && \App\Privilege::isPermitted($this->getName(), $actionName));
		}
	}

	public function getMailCount($owner, $dateFilter)
	{
		$db = PearDatabase::getInstance();

		if (!$owner) {
			$currenUserModel = Users_Record_Model::getCurrentUserModel();
			$owner = $currenUserModel->getId();
		} else if ($owner === 'all') {
			$owner = '';
		}

		$params = [];
		if (!empty($owner)) {
			$ownerSql = ' && smownerid = ? ';
			$params[] = $owner;
		}
		if (!empty($dateFilter)) {
			$dateFilterSql = ' && createdtime BETWEEN ? AND ? ';
			$params[] = $dateFilter['start'] . ' 00:00:00';
			$params[] = $dateFilter['end'] . ' 23:59:59';
		}

		$result = $db->pquery('SELECT COUNT(*) count, ossmailview_sendtype FROM vtiger_ossmailview
						INNER JOIN vtiger_crmentity ON vtiger_ossmailview.ossmailviewid = vtiger_crmentity.crmid
						AND deleted = 0 ' . Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName()) . $ownerSql . $dateFilterSql . ' GROUP BY ossmailview_sendtype', $params);

		$response = [];

		$numRowsCount = $db->numRows($result);
		for ($i = 0; $i < $numRowsCount; $i++) {
			$saleStage = $db->queryResult($result, $i, 'ossmailview_sendtype');
			$response[$i][0] = $saleStage;
			$response[$i][1] = $db->queryResult($result, $i, 'count');
			$response[$i][2] = \App\Language::translate($saleStage, $this->getName());
		}
		return $response;
	}

	public function getPreviewViewUrl($id)
	{
		return 'index.php?module=' . $this->get('name') . '&view=Preview&record=' . $id;
	}

	public function isQuickCreateSupported()
	{
		return false;
	}
}
