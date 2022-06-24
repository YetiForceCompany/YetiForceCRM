<?php

/**
 * Settings RecordCollector module model class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_RecordCollector_Module_Model extends Settings_Vtiger_Module_Model
{
	private $collectors = [];

	public function getCollectors()
	{
		$active = (new \App\Db\Query())->select(['linklabel'])->from('vtiger_links')->where(['linktype' => 'EDIT_VIEW_RECORD_COLLECTOR'])->column();
		$iterator = new \DirectoryIterator('App' . DIRECTORY_SEPARATOR . 'RecordCollectors');
		foreach ($iterator as $item) {
			if ($item->isFile() && 'php' === $item->getExtension() && 'Base' != $item->getBasename('.php')) {
				$collectorInstance = \App\RecordCollector::getInstance('App' . DIRECTORY_SEPARATOR . 'RecordCollectors' . DIRECTORY_SEPARATOR . $item->getBasename('.php'), 'Accounts');
				$this->collectors[] = [
					'instance' => $collectorInstance,
					'active' => \in_array($item->getBasename('.php'), $active) ? true : false,
					'name' => $item->getBasename('.php'),
				];
			}
		}
		return $this->collectors;
	}
}
