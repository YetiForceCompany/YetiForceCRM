<?php

/**
 * Edit view file for Magento module.
 *
 * @package   Settings.View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

use App\Request;

/**
 * Edit view class for Magento.
 */
class Settings_Magento_Edit_View extends Settings_Vtiger_Index_View
{
    /** {@inheritdoc} */
    public function checkPermission(Request $request)
    {
        parent::checkPermission($request);
        if(!\App\YetiForce\Register::getProduct('YetiForceMagento')) {
            throw new \App\Exceptions\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
        }
    }
    /** {@inheritdoc} */
    public function process(App\Request $request)
    {
        $moduleName = $request->getModule(false);
        $viewer = $this->getViewer($request);
        $record = !$request->isEmpty('record') ? $request->getInteger('record') : '';
        if ($record) {
            $recordModel = Settings_Magento_Record_Model::getInstanceById($record);
        } else {
            $recordModel = Settings_Magento_Record_Model::getCleanInstance();
        }
        $viewer->assign('RECORD_MODEL', $recordModel);
        $viewer->assign('RECORD_ID', $record);
        $viewer->assign('QUALIFIED_MODULE', $moduleName);
        $viewer->view('Edit.tpl', $moduleName);
    }
}
