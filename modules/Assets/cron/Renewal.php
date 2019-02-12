<?php
/**
 * Cron updating Assets renewal.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
require_once 'include/main/WebUI.php';

$renewal = ['PLL_PLANNED', 'PLL_WAITING_FOR_RENEWAL', ''];
$query = (new App\Db\Query())->select(['vtiger_assets.assetsid'])->from('vtiger_assets')->innerJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = vtiger_assets.assetsid')->where(['vtiger_crmentity.deleted' => 0])->andWhere(['or', ['assets_renew' => $renewal], ['assets_renew' => null]]);
$dataReader = $query->createCommand()->query();
while ($recordId = $dataReader->readColumn(0)) {
	$recordModel = Vtiger_Record_Model::getInstanceById($recordId, 'Assets');
	$recordModel->updateRenewal();
}
$dataReader->close();
