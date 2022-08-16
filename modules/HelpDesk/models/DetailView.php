<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * *********************************************************************************** */

class HelpDesk_DetailView_Model extends Vtiger_DetailView_Model
{
	/** {@inheritdoc} */
	public function getDetailViewRelatedLinks()
	{
		$recordModel = $this->getRecord();
		$relatedLinks = parent::getDetailViewRelatedLinks();
		if (App\Config::module($recordModel->getModuleName(), 'SHOW_SUMMARY_PRODUCTS_SERVICES')) {
			$relations = \Vtiger_Relation_Model::getAllRelations($this->getModule(), false, true, true, 'modulename');
			if (isset($relations['Products']) || isset($relations['Services']) || isset($relations['Assets']) || isset($relations['OSSSoldServices'])) {
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
