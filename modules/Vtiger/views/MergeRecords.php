<?php
/**
 * Merge records view.
 *
 * @package View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Merge records class.
 */
class Vtiger_MergeRecords_View extends \App\Controller\Modal
{
	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		if (!\App\Privilege::isPermitted($request->getModule(), 'Merge')) {
			throw new \App\Exceptions\NoPermitted('ERR_NOT_ACCESSIBLE', 406);
		}
	}

	/** {@inheritdoc} */
	public $modalSize = 'modal-fullscreen';

	/** {@inheritdoc} */
	public function preProcessAjax(App\Request $request)
	{
		$this->modalIcon = 'fa fa-code';
		$this->initializeContent($request);
		parent::preProcessAjax($request);
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->view('MergeRecords.tpl', $request->getModule());
	}

	/** {@inheritdoc} */
	public function initializeContent(App\Request $request)
	{
		$count = 0;
		$recordModels = $fields = [];
		$queryGenerator = Vtiger_Mass_Action::getQuery($request);
		if ($queryGenerator) {
			$moduleModel = $queryGenerator->getModuleModel();
			foreach ($queryGenerator->getModuleFields() as $field) {
				if ($field->isEditable()) {
					$fields[] = $field->getName();
				}
			}
			$queryGenerator->setFields($fields);
			$queryGenerator->setField('id');
			$query = $queryGenerator->createQuery();
			$count = $query->count();
			$dataReader = $query->limit(\App\Config::performance('MAX_MERGE_RECORDS'))->createCommand()->query();
			while ($row = $dataReader->read()) {
				$recordModels[$row['id']] = $moduleModel->getRecordFromArray($row);
			}
			$dataReader->close();
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('COUNT', $count);
		$viewer->assign('RECORD_MODELS', $recordModels);
		$viewer->assign('FIELDS', $fields);
	}

	/** {@inheritdoc} */
	public function postProcessAjax(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		if (($var = $viewer->getTemplateVars('RECORD_MODELS')) && \count($var) > 1) {
			$viewer->assign('BTN_SUCCESS', 'LBL_MERGE');
		}
		$viewer->assign('BTN_DANGER', $this->dangerBtn);
		$viewer->view('Modals/Footer.tpl', $request->getModule());
	}

	/** {@inheritdoc} */
	public function getPageTitle(App\Request $request)
	{
		$moduleName = $request->getModule();
		return \App\Language::translate('LBL_MERGE_RECORDS_IN', $moduleName) . ': ' . \App\Language::translate($moduleName, $moduleName);
	}
}
