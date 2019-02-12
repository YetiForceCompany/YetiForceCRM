<?php
/**
 * Colors ajax requests handler class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Sławomir Kłos <s.klos@yetiforce.com>
 */

/**
 * Colors Ajax requests handler class.
 */
class Settings_Colors_IndexAjax_View extends Settings_Vtiger_IndexAjax_View
{
	use \App\Controller\ExposeMethod;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getPickListView');
	}

	/**
	 * Get picklist view.
	 *
	 * @param \App\Request $request
	 */
	public function getPickListView(\App\Request $request)
	{
		$pickListFields = $picklistValuesName = [];
		$sourceModule = $request->getByType('source_module', 2);
		$fieldId = $request->getInteger('fieldId');
		if ($sourceModule) {
			$pickListFields = \App\Colors::getPicklistFieldsByModule($sourceModule);
		}
		$noColumn = false;
		if ($fieldId) {
			$fieldModel = Vtiger_Field_Model::getInstanceFromFieldId($fieldId);
			if (array_key_exists($fieldModel->getName(), $pickListFields) && $fieldModel->getModuleName() === $sourceModule) {
				$picklistValuesName = \App\Fields\Picklist::getValues($fieldModel->getName());
				if ($picklistValuesName) {
					$firstRow = reset($picklistValuesName);
					if (!array_key_exists('color', $firstRow)) {
						$noColumn = true;
					}
				}
			}
		}
		$qualifiedName = $request->getModule(false);

		$viewer = $this->getViewer($request);
		$viewer->assign('COLOR_NO_COLUMN', $noColumn);
		$viewer->assign('PICKLIST_FIELDS', $pickListFields);
		$viewer->assign('SELECTED_PICKLIST_FIELDMODEL', isset($fieldModel));
		$viewer->assign('SELECTED_PICKLIST_FIELD_ID', $fieldId);
		$viewer->assign('SELECTED_MODULE_NAME', $sourceModule);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedName);
		$viewer->assign('SELECTED_PICKLISTFIELD_ALL_VALUES', $picklistValuesName);
		$viewer->view('TabFieldColors.tpl', $qualifiedName);
	}
}
