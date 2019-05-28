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
class KnowledgeBase_TreeAjax_Action extends \App\Controller\Action
{
	use \App\Controller\ExposeMethod;
	/**
	 * Detail query conditions.
	 *
	 * @var string[]
	 */
	protected $queryCondition = ['knowledgebase_status' => 'PLL_ACCEPTED'];

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('list');
		$this->exposeMethod('categories');
		$this->exposeMethod('detail');
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
		if (!$request->isEmpty('record') && !\App\Privilege::isPermitted($request->getModule(), 'DetailView', $request->getInteger('record'))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/**
	 * Get tree model instance.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function getModel(App\Request $request)
	{
		return KnowledgeBase_Tree_Model::getInstance($request->getModule());
	}

	/**
	 * Get data for knowledge base.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function list(App\Request $request)
	{
		$treeModel = $this->getModel($request);
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
		$treeModel = $this->getModel($request);
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
			$treeModel = $this->getModel($request);
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

	/**
	 * Details knowledge base.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function detail(App\Request $request)
	{
		$recordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $request->getModule());
		if ('PLL_PRESENTATION' === $recordModel->get('knowledgebase_view')) {
			$content = [];
			foreach (explode('<div style="page-break-after:always;"><span style="display:none;">', $recordModel->get('content')) as $key => $value) {
				if (0 === $key) {
					$content[] = $value;
				} else {
					$content[] = substr($value, 16);
				}
			}
		} else {
			$content = $recordModel->get('content');
		}
		$pagingModel = new Vtiger_Paging_Model();
		$relationListView = Vtiger_RelationListView_Model::getInstance($recordModel, $request->getModule());
		$relationListView->setFields(['id', 'subject', 'introduction', 'assigned_user_id', 'category', 'modifiedtime']);
		$relationListView->getQueryGenerator()->addNativeCondition($this->queryCondition);
		$related = [];
		foreach ($relationListView->getEntries($pagingModel) as $key => $relatedRecordModel) {
			$related[$key] = [
				'assigned_user_id' => $relatedRecordModel->getDisplayValue('assigned_user_id'),
				'subject' => $relatedRecordModel->get('subject'),
				'introduction' => $relatedRecordModel->getDisplayValue('introduction'),
				'category' => $relatedRecordModel->get('category'),
				'full_time' => App\Fields\DateTime::formatToDisplay($relatedRecordModel->get('modifiedtime')),
				'short_time' => \Vtiger_Util_Helper::formatDateDiffInStrings($relatedRecordModel->get('modifiedtime')),
			];
		}
		$response = new Vtiger_Response();
		$response->setResult([
			'content' => $content,
			'introduction' => $recordModel->getDisplayValue('introduction'),
			'subject' => $recordModel->get('subject'),
			'view' => $recordModel->get('knowledgebase_view'),
			'assigned_user_id' => $recordModel->getDisplayValue('assigned_user_id'),
			'category' => $recordModel->getDisplayValue('category'),
			'full_createdtime' => $recordModel->getDisplayValue('createdtime'),
			'short_createdtime' => \Vtiger_Util_Helper::formatDateDiffInStrings($recordModel->get('createdtime')),
			'full_modifiedtime' => $recordModel->getDisplayValue('modifiedtime'),
			'short_modifiedtime' => \Vtiger_Util_Helper::formatDateDiffInStrings($recordModel->get('modifiedtime')),
			'related' => $related,
		]);
		$response->emit();
	}
}
