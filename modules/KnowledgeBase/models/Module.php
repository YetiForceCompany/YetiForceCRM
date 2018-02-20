<?php

/**
 * Model of module.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class KnowledgeBase_Module_Model extends Vtiger_Module_Model
{
	public function getTreeViewName()
	{
		return 'Tree';
	}

	public function getTreeViewUrl()
	{
		return 'index.php?module=' . $this->get('name') . '&view=' . $this->getTreeViewName();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSideBarLinks($linkParams)
	{
		$links = parent::getSideBarLinks($linkParams);
		$links['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues([
				'linktype' => 'SIDEBARLINK',
				'linklabel' => 'LBL_VIEW_TREE',
				'linkurl' => $this->getTreeViewUrl(),
				'linkicon' => 'fas fa-tree',
		]);

		return $links;
	}
}
