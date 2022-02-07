<?php

/**
 * Action to get data of KnowledgeBase.
 *
 * @package Action
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class KnowledgeBase_KnowledgeBaseAjax_Action extends \App\Controller\Action
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

	/** {@inheritdoc} */
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
	 * Get KnowledgeBase model instance.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function getModel(App\Request $request)
	{
		return KnowledgeBase_KnowledgeBase_Model::getInstance($request->getModule());
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
			$row['label'] = \App\Language::translate($row['label'], $request->getModule());
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
			$fieldModel = $recordModel->getField('content');
			foreach (explode('<div style="page-break-after:always;"><span style="display:none;">', $recordModel->get('content')) as $key => $value) {
				if (0 === $key) {
					$content[] = $fieldModel->getDisplayValue($value, $recordModel->getId(), $recordModel, true);
				} else {
					$content[] = $fieldModel->getDisplayValue(substr($value, 16), $recordModel->getId(), $recordModel, true);
				}
			}
		} else {
			$content = $recordModel->getDisplayValue('content', false, true);
		}
		$relatedModules = $relatedRecords = [];
		foreach ($recordModel->getModule()->getRelations() as $value) {
			$relatedModuleName = $value->get('relatedModuleName');
			$relatedModules[$relatedModuleName] = App\Language::translate($relatedModuleName, $relatedModuleName);
			if ('ModComments' !== $relatedModuleName && $request->getModule() !== $relatedModuleName) {
				$relatedRecords[$relatedModuleName] = $this->getRelatedRecords($recordModel, $relatedModuleName);
			}
		}
		$response = new Vtiger_Response();
		$response->setResult([
			'content' => $content,
			'introduction' => $recordModel->getDisplayValue('introduction', false, true),
			'subject' => $recordModel->get('subject'),
			'view' => $recordModel->get('knowledgebase_view'),
			'assigned_user_id' => $recordModel->getDisplayValue('assigned_user_id', false, true),
			'accountId' => $recordModel->get('accountid'),
			'accountName' => $recordModel->getDisplayValue('accountid', false, true),
			'category' => $recordModel->getDisplayValue('category'),
			'full_createdtime' => $recordModel->getDisplayValue('createdtime'),
			'short_createdtime' => \Vtiger_Util_Helper::formatDateDiffInStrings($recordModel->get('createdtime')),
			'full_modifiedtime' => $recordModel->getDisplayValue('modifiedtime'),
			'short_modifiedtime' => \Vtiger_Util_Helper::formatDateDiffInStrings($recordModel->get('modifiedtime')),
			'related' => [
				'base' => [
					'Articles' => $this->getRelated($recordModel),
					'ModComments' => $this->getRelatedComments($recordModel->getId()),
				],
				'dynamic' => $relatedRecords
			],
			'translations' => $relatedModules
		]);
		$response->emit();
	}

	/**
	 * Get related records.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @return array
	 */
	public function getRelated(Vtiger_Record_Model $recordModel): array
	{
		$relationListView = Vtiger_RelationListView_Model::getInstance($recordModel, $recordModel->getModuleName());
		$relationListView->setFields(['id', 'subject', 'introduction', 'assigned_user_id', 'category', 'modifiedtime']);
		$relationListView->getQueryGenerator()->addNativeCondition($this->queryCondition);
		$related = [];
		foreach ($relationListView->getAllEntries() as $key => $relatedRecordModel) {
			$related[$key] = [
				'assigned_user_id' => $relatedRecordModel->getDisplayValue('assigned_user_id'),
				'subject' => $relatedRecordModel->get('subject'),
				'introduction' => $relatedRecordModel->getDisplayValue('introduction'),
				'category' => $relatedRecordModel->get('category'),
				'full_time' => App\Fields\DateTime::formatToDisplay($relatedRecordModel->get('modifiedtime')),
				'short_time' => \Vtiger_Util_Helper::formatDateDiffInStrings($relatedRecordModel->get('modifiedtime')),
			];
		}
		return $related;
	}

	/**
	 * Get related comments.
	 *
	 * @param int $recordId
	 *
	 * @return array
	 */
	public function getRelatedComments(int $recordId): array
	{
		if (!\App\Privilege::isPermitted('ModComments')) {
			return [];
		}
		$queryGenerator = new \App\QueryGenerator('ModComments');
		$queryGenerator->setFields(['modifiedtime', 'id',	'assigned_user_id', 'commentcontent']);
		$queryGenerator->setSourceRecord($recordId);
		$queryGenerator->addNativeCondition(['related_to' => $recordId]);
		$query = $queryGenerator->createQuery()->orderBy(['id' => SORT_DESC]);
		$query->limit(50);
		$dataReader = $query->createCommand()->query();
		$related = [];
		while ($row = $dataReader->read()) {
			$related[$row['id']] = [
				'userid' => $row['assigned_user_id'],
				'userName' => App\Fields\Owner::getLabel($row['assigned_user_id']),
				'commentId' => $row['id'],
				'comment' => $row['commentcontent'],
				'avatar' => \App\User::getImageById($row['assigned_user_id']),
				'modifiedFull' => App\Fields\DateTime::formatToDisplay($row['modifiedtime']),
				'modifiedShort' => \Vtiger_Util_Helper::formatDateDiffInStrings($row['modifiedtime']),
			];
		}
		return $related;
	}

	/**
	 * Get related records.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 * @param string              $moduleName
	 *
	 * @return array
	 */
	public function getRelatedRecords(Vtiger_Record_Model $recordModel, string $moduleName): array
	{
		if (!\App\Privilege::isPermitted($moduleName)) {
			return [];
		}
		$relationListView = Vtiger_RelationListView_Model::getInstance($recordModel, $moduleName);
		$fields = $relationListView->getRelatedModuleModel()->getNameFields();
		$relationListView->setFields(array_merge(['id'], $fields));
		$related = [];
		foreach ($relationListView->getAllEntries() as $key => $relatedRecordModel) {
			$name = [];
			foreach ($fields as $fieldName) {
				$name[] = $relatedRecordModel->getDisplayName($fieldName);
			}
			$related[$key] = implode(' | ', $name);
		}
		return $related;
	}
}
