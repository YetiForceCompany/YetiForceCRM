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
			'name' => 'assets_renew',
		];
	}

	public function updateRenewal()
	{
		$value = $this->getRenewalValue();
		if ($value && $this->get('assets_renew') != $value) {
			$this->set('assets_renew', $value);
			$this->save();
		}
	}

	public function getRenewalValue()
	{
		if ($this->isEmpty('product')) {
			return 'PLL_NOT_APPLICABLE_VERIFICATION';
		}
		$productsRecordModel = Vtiger_Record_Model::getInstanceById($this->get('product'), 'Products');
		$renewable = $productsRecordModel->get('renewable');
		if (!$renewable) {
			return 'PLL_NOT_APPLICABLE_VERIFICATION';
		}
		if (!$this->isEmpty('renewalinvoice')) {
			return 'PLL_RENEWED_VERIFICATION';
		}
		$dateInService = strtotime($this->get('dateinservice'));
		$renewalTime = AppConfig::module('Assets', 'RENEWAL_TIME');
		$dateRenewable = strtotime('-' . $renewalTime, $dateInService);
		$classFunction = AppConfig::module('Assets', 'RENEWAL_CUSTOMER_FUNCTION');
		$methodExist = false;
		if ($classFunction && class_exists($classFunction['class']) && method_exists($classFunction['class'], $classFunction['method'])) {
			$methodExist = true;
		}
		if ($dateRenewable > time()) {
			return 'PLL_PLANNED';
		}
		if (strtotime('+' . $renewalTime, $dateInService) < time()) {
			if ($methodExist) {
				return $classFunction['class']::$classFunction['method']($this, 'PLL_NOT_RENEWED_VERIFICATION');
			}
			return 'PLL_NOT_RENEWED_VERIFICATION';
		}
		if ($methodExist) {
			return $classFunction['class']::$classFunction['method']($this, 'PLL_WAITING_FOR_RENEWAL', $renewalTime);
		}
		return 'PLL_WAITING_FOR_RENEWAL';
	}
}
