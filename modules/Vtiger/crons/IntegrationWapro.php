<?php
/**
 * Integration WAPRO ERP cron file.
 *
 * @package   Cron
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Integration WAPRO ERP cron class.
 */
class Vtiger_IntegrationWapro_Cron extends \App\CronHandler
{
	/** {@inheritdoc} */
	public function process()
	{
		$ids = (new \App\Db\Query())->select(['id'])->from(\App\Integrations\Wapro::TABLE_NAME)
			->where(['status' => 1])
			->column(\App\Db::getInstance('admin')) ?: [];
		$this->logs = 0;
		foreach ($ids as $id) {
			$this->updateLastActionTime();
			$wapro = new \App\Integrations\Wapro($id, $this);
			foreach ($wapro->getSynchronizers() as $synchronizer) {
				$this->updateLastActionTime();
				$this->logs += $synchronizer->process();
				if ($this->checkTimeout()) {
					return;
				}
			}
		}
	}
}
