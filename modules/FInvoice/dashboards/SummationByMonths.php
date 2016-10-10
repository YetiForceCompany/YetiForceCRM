<?php

/**
 * FInvoice Summation By Months Dashboard Class
 * @package YetiForce.Dashboard
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class FInvoice_SummationByMonths_Dashboard extends Vtiger_IndexAjax_View
{

	private $conditions = false;

	public function process(Vtiger_Request $request)
	{
		$linkId = $request->get('linkid');

		$currentUser = Users_Record_Model::getCurrentUserModel();
		$userId = $currentUser->getId();

		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$widget = Vtiger_Widget_Model::getInstance($linkId, $userId);
		if (!$request->has('owner'))
			$owner = Settings_WidgetsManagement_Module_Model::getDefaultUserId($widget);
		else
			$owner = $request->get('owner');
		$data = $this->getWidgetData($moduleName, $owner);

		$viewer->assign('USERID', $owner);
		$viewer->assign('DATA', $data);
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('CURRENTUSER', $currentUser);
		$accessibleUsers = \includes\fields\Owner::getInstance($moduleName, $currentUser)->getAccessibleUsersForModule();
		$accessibleGroups = \includes\fields\Owner::getInstance($moduleName, $currentUser)->getAccessibleGroupForModule();
		$viewer->assign('ACCESSIBLE_USERS', $accessibleUsers);
		$viewer->assign('ACCESSIBLE_GROUPS', $accessibleGroups);
		$viewer->assign('USER_CONDITIONS', $this->conditions);
		$content = $request->get('content');
		if (!empty($content)) {
			$viewer->view('dashboards/SummationByMonthsContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/SummationByMonths.tpl', $moduleName);
		}
	}

	public function getWidgetData($moduleName, $owner)
	{
		$rawData = $data = $response = $ticks = $years = [];

		$currentUser = Users_Record_Model::getCurrentUserModel();
		$instance = CRMEntity::getInstance($moduleName);
		$securityParameter = $instance->getUserAccessConditionsQuerySR($moduleName, $currentUser);

		$date = date('Y-m-01', strtotime('-23 month', strtotime(date('Y-m-d'))));
		$param = [0, $date];
		$db = PearDatabase::getInstance();
		$sql = 'SELECT Year(`saledate`) as y,  Month(`saledate`) as m,sum(`sum_gross`) as s FROM u_yf_finvoice
					INNER JOIN vtiger_crmentity ON u_yf_finvoice.finvoiceid = vtiger_crmentity.crmid
					WHERE vtiger_crmentity.deleted = ? && saledate > ?';
		if ($securityParameter != '')
			$sql.= $securityParameter;
		if ($owner != 'all') {
			$sql .= ' && vtiger_crmentity.smownerid = ?';
			$param[] = $owner;
		}
		$sql .= ' GROUP BY YEAR(`saledate`), MONTH(`saledate`)';
		$this->conditions = ['saledate', "'$date'", 'g', QueryGenerator::$AND];

		$result = $db->pquery($sql, $param);
		while ($row = $db->getRow($result)) {
			$rawData[$row['y']][] = [$row['m'], (int) $row['s']];
		}
		foreach ($rawData as $y => $raw) {
			$years[] = $y;
		}
		$years = array_values(array_unique($years));
		foreach ($rawData as $y => $raw) {
			$values = [];
			foreach ($raw as $m => &$value) {
				$plus = array_search($y, $years) % 2 == 0 ? 0.45 : 0;
				$value[0] = $value[0] - $plus;
				$values[] = $value;
			}
			$data[] = [
				'data' => $values,
				'bars' => ['order' => (array_search($y, $years) + 1)],
				'label' => vtranslate('LBL_YEAR', $moduleName) . ' ' . $y,
			];
		}
		$response['chart'] = $data;
		$response['ticks'] = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
		return $response;
	}
}
