<?php
/**
 * Actions file to widgets.
 *
 * @package Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */

/**
 * Actions class to widgets.
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
		$this->exposeMethod('updateWidgetConfig');
		$this->exposeMethod('positions');
		$this->exposeMethod('clear');
	}

	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(App\Request $request)
	{
		$moduleName = $request->getModule();
		$mode = $request->getMode();
		if ($request->has('widgetid')) {
			$widget = Vtiger_Widget_Model::getInstanceWithWidgetId($request->getInteger('widgetid'), \App\User::getCurrentUserId());
		} else {
			if ('add' !== $mode) {
				$widget = Vtiger_Widget_Model::getInstance($request->getInteger('linkid'), \App\User::getCurrentUserId());
			}
		}
		if (('updateWidgetConfig' === $mode && $request->has('widgetid') && $widget->get('active'))
			|| ('remove' === $mode && !$widget->isDefault() && \App\Privilege::isPermitted($moduleName))
			|| (('positions' === $mode || 'clear' === $mode) && \App\Privilege::isPermitted($moduleName))
			|| 'add' === $mode) {
			return true;
		}
		throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED');
	}

	/**
	 * Remove widget.
	 *
	 * @param \App\Request $request
	 */
	public function remove(App\Request $request)
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
			'deleteFromList' => $widget->isDeletable(),
		]);
		$response->emit();
	}

	/**
	 * Function to add widget.
	 *
	 * @param \App\Request $request
	 */
	public function add(App\Request $request)
	{
		$moduleName = $request->getByType('sourceModule', \App\Purifier::ALNUM);
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
			'skip_year' => 'Integer',
			'date_fields' => 'Integer',
			'default_owner' => 'Standard',
			'dashboardId' => 'Integer',
			'limit' => 'Integer',
			'cache' => 'Integer',
			'default_date' => 'Standard',
			'sortOrder' => 'Text',
		]);
		if (!\is_array($data) || !$data) {
			$result = ['success' => false, 'message' => \App\Language::translate('LBL_INVALID_DATA', $moduleName)];
		} elseif (!Vtiger_Widget_Model::getInstanceFromValues(array_merge($data, \vtlib\Link::getLinkData($request->getInteger('linkid'))))->isCreatable()) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED');
		} else {
			$data['linkid'] = $request->getInteger('linkid');
			$widgetsManagementModel = new Settings_WidgetsManagement_Module_Model();
			$result = $widgetsManagementModel->addWidget($data, $moduleName, $request->getBoolean('addToUser'));
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
	public function removeWidgetFromList(App\Request $request)
	{
		Vtiger_Widget_Model::removeWidgetFromList($request->getInteger('widgetid'));
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}

	/**
	 * Save updates widget config.
	 *
	 * @param \App\Request $request
	 */
	public function updateWidgetConfig(App\Request $request)
	{
		$moduleName = $request->getModule();
		$widgetId = $request->getInteger('widgetid');
		$widget = Vtiger_Widget_Model::getInstanceWithWidgetId($widgetId, \App\User::getCurrentUserId());
		$className = Vtiger_Loader::getComponentClassName('Dashboard', $widget->get('linklabel'), $moduleName);
		$data = $request->getArray('widgetData', \App\Purifier::TEXT);
		$instance = new $className();
		$instance->setWidgetData($widget, $data);
		$result = (bool) \App\Db::getInstance()->createCommand()->update('vtiger_module_dashboard_widgets', ['data' => $widget->get('data')],
		['userid' => App\User::getCurrentUserId(), 'id' => $widgetId])
			->execute();
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	/**
	 * Save positions.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function positions(App\Request $request): void
	{
		$currentUserId = App\User::getCurrentUserId();
		if ($positionsMap = $request->getMultiDimensionArray('position', [['row' => 'Integer',	'col' => 'Integer']])) {
			foreach ($positionsMap as $id => $position) {
				[$linkId, $widgetId] = array_pad(explode('-', $id), 2, false);
				if ($widgetId) {
					Vtiger_Widget_Model::updateWidgetPosition($position, null, (int) $widgetId, $currentUserId);
				} else {
					Vtiger_Widget_Model::updateWidgetPosition($position, (int) $linkId, null, $currentUserId);
				}
			}
		}
		if ($sizesMap = $request->getMultiDimensionArray('size', [['width' => 'Integer', 'height' => 'Integer']])) {
			foreach ($sizesMap as $id => $size) {
				[$linkId, $widgetId] = array_pad(explode('-', $id), 2, false);
				if ($widgetId) {
					Vtiger_Widget_Model::updateWidgetSize($size, null, (int) $widgetId, $currentUserId);
				} else {
					Vtiger_Widget_Model::updateWidgetSize($size, (int) $linkId, null, $currentUserId);
				}
			}
		}

		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}

	/**
	 * Clear configuration of widgets for this device.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function clear(App\Request $request): void
	{
		Vtiger_Widget_Model::clearDeviceConf(Vtiger_Widget_Model::getDashboardId($request));
		header("location: index.php?module={$request->getModule()}&view=DashBoard");
	}
}
