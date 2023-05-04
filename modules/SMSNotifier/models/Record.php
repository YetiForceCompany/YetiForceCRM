<?php
/**
 * Record Class for SMSNotifier.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Record Class for SMSNotifier.
 */
class SMSNotifier_Record_Model extends Vtiger_Record_Model
{
	/**
	 * Send sms.
	 *
	 * @return bool
	 */
	public function send(): bool
	{
		$result = false;
		if ($this->isEditable() && ($provider = $this->getProviderToSend())) {
			$result = $provider->sendByRecord($this);
			$this->set('smsnotifier_status', $result ? 'PLL_SENT' : 'PLL_FAILED');
			$this->save();
		}

		return $result;
	}

	/**
	 * Get provider to send.
	 *
	 * @return App\Integrations\SMSProvider\Provider|null
	 */
	private function getProviderToSend(): ?App\Integrations\SMSProvider\Provider
	{
		return $this->get('sms_provider_id') ? \App\Integrations\SMSProvider::getById($this->get('sms_provider_id')) : \App\Integrations\SMSProvider::getDefaultProvider();
	}
}
