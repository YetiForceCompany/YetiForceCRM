<?php

/**
 * Services TreeView Model Class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Services_TreeView_Model extends Vtiger_TreeView_Model
{
	/** {@inheritdoc} */
	public function isActive()
	{
		return true;
	}

	/**
	 * Gets tree records.
	 *
	 * @return array
	 */
	private function getRecords(): array
	{
		$listViewModel = Vtiger_ListView_Model::getInstance($this->getModuleName());
		$listViewModel->getQueryGenerator()->setFields(['id', 'pscategory']);
		$tree = [];
		foreach ($listViewModel->getAllEntries() as $item) {
			++$this->lastTreeId;
			$parent = $item->get('pscategory');
			$parent = (int) str_replace('T', '', $parent);
			$tree[] = [
				'id' => $this->lastTreeId,
				'record_id' => $item->getId(),
				'parent' => 0 == $parent ? '#' : $parent,
				'text' => $item->getName(),
				'isrecord' => true,
				'state' => [],
				'icon' => 'fas fa-file',
			];
		}
		return $tree;
	}

	/**
	 * Load tree.
	 *
	 * @return string
	 */
	public function getTreeList()
	{
		return array_merge(parent::getTreeList(), $this->getRecords());
	}
}
