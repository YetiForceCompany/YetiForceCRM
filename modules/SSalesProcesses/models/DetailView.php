<?php

/**
 * SSalesProcesses DetailView Model Class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class SSalesProcesses_DetailView_Model extends Vtiger_DetailView_Model
{
	/** {@inheritdoc} */
	public function getDetailViewRelatedLinks()
	{
		$recordModel = $this->getRecord();
		$relatedLinks = parent::getDetailViewRelatedLinks();
		if (App\Config::module($recordModel->getModuleName(), 'SHOW_SUMMARY_PRODUCTS_SERVICES')) {
			$relations = \Vtiger_Relation_Model::getAllRelations($this->getModule(), false, true, true, 'modulename');
			if (isset($relations['Products']) || isset($relations['Services']) || isset($relations['OSSOutsourcedServices']) || isset($relations['Assets']) || isset($relations['OSSSoldServices']) || isset($relations['OutsourcedProducts'])) {
				$relatedLinks[] = [
					'linktype' => 'DETAILVIEWTAB',
					'linklabel' => 'LBL_RECORD_SUMMARY_PRODUCTS_SERVICES',
					'linkurl' => $recordModel->getDetailViewUrl() . '&mode=showRelatedProductsServices&requestMode=summary',
					'linkicon' => '',
					'linkKey' => 'LBL_RECORD_SUMMARY',
					'related' => 'ProductsAndServices',
					'countRelated' => App\Config::relation('SHOW_RECORDS_COUNT'),
				];
			}
		}
		return $relatedLinks;
	}
}
