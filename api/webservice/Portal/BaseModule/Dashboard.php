<?php
/**
 * Get dashboard detail class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace Api\Portal\BaseModule;

use Api\Portal\Dashboard as DashboardModel;

/**
 * Action to get widgets for dashboard.
 */
class Dashboard extends \Api\Core\BaseAction
{
	/** @var string[] Allowed request methods */
	public $allowedMethod = ['GET'];

	/**
	 * Get record detail.
	 *
	 * @return array
	 */
	public function get()
	{
		$moduleName = $this->controller->request->getModule();
		if ($this->controller->request->isEmpty('record', true)) {
			$dashBoardId = \Settings_WidgetsManagement_Module_Model::getDefaultDashboard();
		} else {
			$dashBoardId = $this->controller->request->getInteger('record');
		}
		$dashboardInstance = DashboardModel::getInstance($moduleName, $dashBoardId, $this->controller->app['id']);
		return [
			'types' => $dashboardInstance->getTabs(),
			'widgets' => $dashboardInstance->getData()
		];
	}
}
