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
class Settings_LayoutEditor_HelpInfo_View extends Settings_Vtiger_BasicModal_View
{
	/**
	 * Proccess view.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$fieldModel = \Vtiger_Field_Model::getInstanceFromFieldId($request->getInteger('field'));
		$this->preProcess($request);
		$qualifiedModuleName = $request->getModule(false);
		$viewer = $this->getViewer($request);
		$viewer->assign('HELP_INFO_VIEWS', \App\Field::HELP_INFO_VIEWS);
		$viewer->assign('FIELD_MODEL', $fieldModel);
		$viewer->assign('LANG_DEFAULT', \App\Language::getLanguage());
		$viewer->assign('LANGUAGES', \App\Language::getAll());
		$viewer->assign('SELECTED_VIEWS', ['Edit', 'Detail', 'QuickCreateAjax']);
		$viewer->assign('DEFAULT_VALUE', $request->get('defaultValue'));
		$viewer->view('HelpInfo.tpl', $qualifiedModuleName);
		$this->postProcess($request);
	}
}
