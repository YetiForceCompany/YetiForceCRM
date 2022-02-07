<?php

/**
 * Record Class for OSSSoldServices.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class OSSSoldServices_Record_Model extends Vtiger_Record_Model
{
	public function updateRenewal()
	{
		$value = $this->getRenewalValue();
		if ($value && $this->get('osssoldservices_renew') != $value) {
			$this->set('osssoldservices_renew', $value);
			$this->save();
		}
	}

	public function getRenewalValue()
	{
		if ($this->isEmpty('serviceid') || !\App\Record::isExists($this->get('serviceid'), 'Services')) {
			return 'PLL_NOT_APPLICABLE_VERIFICATION';
		}
		$productsRecordModel = Vtiger_Record_Model::getInstanceById($this->get('serviceid'), 'Services');
		$renewable = $productsRecordModel->get('renewable');
		if (!$renewable) {
			return 'PLL_NOT_APPLICABLE_VERIFICATION';
		}
		if (!$this->isEmpty('renewalinvoice')) {
			return 'PLL_RENEWED_VERIFICATION';
		}
		$dateInService = strtotime($this->get('dateinservice'));
		$renewalTime = App\Config::module('OSSSoldServices', 'RENEWAL_TIME');
		$dateRenewable = strtotime('-' . $renewalTime, $dateInService);
		$classFunction = App\Config::module('Assets', 'RENEWAL_CUSTOMER_FUNCTION');
		$methodExist = false;
		if ($classFunction && class_exists($classFunction['class']) && method_exists($classFunction['class'], $classFunction['method'])) {
			$methodExist = true;
		}
		if ($dateRenewable > time()) {
			if ($methodExist) {
				return \call_user_func_array("{$classFunction['class']}::{$classFunction['method']}", [$this, 'PLL_PLANNED']);
			}
			return 'PLL_PLANNED';
		}
		if (strtotime('+' . $renewalTime, $dateInService) < time()) {
			if ($methodExist) {
				return \call_user_func_array("{$classFunction['class']}::{$classFunction['method']}", [$this, 'PLL_NOT_RENEWED_VERIFICATION']);
			}
			return 'PLL_NOT_RENEWED_VERIFICATION';
		}
		if ($methodExist) {
			return \call_user_func_array("{$classFunction['class']}::{$classFunction['method']}", [$this, 'PLL_WAITING_FOR_RENEWAL']);
		}
		return 'PLL_WAITING_FOR_RENEWAL';
	}
}
