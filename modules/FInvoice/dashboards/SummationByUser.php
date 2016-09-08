<?php

/**
 * FInvoice Summation By User Dashboard Class
 * @package YetiForce.Dashboard
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class FInvoice_SummationByUser_Dashboard extends Vtiger_IndexAjax_View
{

	public function process(Vtiger_Request $request)
	{
		$linkId = $request->get('linkid');


		$currentUser = Users_Record_Model::getCurrentUserModel();
		$userId = $currentUser->getId();
		if ($request->has('time')) {
			$time = $request->get('time');
		} else {
			$time['start'] = date('Y-m-01');
			$time['end'] = date('Y-m-t');
		}
		// date parameters passed, convert them to YYYY-mm-dd
		$time['start'] = vtlib\Functions::currentUserDisplayDate($time['start']);
		$time['end'] = vtlib\Functions::currentUserDisplayDate($time['end']);
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$widget = Vtiger_Widget_Model::getInstance($linkId, $userId);
		$param = \includes\utils\Json::decode($widget->get('data'));
		$data = $this->getWidgetData($moduleName, $param, $time);

		$viewer->assign('DTIME', $time);
		$viewer->assign('DATA', $data);
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('PARAM', $param);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('CURRENTUSER', $currentUser);
		$content = $request->get('content');
		if (!empty($content)) {
			$viewer->view('dashboards/SummationByUserContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/SummationByUser.tpl', $moduleName);
		}
	}

	public function getWidgetData($moduleName, $widgetParam, $time)
	{
		$rawData = $response = $ticks = [];

		$currentUser = Users_Record_Model::getCurrentUserModel();
		$instance = CRMEntity::getInstance($moduleName);
		$securityParameter = $instance->getUserAccessConditionsQuerySR($moduleName, $currentUser);

		$param = [0, $time['start'], $time['end']];
		$db = PearDatabase::getInstance();
		$sql = 'SELECT vtiger_crmentity.smownerid as o,sum(`sum_gross`) as s FROM u_yf_finvoice
					INNER JOIN vtiger_crmentity ON u_yf_finvoice.finvoiceid = vtiger_crmentity.crmid
					WHERE vtiger_crmentity.deleted = ? && u_yf_finvoice.saledate >= ? && u_yf_finvoice.saledate <= ?';
		if ($securityParameter != '')
			$sql.= $securityParameter;
		$sql .= ' GROUP BY smownerid ORDER BY s DESC';

		$result = $db->pquery($sql, $param);
		$i = 0;

		while ($row = $db->getRow($result)) {
			if ($row['s'] == 0) {
				continue;
			}
			$i++;
			$color = '#EDC240';
			if ($currentUser->getId() == $row['o']) {
				$color = '#4979aa';
			}
			$owner = vtlib\Functions::getOwnerRecordLabel($row['o']);
			$rawData[] = [
				'data' => [[$i, (int) $row['s']]],
				'label' => $owner,
				'color' => $color
			];
		}
		$response['ticks'] = $ticks;
		$response['chart'] = $rawData;
		return $response;
	}
}
