<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

class Contacts_Detail_View extends Vtiger_Detail_View
{
    /**
     * {@inheritdoc}
     */
    public function showModuleDetailView(\App\Request $request)
    {
        if (!$this->record) {
            $this->record = Vtiger_DetailView_Model::getInstance($request->getModule(), $request->getInteger('record'));
        }
        $viewer = $this->getViewer($request);
        $viewer->assign('IMAGE_DETAILS', $this->record->getRecord()->getImageDetails());

        return parent::showModuleDetailView($request);
    }
}
