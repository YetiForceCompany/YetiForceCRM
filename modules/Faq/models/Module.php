<?php
/**
 * Model of module.
 *
 * @package Model
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Faq_Module_Model extends Vtiger_Module_Model
{
	/**
	 * {@inheritdoc}
	 */
	public function getKnowledgeBaseViewName()
	{
		$defaultView = 'KnowledgeBase';
		if (\App\RequestUtil::getBrowserInfo()->ie) {
			$defaultView = 'List';
		}
		return $defaultView;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getKnowledgeBaseViewUrl()
	{
		return 'index.php?module=' . $this->get('name') . '&view=' . $this->getKnowledgeBaseViewName();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSideBarLinks($linkParams)
	{
		$links = parent::getSideBarLinks($linkParams);
		if (!\App\RequestUtil::getBrowserInfo()->ie) {
			$links['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues([
				'linktype' => 'SIDEBARLINK',
				'linklabel' => 'LBL_VIEW_KNOWLEDGE_BASE',
				'linkurl' => $this->getKnowledgeBaseViewUrl(),
				'linkicon' => 'fas fa-book-open',
			]);
		}
		return $links;
	}
}
