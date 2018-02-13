<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce Sp. z o.o.
 * *********************************************************************************** */

class Products_RelationAjax_Action extends Vtiger_RelationAjax_Action
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod('addListPrice');
    }

    /**
     * Function adds Products/Services-PriceBooks Relation.
     *
     * @param type $request
     */
    public function addListPrice(\App\Request $request)
    {
        $sourceModule = $request->getModule();
        $sourceRecordId = $request->getInteger('src_record');
        $relatedModule = $request->getByType('related_module');
        $relInfos = $request->get('relinfo');

        $sourceModuleModel = Vtiger_Module_Model::getInstance($sourceModule);
        $relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModule);
        $relationModel = Vtiger_Relation_Model::getInstance($sourceModuleModel, $relatedModuleModel);
        foreach ($relInfos as $relInfo) {
            $price = CurrencyField::convertToDBFormat($relInfo['price'], null, true);
            $relationModel->addListPrice($sourceRecordId, $relInfo['id'], $price);
        }
    }
}
