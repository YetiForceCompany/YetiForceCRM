<?php

/**
 * Record Class for OSSSoldServices
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class OSSSoldServices_Record_Model extends Vtiger_Record_Model
{

	protected $privileges = ['editFieldByModal' => true];

	public function getFieldToEditByModal()
	{
		return [
			'addClass' => 'btn-danger',
			'iconClass' => 'glyphicon-modal-window',
			'listViewClass' => 'danger-color',
			'titleTag' => 'LBL_SET_RECORD_STATUS',
			'name' => 'ssservicesstatus',
		];
	}
	
	public function updateRenewal()
	{
		$value = $this->getRenewalValue();
		if ($value) {
			$this->set('osssoldservices_renew', $value);
			$this->set('mode', 'edit');
			$this->save();
		}
	}

	public function getRenewalValue()
	{
		if ($this->has('serviceid') == false) {
			return 'PLL_NOT_APPLICABLE';
		}
		$productsRecordModel = Vtiger_Record_Model::getInstanceById($this->get('serviceid'), 'Services');
		$renewable = $productsRecordModel->get('renewable');
		if (!$renewable) {
			return 'PLL_NOT_APPLICABLE';
		}
		if (!$this->isEmpty('renewalinvoice')) {
			return 'PLL_RENEWED';
		}
		$dateRenewable = strtotime(AppConfig::module('OSSSoldServices', 'RENEWAL_TIME'), strtotime($this->get('dateinservice')));
		if ($dateRenewable > time()) {
			return 'PLL_PLANNED';
		}
		if (strtotime('+1 month', $dateRenewable) > time()) {
			return 'PLL_WAITING_FOR_RENEWAL';
		}
		return 'PLL_NOT_RENEWED';
	}
}
