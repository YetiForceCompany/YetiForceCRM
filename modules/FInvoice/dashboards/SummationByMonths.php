<?php

/**
 * FInvoice Summation By Months Dashboard Class
 * @package YetiForce.Dashboard
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class FInvoice_SummationByMonths_Dashboard extends Vtiger_IndexAjax_View
{

	public function process(Vtiger_Request $request)
	{
		$linkId = $request->get('linkid');
		$owner = $request->get('owner');

		$currentUser = Users_Record_Model::getCurrentUserModel();
		$userId = $currentUser->getId();

		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$widget = Vtiger_Widget_Model::getInstance($linkId, $userId);
		$data = $this->getWidgetData($moduleName, $owner);

		$viewer->assign('USERID', $owner);
		$viewer->assign('DATA', $data);
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('CURRENTUSER', $currentUser);
		$accessibleUsers = $currentUser->getAccessibleUsersForModule($moduleName);
		$accessibleGroups = $currentUser->getAccessibleGroupForModule($moduleName);
		$viewer->assign('ACCESSIBLE_USERS', $accessibleUsers);
		$viewer->assign('ACCESSIBLE_GROUPS', $accessibleGroups);
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

		$date = date('Y-m-01', strtotime('-23 month', strtotime(date('Y-m-d'))) ); 
		$param = [0,$date];
		$db = PearDatabase::getInstance();
		$sql = 'SELECT Year(`saledate`) as y,  Month(`saledate`) as m,sum(`sum_gross`) as s FROM u_yf_finvoice
					INNER JOIN vtiger_crmentity ON u_yf_finvoice.finvoiceid = vtiger_crmentity.crmid
					WHERE vtiger_crmentity.deleted = ? AND saledate > ?';
		if ($securityParameter != '')
			$sql.= $securityParameter;
		if ($owner != 'all') {
			$sql .= ' AND vtiger_crmentity.smownerid = ?';
			$param[] = $owner;
		}
		$sql .= ' GROUP BY YEAR(`saledate`), MONTH(`saledate`)';
		
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
