<?php
/**
 * Colors Ajax requests handler class
 * @package YetiForce.Users
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Sławomir Kłos <s.klos@yetiforce.com>
 */

/**
 * Colors Ajax requests handler class
 */
class Settings_Users_ColorsAjax_View extends Settings_Vtiger_IndexAjax_View
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getPickListView');
	}

	/**
	 * Process client request
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$mode = $request->get('mode');
		if ($this->isMethodExposed($mode)) {
			$this->invokeExposedMethod($mode, $request);
		}
	}

	/**
	 * Get picklist view
	 * @param \App\Request $request
	 */
	public function getPickListView(\App\Request $request)
	{
		$sourceModule = $request->get('source_module');
		$pickFieldId = $request->getInteger('pickListFieldId');
		if ($sourceModule) {
			$moduleModel = Settings_Picklist_Module_Model::getInstance($sourceModule);
			$pickListFields = $moduleModel->getFieldsByType(['picklist', 'multipicklist']);
		}
		$noColumn = false;
		if ($pickFieldId) {
			$fieldModel = Settings_Picklist_Field_Model::getInstance($pickFieldId);
			if (key_exists($fieldModel->getName(), $pickListFields) && $fieldModel->getModuleName() === $sourceModule) {
				$selectedFieldAllPickListValues = \App\Colors::getPickListFieldValues($fieldModel->getName());
				if ($selectedFieldAllPickListValues) {
					$firstRow = reset($selectedFieldAllPickListValues);
					if (!key_exists('color', $firstRow)) {
						$noColumn = true;
					}
				}
			}
		}
		$qualifiedName = $request->getModule(false);

		$viewer = $this->getViewer($request);
		$viewer->assign('COLOR_NO_COLUMN', $noColumn);
		$viewer->assign('PICKLIST_FIELDS', $pickListFields);
		$viewer->assign('SELECTED_PICKLIST_FIELDMODEL', $fieldModel);
		$viewer->assign('SELECTED_PICKLIST_FIELD_ID', $pickFieldId);
		$viewer->assign('SELECTED_MODULE_NAME', $sourceModule);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedName);
		$viewer->assign('SELECTED_PICKLISTFIELD_ALL_VALUES', $selectedFieldAllPickListValues);
		$viewer->view('ColorsPickListView.tpl', $qualifiedName);
	}
}
