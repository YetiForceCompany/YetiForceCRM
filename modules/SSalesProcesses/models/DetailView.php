<?php

/**
 * SSalesProcesses DetailView Model Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
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
			$relatedLinks = $this->issetRelationsInModule($relations, $recordModel, $relatedLinks);
		}
		return $relatedLinks;
	}
}
