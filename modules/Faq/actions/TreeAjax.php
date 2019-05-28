<?php

/**
 * Action to get data of tree.
 *
 * @package Action
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Faq_TreeAjax_Action extends KnowledgeBase_TreeAjax_Action
{
	/**
	 * Detail query conditions.
	 *
	 * @var string[]
	 */
	protected $queryCondition = ['faqstatus' => 'Published'];

	/**
	 * Get tree model instance.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function getModel(App\Request $request)
	{
		return Faq_Tree_Model::getInstance($request->getModule());
	}
}
