<?php
/**
 * Actions to widgets.
 *
 * @package Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
\Vtiger_Loader::includeOnce('~/modules/ModTracker/ModTracker.php');
/**
 * Actions to widgets.
 */
class ModTracker_Widget_Action extends \App\Controller\Action
{
	use \App\Controller\ExposeMethod;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('saveUpdatesWidgetConfig');
	}

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		$widget = Vtiger_Widget_Model::getInstanceWithWidgetId($request->getInteger('widgetId'), \App\User::getCurrentUserId());
		if (!$widget->get('active') || ($request->has('trackerActions') && array_diff($request->getArray('trackerActions', 'Integer'), array_keys(ModTracker::getAllActionsTypes())))) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	/**
	 * Save updates widget config.
	 *
	 * @param \App\Request $request
	 */
	public function saveUpdatesWidgetConfig(App\Request $request)
	{
		$actions = $request->has('trackerActions') ? $request->getArray('trackerActions', 'Integer') : [];
		$owner = $request->getByType('owner', 2);
		$historyOwner = $request->getByType('historyOwner', 2);
		$data = ['actions' => $actions, 'owner' => $owner, 'historyOwner' => $historyOwner];
		$result = (bool) \App\Db::getInstance()->createCommand()->update('vtiger_module_dashboard_widgets', ['data' => App\Json::encode($data)],
		['userid' => App\User::getCurrentUserId(), 'id' => $request->getInteger('widgetId')])
			->execute();
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
