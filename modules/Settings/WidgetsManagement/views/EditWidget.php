<?php
/**
 * Modal for widget edit view.
 *
 * @package   Settings.View
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Widget edit view - class.
 */
class Settings_WidgetsManagement_EditWidget_View extends \App\Controller\ModalSettings
{
	/** {@inheritdoc} */
	public $modalIcon = 'adminIcon-widgets-configuration';

	/** {@inheritdoc} */
	public $modalSize = 'modal-md';

	/** {@inheritdoc} */
	public $showFooter = false;

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		parent::checkPermission($request);
		if ($request->isEmpty('widgetId', true) && !$request->isEmpty('blockId', true) && $linkData = \vtlib\Link::getLinkData($request->getInteger('linkId'))) {
			$linkData['blockid'] = $request->getInteger('blockId');
			$this->widgetModel = \Vtiger_Widget_Model::getInstanceFromValues($linkData);
		} else {
			$this->widgetModel = \Vtiger_Widget_Model::getInstanceWithTemplateId($request->getInteger('widgetId'));
		}

		// if (!Settings_LayoutEditor_Field_Model::getInstance($request->getInteger('fieldId'))->isEditable()) {
		// 	throw new \App\Exceptions\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		// }
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPageTitle(App\Request $request)
	{
		// $moduleName = $request->getModule(false);
		// if (isset($this->pageTitle)) {
		// 	$pageTitle = \App\Language::translate($this->pageTitle, $moduleName);
		// } else {
		// 	$pageTitle = \App\Language::translate($request->getModule(), $moduleName);
		// }
		// $widgetModel = \Vtiger_Widget_Model::getInstanceWithTemplateId($request->getInteger('widgetId'));
		// $title = $widgetModel->getTitle();

		return \App\Language::translate($this->widgetModel->getTitle(), \App\Module::getModuleName($this->widgetModel->get('tabid')));
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		// $widgetModel = \Vtiger_Widget_Model::getInstanceWithTemplateId($request->getInteger('widgetId'));

		$viewer = $this->getViewer($request);
		$viewer->assign('WIDGET_MODEL', $this->widgetModel);
		$viewer->assign('BTN_SUCCESS', $this->successBtn);
		$viewer->assign('BTN_SUCCESS_ICON', $this->successBtnIcon);
		$viewer->assign('BTN_DANGER', $this->dangerBtn);
		$viewer->assign('FOOTER_CLASS', $this->footerClass);
		$viewer->view('EditWidget.tpl', $qualifiedModuleName);
	}

	// /** {@inheritdoc} */
	// public function preProcessAjax(App\Request $request)
	// {
	// 	$moduleName = $request->getModule(false);
	// 	$this->modalIcon = 'fas fa-info-circle';
	// 	$this->pageTitle = App\Language::translate('LBL_CONTEXT_HELP', $moduleName);
	// 	parent::preProcessAjax($request);
	// }

	// /**
	//  * Process view.
	//  *
	//  * @param \App\Request $request
	//  */
	// public function process(App\Request $request)
	// {
	// 	$fieldModel = \Vtiger_Field_Model::getInstanceFromFieldId($request->getInteger('field'));
	// 	$qualifiedModuleName = $request->getModule(false);
	// 	$viewer = $this->getViewer($request);
	// 	$viewer->assign('HELP_INFO_VIEWS', \App\Field::HELP_INFO_VIEWS);
	// 	$viewer->assign('FIELD_MODEL', $fieldModel);
	// 	$viewer->assign('LANG_DEFAULT', \App\Language::getLanguage());
	// 	$viewer->assign('LANGUAGES', \App\Language::getAll());
	// 	$viewer->assign('SELECTED_VIEWS', ['Edit', 'Detail', 'QuickCreateAjax']);
	// 	$viewer->assign('DEFAULT_VALUE', $request->getByType('defaultValue', 'Text'));
	// 	$viewer->view('HelpInfo.tpl', $qualifiedModuleName);
	// }

	// /** {@inheritdoc} */
	// public function postProcessAjax(App\Request $request)
	// {
	// 	$viewer = $this->getViewer($request);
	// 	$moduleName = $request->getModule(false);
	// 	if (!$request->getBoolean('onlyBody')) {
	// 		$viewer->assign('MODULE', $moduleName);
	// 		$viewer->assign('BTN_SUCCESS', 'LBL_SAVE');
	// 		$viewer->assign('BTN_DANGER', 'LBL_CLOSE');
	// 		$viewer->view('Modals/HelpInfoFooter.tpl', $moduleName);
	// 	}
	// }
}
