<?php
/**
 * Colors ajax requests handler class.
 *
 * @package Settings.View
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Slawomir Klos <s.klos@yetiforce.com>
 * @author Adrian Kon <a.kon@yetiforce.com>
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
		$this->exposeMethod('getFieldsColorView');
	}

	/**
	 * Get picklist view.
	 *
	 * @param \App\Request $request
	 */
	public function getPickListView(App\Request $request)
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
			if (\array_key_exists($fieldModel->getName(), $pickListFields) && $fieldModel->getModuleName() === $sourceModule) {
				$picklistValuesName = \App\Fields\Picklist::getValues($fieldModel->getName());
				if ($picklistValuesName) {
					$firstRow = reset($picklistValuesName);
					if (!\array_key_exists('color', $firstRow)) {
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
		$viewer->view('TabPicklistValuesColors.tpl', $qualifiedName);
	}

	/**
	 * Get fields color view.
	 *
	 * @param \App\Request $request
	 */
	public function getFieldsColorView(App\Request $request)
	{
		$sourceModule = $request->getByType('source_module', 2);
		$selectedModuleFields = [];
		if ($sourceModule) {
			$selectedModuleFields = Vtiger_Module_Model::getInstance($sourceModule)->getFields();
		}
		$qualifiedName = $request->getModule(false);
		$viewer = $this->getViewer($request);
		$viewer->assign('SELECTED_MODULE_NAME', $sourceModule);
		$viewer->assign('SELECTED_MODULE_FIELDS', $selectedModuleFields);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedName);
		$viewer->assign('ALL_ACTIVE_MODULES', \vtlib\Functions::getAllModules());
		$viewer->view('TabFieldColors.tpl', $qualifiedName);
	}
}
