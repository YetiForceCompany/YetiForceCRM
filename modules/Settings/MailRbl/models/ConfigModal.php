<?php
/**
 * Mail RBL configuration modal model file.
 *
 * @package   Settings.Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author	  Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Mail RBL configuration modal model class.
 */
class Settings_MailRbl_ConfigModal_Model
{
	/**
	 * Function determines fields available in modal view.
	 *
	 * @return Vtiger_Field_Model[]
	 */
	public static function getFields(): array
	{
		$config = \App\Config::component('Mail', null, []);
		$fields = [
			'rcListCheckRbl' => [
				'purifyType' => 'bool',
				'uitype' => 56,
				'label' => 'LBL_ACTIVATE_RBL_LIST',
				'labelDesc' => 'LBL_ACTIVATE_RBL_LIST_DESC',
				'fieldvalue' => $config['rcListCheckRbl'] ?? false,
			],
			'rcDetailCheckRbl' => [
				'purifyType' => 'bool',
				'uitype' => 56,
				'label' => 'LBL_ACTIVATE_RBL_DETAIL',
				'labelDesc' => 'LBL_ACTIVATE_RBL_DETAIL_DESC',
				'fieldvalue' => $config['rcDetailCheckRbl'] ?? false,
			],
			'rcListAcceptAutomatically' => [
				'purifyType' => 'bool',
				'uitype' => 56,
				'label' => 'LBL_ACCEPT_AUTOMATICALLY',
				'labelDesc' => 'LBL_ACCEPT_AUTOMATICALLY_DESC',
				'fieldvalue' => $config['rcListAcceptAutomatically'] ?? false,
			],
			'rcListSendReportAutomatically' => [
				'purifyType' => 'bool',
				'uitype' => 56,
				'label' => 'LBL_SEND_REPORT_AUTOMATICALLY',
				'labelDesc' => 'LBL_SEND_REPORT_AUTOMATICALLY_DESC',
				'fieldvalue' => $config['rcListSendReportAutomatically'] ?? false,
			],
		];
		foreach ($fields as $key => $value) {
			$fields[$key] = \Vtiger_Field_Model::init('OSSMail', $value, $key);
		}
		return $fields;
	}
}
