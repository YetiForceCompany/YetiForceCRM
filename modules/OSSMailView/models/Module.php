<?php

/**
 * OSSMailView ListView model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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
			'linkicon' => $layoutEditorImagePath,
		];

		return $settingsLinks;
	}

	public function isPermitted($actionName)
	{
		if ($actionName === 'EditView' || $actionName === 'CreateView') {
			return false;
		} else {
			return $this->isActive() && \App\Privilege::isPermitted($this->getName(), $actionName);
		}
	}

	public function getMailCount($owner, $dateFilter)
	{
		if (!$owner) {
			$owner = \App\User::getCurrentUserId();
		} elseif ($owner === 'all') {
			$owner = '';
		}
		$queryGenerator = new App\QueryGenerator('OSSMailView');
		$queryGenerator->setFields(['ossmailview_sendtype']);
		$queryGenerator->setCustomColumn(['count' => new \yii\db\Expression('COUNT(*)')]);
		$queryGenerator->setGroup('ossmailview_sendtype');
		if (!empty($owner)) {
			$queryGenerator->addCondition('assigned_user_id', $owner, 'e');
		}
		if (!empty($dateFilter)) {
			$queryGenerator->addCondition('createdtime', App\Fields\DateTime::formatToDisplay($dateFilter['start']) . ',' . App\Fields\DateTime::formatToDisplay($dateFilter['end']), 'bw');
		}
		$dataReader = $queryGenerator->createQuery()->createCommand()->query();
		$response = [];
		while ($row = $dataReader->read()) {
			$response[] = [$row['ossmailview_sendtype'], $row['count'], \App\Language::translate($row['ossmailview_sendtype'], $this->getName())];
		}
		$dataReader->close();

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
