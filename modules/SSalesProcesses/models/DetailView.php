<?php

/**
 * SSalesProcesses DetailView Model Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class SSalesProcesses_DetailView_Model extends Vtiger_DetailView_Model
{
	public function getDetailViewRelatedLinks()
	{
		$recordModel = $this->getRecord();
		$moduleName = $recordModel->getModuleName();
		$relatedLinks = parent::getDetailViewRelatedLinks();
		if (AppConfig::module($moduleName, 'SHOW_SUMMARY_PRODUCTS_SERVICES')) {
			$relations = \Vtiger_Relation_Model::getAllRelations($this->getModule(), false);
			if (isset($relations[\App\Module::getModuleId('OutsourcedProducts')]) ||
				isset($relations[\App\Module::getModuleId('Products')]) ||
				isset($relations[\App\Module::getModuleId('Services')]) ||
				isset($relations[\App\Module::getModuleId('OSSOutsourcedServices')]) ||
				isset($relations[\App\Module::getModuleId('Assets')]) ||
				isset($relations[\App\Module::getModuleId('OSSSoldServices')])) {
				$relatedLinks[] = [
					'linktype' => 'DETAILVIEWTAB',
					'linklabel' => 'LBL_RECORD_SUMMARY_PRODUCTS_SERVICES',
					'linkurl' => $recordModel->getDetailViewUrl() . '&mode=showRelatedProductsServices&requestMode=summary',
					'linkicon' => '',
					'linkKey' => 'LBL_RECORD_SUMMARY',
					'related' => 'ProductsAndServices',
					'countRelated' => AppConfig::relation('SHOW_RECORDS_COUNT'),
				];
			}
		}
		return $relatedLinks;
	}
}
