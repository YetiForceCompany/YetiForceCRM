<?php

/**
 * Action to get data of tree.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class KnowledgeBase_TreeAjax_Action extends \App\Controller\Action
{
	use \App\Controller\ExposeMethod;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('data');
		$this->exposeMethod('categories');
		$this->exposeMethod('search');
	}

	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(App\Request $request)
	{
		if (!Users_Privileges_Model::getCurrentUserPrivilegesModel()->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * Get data for knowledge base.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function data(App\Request $request)
	{
		$treeModel = KnowledgeBase_Tree_Model::getInstance();
		if (!$request->isEmpty('category')) {
			$treeModel->set('parentCategory', $request->getByType('category', 'Alnum'));
		}
		$response = new Vtiger_Response();
		$response->setResult($treeModel->getData());
		$response->emit();
	}

	/**
	 * Get categories for knowledge base.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function categories(App\Request $request)
	{
		$treeModel = KnowledgeBase_Tree_Model::getInstance();
		$categories = [];
		foreach ($treeModel->getCategories() as $row) {
			$row['parent'] = App\Fields\Tree::getParentIdx($row);
			unset($row['templateid'], $row['depth'], $row['state'], $row['name']);
			$row['parentTree'] = explode('::', $row['parentTree']);
			$categories[$row['tree']] = $row;
		}
		$response = new Vtiger_Response();
		$response->setResult($categories);
		$response->emit();
	}

	/**
	 * Search for knowledge base.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function search(App\Request $request)
	{
		$rows = [];
		if (!$request->isEmpty('value')) {
			$treeModel = KnowledgeBase_Tree_Model::getInstance();
			if (!$request->isEmpty('category')) {
				$treeModel->set('parentCategory', $request->getByType('category', 'Alnum'));
			}
			$treeModel->set('value', $request->getByType('value', 'Text'));
			$rows = $treeModel->search();
		}
		$response = new Vtiger_Response();
		$response->setResult($rows);
		$response->emit();
	}
}
