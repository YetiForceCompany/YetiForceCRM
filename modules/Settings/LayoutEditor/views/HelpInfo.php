<?php
/**
 * Context help.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Help info View Class.
 */
class Settings_LayoutEditor_HelpInfo_View extends \App\Controller\Modal
{
	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(\App\Request $request)
	{
		if (!\App\User::getCurrentUserModel()->isAdmin()) {
			throw new \App\Exceptions\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function preProcessAjax(\App\Request $request)
	{
		$moduleName = $request->getModule(false);
		$this->modalIcon = 'fas fa-info-circle';
		$this->pageTitle = App\Language::translate('LBL_CONTEXT_HELP', $moduleName);
		parent::preProcessAjax($request);
	}

	/**
	 * Proccess view.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$fieldModel = \Vtiger_Field_Model::getInstanceFromFieldId($request->getInteger('field'));
		$qualifiedModuleName = $request->getModule(false);
		$viewer = $this->getViewer($request);
		$viewer->assign('HELP_INFO_VIEWS', \App\Field::HELP_INFO_VIEWS);
		$viewer->assign('FIELD_MODEL', $fieldModel);
		$viewer->assign('LANG_DEFAULT', \App\Language::getLanguage());
		$viewer->assign('LANGUAGES', \App\Language::getAll());
		$viewer->assign('SELECTED_VIEWS', ['Edit', 'Detail', 'QuickCreateAjax']);
		$viewer->assign('DEFAULT_VALUE', $request->getByType('defaultValue', 'Text'));
		$viewer->view('HelpInfo.tpl', $qualifiedModuleName);
	}

	/**
	 * {@inheritdoc}
	 */
	public function postProcessAjax(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule(false);
		if (!$request->getBoolean('onlyBody')) {
			$viewer->assign('MODULE', $moduleName);
			$viewer->assign('BTN_SUCCESS', 'LBL_SAVE');
			$viewer->assign('BTN_DANGER', 'LBL_CLOSE');
			$viewer->view('Modals/HelpInfoFooter.tpl', $moduleName);
		}
	}
}
