<?php
/**
 * Modal for widget list view.
 *
 * @package   Settings.View
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Widget list view - class.
 */
class Settings_WidgetsManagement_WidgetListModal_View extends \App\Controller\ModalSettings
{
	/** {@inheritdoc} */
	public $modalIcon = 'adminIcon-widgets-configuration';

	/** {@inheritdoc} */
	public $modalSize = 'modal-md';

	/** {@inheritdoc} */
	public $successBtn = 'BTN_NEXT';

	/** {@inheritdoc} */
	public $successBtnIcon = 'fas fa-caret-right';

	/** {@inheritdoc} */
	protected $pageTitle = 'LBL_CREATE_CUSTOM_FIELD';

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$blockId = $request->getInteger('blockId');
		$widgetsManagementModel = new Settings_WidgetsManagement_Module_Model();
		$widgets = $widgetsManagementModel->getPredefinedWidgetsByBlock($blockId);

		$viewer = $this->getViewer($request);
		$viewer->assign('WIDGETS', $widgets);
		$viewer->view('WidgetListModal.tpl', $qualifiedModuleName);
	}
}
