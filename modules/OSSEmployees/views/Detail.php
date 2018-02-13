<?php

/**
 * OSSEmployees detail view class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSEmployees_Detail_View extends Vtiger_Detail_View
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod('showRelatedRecords');
    }

    /**
     * {@inheritdoc}
     */
    public function getFooterScripts(\App\Request $request)
    {
        $headerScriptInstances = parent::getFooterScripts($request);

        $moduleName = $request->getModule();

        //Added to remove the module specific js, as they depend on inventory files
        $modulePopUpFile = 'modules.'.$moduleName.'.resources.Popup';
        $moduleEditFile = 'modules.'.$moduleName.'.resources.Edit';
        $moduleDetailFile = 'modules.'.$moduleName.'.resources.Detail';
        unset($headerScriptInstances[$modulePopUpFile]);
        unset($headerScriptInstances[$moduleEditFile]);
        unset($headerScriptInstances[$moduleDetailFile]);

        $jsFileNames = [
            "modules.$moduleName.resources.Detail",
        ];
        $jsFileNames[] = $moduleEditFile;
        $jsFileNames[] = $modulePopUpFile;
        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);

        return $headerScriptInstances;
    }
}
