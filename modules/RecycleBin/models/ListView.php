<?php

/**
 * RecycleBin listView View Class.
 *
 * @package   View
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */
class RecycleBin_ListView_Model extends Vtiger_ListView_Model
{
	public static function getInstance($moduleName, $sourceModule = 0)
	{
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'ListView', $moduleName);
		$instance = new $modelClassName();
		$sourceModuleModel = Vtiger_Module_Model::getInstance($sourceModule);
		$queryGenerator = new \App\QueryGenerator($sourceModuleModel->get('name'));
		$cvidObj = CustomView_Record_Model::getAllFilterByModule($sourceModuleModel->get('name'));
		$viewId = $cvidObj->getId('cvid');
		$queryGenerator->initForCustomViewById($viewId);
		return $instance->set('module', $sourceModuleModel)->set('query_generator', $queryGenerator);
	}

	public function getListViewMassActions($linkParams)
	{
		$massActionLinks = [];
		$moduleModel = $this->getModule();
		if ($moduleModel->isPermitted('MassActive')) {
			$massActionLinks[] = [
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_MASS_ACTIVATE',
				'linkurl' => 'javascript:',
				'dataUrl' => 'index.php?module=' . $moduleModel->getName() . '&action=MassState&state=Active&sourceView=List',
				'linkdata' => ['confirm' => \App\Language::translate('LBL_ACTIVATE_RECORD_DESC')],
				'linkclass' => 'js-mass-record-event',
				'linkicon' => 'fas fa-undo-alt'
			];
		}
		if ($moduleModel->isPermitted('MassDelete')) {
			$massActionLinks[] = [
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_MASS_DELETE',
				'linkurl' => 'javascript:',
				'dataUrl' => 'index.php?module=' . $moduleModel->getName() . '&action=MassDelete&sourceView=List',
				'linkdata' => ['confirm' => \App\Language::translate('LBL_DELETE_RECORD_COMPLETELY_DESC')],
				'linkclass' => 'js-mass-record-event',
				'linkicon' => 'fas fa-eraser'
			];
		}
		foreach ($massActionLinks as $massActionLink) {
			$links['LISTVIEWMASSACTION'][] = Vtiger_Link_Model::getInstanceFromValues($massActionLink);
		}
		return $links;
	}

	public function loadListViewCondition()
	{
		$this->set('entityState', 'Trash');
		parent::loadListViewCondition();
	}
}
