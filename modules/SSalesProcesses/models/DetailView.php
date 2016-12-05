<?php

/**
 * SSalesProcesses DetailView Model Class
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class SSalesProcesses_DetailView_Model extends Vtiger_DetailView_Model
{

	public function getDetailViewRelatedLinks()
	{
		$recordModel = $this->getRecord();
		$moduleName = $recordModel->getModuleName();

		$relatedLinks = Vtiger_DetailView_Model::getDetailViewRelatedLinks();
		$showPSTab = (!AppConfig::module($moduleName, 'HIDE_SUMMARY_PRODUCTS_SERVICES')) && (\App\Module::isModuleActive('OutsourcedProducts') || \App\Module::isModuleActive('Products') || \App\Module::isModuleActive('Services') || \App\Module::isModuleActive('OSSOutsourcedServices') || \App\Module::isModuleActive('Assets') || \App\Module::isModuleActive('OSSSoldServices'));

		if ($showPSTab) {
			$relatedLinks[] = [
				'linktype' => 'DETAILVIEWTAB',
				'linklabel' => 'LBL_RECORD_SUMMARY_PRODUCTS_SERVICES',
				'linkurl' => $recordModel->getDetailViewUrl() . '&mode=showRelatedProductsServices&requestMode=summary',
				'linkicon' => '',
				'linkKey' => 'LBL_RECORD_SUMMARY',
				'related' => 'ProductsAndServices',
				'countRelated' => AppConfig::relation('SHOW_RECORDS_COUNT')
			];
		}
		return $relatedLinks;
	}
}
