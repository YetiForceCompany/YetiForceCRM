<?php
/**
 * Actions to widgets.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */

/**
 * Actions to widgets.
 */
class Vtiger_Widget_Action extends \App\Controller\Action
{
	use \App\Controller\ExposeMethod;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('add');
		$this->exposeMethod('remove');
		$this->exposeMethod('removeWidgetFromList');
	}

	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$mode = $request->getMode();
		if ($request->has('widgetid')) {
			$widget = Vtiger_Widget_Model::getInstanceWithWidgetId($request->getInteger('widgetid'), \App\User::getCurrentUserId());
			$label = $widget->get('linklabel');
		} else {
			if ($mode === 'add') {
				$linkDdata = \vtlib\Link::getLinkData($request->getInteger('linkid'));
				$label = $linkDdata['linklabel'];
			} else {
				$widget = Vtiger_Widget_Model::getInstance($request->getInteger('linkid'), \App\User::getCurrentUserId());
				$label = $widget->get('linklabel');
			}
		}
		if (($mode === 'remove' && !$widget->isDefault() && \App\Privilege::isPermitted($moduleName)) ||
			($label === 'Mini List' && \App\Privilege::isPermitted($moduleName, 'CreateDashboardFilter')) ||
			($label === 'ChartFilter' && \App\Privilege::isPermitted($moduleName, 'CreateDashboardChartFilter'))) {
			return true;
		}
		throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED');
	}

	/**
	 * Remove widget.
	 *
	 * @param \App\Request $request
	 */
	public function remove(\App\Request $request)
	{
		$linkId = $request->getInteger('linkid');
		$response = new Vtiger_Response();

		if ($request->has('widgetid')) {
			$widget = Vtiger_Widget_Model::getInstanceWithWidgetId($request->getInteger('widgetid'), \App\User::getCurrentUserId());
		} else {
			$widget = Vtiger_Widget_Model::getInstance($linkId, \App\User::getCurrentUserId());
		}
		$widget->remove('hide');
		$response->setResult(['linkid' => $linkId,
			'name' => $widget->getName(),
			'url' => $widget->getUrl(),
			'title' => \App\Language::translate($widget->getTitle(), $request->getModule()),
			'id' => $widget->get('id'),
			'deleteFromList' => $widget->get('deleteFromList'),
		]);
		$response->emit();
	}

	/**
	 * Function to add widget.
	 *
	 * @param \App\Request $request
	 */
	public function add(\App\Request $request)
	{
		$data = $request->getMultiDimensionArray('form', [
			'data' => 'Text',
			'blockid' => 'Integer',
			'linkid' => 'Integer',
			'label' => 'Text',
			'title' => 'Text',
			'name' => 'Text',
			'filterid' => 'Text',
			'isdefault' => 'Integer',
			'height' => 'Integer',
			'width' => 'Integer',
			'owners_all' => [
				'Standard',
				'Standard',
				'Standard',
				'Standard',
			],
			'default_owner' => 'Standard',
			'dashboardId' => 'Integer',
			'limit' => 'Integer',
			'cache' => 'Integer',
			'default_date' => 'Standard'
		]);
		$moduleName = $request->getByType('sourceModule', 2);
		$addToUser = $request->getBoolean('addToUser');
		$linkId = $request->getInteger('linkid');
		if (!is_array($data) || !$data) {
			$result = ['success' => false, 'message' => \App\Language::translate('LBL_INVALID_DATA', $moduleName)];
		} else {
			$data['linkid'] = $linkId;
			$widgetsManagementModel = new Settings_WidgetsManagement_Module_Model();
			$result = $widgetsManagementModel->addWidget($data, $moduleName, $addToUser);
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	/**
	 * Remove widget from list.
	 *
	 * @param \App\Request $request
	 */
	public function removeWidgetFromList(\App\Request $request)
	{
		Vtiger_Widget_Model::removeWidgetFromList($request->getInteger('widgetid'));
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}
}
