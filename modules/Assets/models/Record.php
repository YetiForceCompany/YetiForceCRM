<?php

/**
 * Record Class for Assets
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Assets_Record_Model extends Vtiger_Record_Model
{

	protected $privileges = ['editFieldByModal' => true];

	public function getFieldToEditByModal()
	{
		return [
			'addClass' => 'btn-danger',
			'iconClass' => 'glyphicon-modal-window',
			'listViewClass' => 'danger-color',
			'titleTag' => 'LBL_SET_RECORD_STATUS',
			'name' => 'assetstatus',
		];
	}

	public function updateRenewal()
	{
		$value = $this->getRenewalValue();
		if ($value && $this->get('assets_renew') != $value) {
			$this->set('assets_renew', $value);
			$this->set('mode', 'edit');
			$this->save();
		}
	}

	public function getRenewalValue()
	{
		if ($this->isEmpty('product')) {
			return 'PLL_NOT_APPLICABLE';
		}
		$productsRecordModel = Vtiger_Record_Model::getInstanceById($this->get('product'), 'Products');
		$renewable = $productsRecordModel->get('renewable');
		if (!$renewable) {
			return 'PLL_NOT_APPLICABLE';
		}
		if (!$this->isEmpty('renewalinvoice')) {
			return 'PLL_WAITING_FOR_VERIFICATION';
		}
		$dateInService = strtotime($this->get('dateinservice'));
		$dateRenewable = strtotime(AppConfig::module('Assets', 'RENEWAL_TIME'), $dateInService);
		if ($dateRenewable > time()) {
			return 'PLL_PLANNED';
		}
		if (strtotime('+1 month', $dateInService) < time()) {
			return 'PLL_WAITING_FOR_VERIFICATION';
		}
		return 'PLL_WAITING_FOR_RENEWAL';
	}
}
