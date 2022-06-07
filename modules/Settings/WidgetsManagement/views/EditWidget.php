<?php
/**
 * Modal for widget edit view.
 *
 * @package   Settings.View
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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
	}

	/** {@inheritdoc} */
	public function getPageTitle(App\Request $request)
	{
		return $this->widgetModel->getTranslatedTitle();
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);

		$viewer = $this->getViewer($request);
		$viewer->assign('WIDGET_MODEL', $this->widgetModel);
		$viewer->assign('BTN_SUCCESS', $this->successBtn);
		$viewer->assign('BTN_SUCCESS_ICON', $this->successBtnIcon);
		$viewer->assign('BTN_DANGER', $this->dangerBtn);
		$viewer->assign('FOOTER_CLASS', $this->footerClass);
		$viewer->view('EditWidget.tpl', $qualifiedModuleName);
	}
}
