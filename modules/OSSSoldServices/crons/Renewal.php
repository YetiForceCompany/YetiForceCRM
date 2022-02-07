<?php
/**
 * Cron updating SoldServices renewal.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * OSSSoldServices_Renewal_Cron class.
 */
class OSSSoldServices_Renewal_Cron extends \App\CronHandler
{
	/** {@inheritdoc} */
	public function process()
	{
		$renewal = ['PLL_PLANNED', 'PLL_WAITING_FOR_RENEWAL', ''];
		$query = (new App\Db\Query())->select(['vtiger_osssoldservices.osssoldservicesid'])->from('vtiger_osssoldservices')->innerJoin('vtiger_crmentity', 'vtiger_osssoldservices.osssoldservicesid = vtiger_crmentity.crmid')->where(['and', ['vtiger_crmentity.deleted' => 0], ['or', ['osssoldservices_renew' => $renewal], ['osssoldservices_renew' => null]]]);
		$dataReader = $query->createCommand()->query();
		while ($recordId = $dataReader->readColumn(0)) {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, 'OSSSoldServices');
			$recordModel->updateRenewal();
		}
		$dataReader->close();
	}
}
