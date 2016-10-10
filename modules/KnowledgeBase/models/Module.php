<?php

/**
 * Model of module
 * @package YetiForce.Model
 * @license licenses/License.html
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

	public function getSideBarLinks($linkParams)
	{
		$links = parent::getSideBarLinks($linkParams);
		$quickLinks = [
			[
				'linktype' => 'SIDEBARLINK',
				'linklabel' => 'LBL_VIEW_TREE',
				'linkurl' => $this->getTreeViewUrl(),
				'linkicon' => '',
			],
		];
		foreach ($quickLinks as $quickLink) {
			$links['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues($quickLink);
		}
		return $links;
	}
}
