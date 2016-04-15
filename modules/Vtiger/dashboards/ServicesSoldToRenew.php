<?php

/**
 * ServicesSoldToRenew Dashboard Class
 * @package YetiForce.Dashboard
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_ServicesSoldToRenew_Dashboard extends Vtiger_ProductsSoldToRenew_Dashboard
{

	public function setData($data)
	{
		if (empty($data['orderby'])) {
			$data['orderby'] = 'productname';
			$data['sortorder'] = 'asc';
		}
		$this->data = $data;
	}

	public function getTargetModule()
	{
		return 'OSSSoldServices';
	}

	public function getTargetFields()
	{
		return ['id', 'productname', 'parent_id', 'dateinservice'];
	}

	public function getFieldNameToSecondButton()
	{
		return 'osssoldservices_renew';
	}

	public function getConditions()
	{
		$where = ' AND ssservicesstatus = ? AND osssoldservices_renew NOT IN (?, ?)';
		$params = ['PLL_ACCEPTED', 'PLL_RENEWED', 'PLL_NOT_RENEWED'];
		return ['where' => $where, 'params' => $params];
	}
}
