<?php

/**
 * Action to get data of tree.
 *
 * @package Action
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Faq_KnowledgeBaseAjax_Action extends KnowledgeBase_KnowledgeBaseAjax_Action
{
	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getAccounts');
	}

	/** {@inheritdoc} */
	protected $queryCondition = ['vtiger_faq.status' => 'Published'];

	/** {@inheritdoc} */
	public function getModel(App\Request $request)
	{
		return Faq_KnowledgeBase_Model::getInstance($request->getModule());
	}

	/** {@inheritdoc} */
	public function list(App\Request $request)
	{
		$treeModel = $this->getModel($request);
		if (!$request->isEmpty('category')) {
			$treeModel->set('parentCategory', $request->getByType('category', 'Alnum'));
		}
		if (!$request->isEmpty('accountid')) {
			$treeModel->set('filterField', 'accountid');
			$treeModel->set('filterValue', $request->getInteger('accountid'));
		}
		$response = new Vtiger_Response();
		$response->setResult($treeModel->getData());
		$response->emit();
	}

	/**
	 * Get accounts list.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function getAccounts(App\Request $request)
	{
		$treeModel = $this->getModel($request);
		if (!$request->isEmpty('category')) {
			$treeModel->set('parentCategory', $request->getByType('category', 'Alnum'));
		}
		$response = new Vtiger_Response();
		$response->setResult($treeModel->getAccounts());
		$response->emit();
	}
}
