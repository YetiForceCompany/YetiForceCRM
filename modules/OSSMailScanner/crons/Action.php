<?php
/**
 * Cron for scheduled import.
 *
 * @package   App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */

/**
 * OSSMailScanner_Action_Cron class.
 */
class OSSMailScanner_Action_Cron extends \App\CronHandler
{
	/** {@inheritdoc} */
	public function process()
	{
		$recordModel = Vtiger_Record_Model::getCleanInstance('OSSMailScanner');
		$user_name = '';
		if (PHP_SAPI == 'cgi-fcgi') {
			$user_name = Users_Record_Model::getCurrentUserModel()->user_name;
		}
		$recordModel->executeCron(PHP_SAPI . ' - ' . $user_name);
	}
}
