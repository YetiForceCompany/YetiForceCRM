<?php

/**
 * Settings RecordCollector module model file.
 *
 * @package Settings.Models
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Sławomir Rembiesa <s.rembiesa@yetiforce.com>
 */

/**
 * Settings RecordCollector module model class.
 */
class Settings_RecordCollector_Module_Model extends Settings_Vtiger_Module_Model
{
	/** @var array collectors */
	private $collectors = [];

	/**
	 * Function fetching all collectors in system.
	 *
	 * @return array
	 */
	public function getCollectors()
	{
		$active = (new \App\Db\Query())->select(['linklabel'])->from('vtiger_links')->where(['linktype' => 'EDIT_VIEW_RECORD_COLLECTOR'])->column();
		$iterator = new \DirectoryIterator('App' . DIRECTORY_SEPARATOR . 'RecordCollectors');
		foreach ($iterator as $item) {
			$file = $item->getBasename('.php');
			if ($item->isFile() && 'php' === $item->getExtension() && 'Base' != $file) {
				$collectorInstance = \App\RecordCollector::getInstance('App' . DIRECTORY_SEPARATOR . 'RecordCollectors' . DIRECTORY_SEPARATOR . $file, 'Accounts');
				$this->collectors[] = [
					'instance' => $collectorInstance,
					'active' => \in_array($file, $active) ? true : false,
					'name' => $file,
				];
			}
		}
		return $this->collectors;
	}
}
