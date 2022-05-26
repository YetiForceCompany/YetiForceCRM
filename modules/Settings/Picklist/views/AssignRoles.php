<?php
/**
 * Assign role for picklist values.
 *
 * @package Settings.View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Assign role for picklist values class.
 */
class Settings_Picklist_AssignRoles_View extends \App\Controller\ModalSettings
{
	/** {@inheritdoc} */
	protected $pageTitle = 'LBL_ASSIGN_VALUE';
	/** {@inheritdoc} */
	public $modalIcon = 'yfi-roles';
	/** {@inheritdoc} */
	public $modalSize = 'modal-md';

	/** {@inheritdoc} */
	public function getPageTitle(App\Request $request)
	{
		$moduleName = $request->getModule(false);
		$sourceModule = $request->getByType('source_module', \App\Purifier::ALNUM);
		$pickFieldName = $request->getByType('picklistName', \App\Purifier::ALNUM);
		$fieldModel = Settings_Picklist_Field_Model::getInstance($pickFieldName, Vtiger_Module_Model::getInstance($sourceModule));

		return \App\Language::translate($this->pageTitle, $moduleName) . ': ' . \App\Language::translate($fieldModel->getFieldLabel(), $fieldModel->getModuleName());
	}

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		parent::checkPermission($request);
		$sourceModule = $request->getByType('source_module', \App\Purifier::ALNUM);
		$pickFieldName = $request->getByType('picklistName', \App\Purifier::ALNUM);
		if (!Settings_Picklist_Field_Model::getInstance($pickFieldName, Vtiger_Module_Model::getInstance($sourceModule))->isEditable()) {
			throw new \App\Exceptions\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$sourceModule = $request->getByType('source_module', \App\Purifier::ALNUM);
		$pickFieldName = $request->getByType('picklistName', \App\Purifier::ALNUM);
		$qualifiedName = $request->getModule(false);
		$fieldModel = Settings_Picklist_Field_Model::getInstance($pickFieldName, Vtiger_Module_Model::getInstance($sourceModule));

		$viewer = $this->getViewer($request);
		$viewer->assign('ROLES_LIST', Settings_Roles_Record_Model::getAll());
		$viewer->assign('SELECTED_PICKLISTFIELD_ALL_VALUES', \App\Fields\Picklist::getValuesName($fieldModel->getName()));
		$viewer->assign('SOURCE_MODULE', $sourceModule);
		$viewer->assign('SOURCE_MODULE_NAME', $sourceModule);
		$viewer->assign('FIELD_MODEL', $fieldModel);
		$viewer->assign('MODULE_NAME', $request->getModule());
		$viewer->view('AssignRoles.tpl', $qualifiedName);
	}
}
