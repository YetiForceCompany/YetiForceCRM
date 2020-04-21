<?php
/**
 * Magento active action file.
 *
 * @package Settings.Action
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Magento active action class.
 */
class Settings_Magento_Active_Action extends Settings_Vtiger_Save_Action
{
	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
		$sSingleOrderTabId = \App\Module::getModuleId('SSingleOrders');
		$fInvoiceTabId = \App\Module::getModuleId('FInvoice');
		$productCategoryId = \App\Module::getModuleId('ProductCategory');
		$importerType = new \App\Db\Importers\Base();
		if (!(new \App\Db\Query())->from('vtiger_field')->where(['tabid' => $sSingleOrderTabId, 'fieldname' => 'magento_server_id'])->exists()) {
			$blockModel = Vtiger_Block_Model::getInstance('LBL_CUSTOM_INFORMATION', 'SSingleOrders');
			$blockModel = vtlib\Block::getInstance('LBL_CUSTOM_INFORMATION', 'SSingleOrders');
			if (!$blockModel) {
				$blocks = vtlib\Block::getAllForModule(vtlib\Module::getInstance('SSingleOrders'));
				$blockModel = current($blocks);
			}
			$fieldInstance = new Vtiger_Field_Model();
			$fieldInstance->set('name', 'magento_server_id');
			$fieldInstance->set('column', 'magento_server_id');
			$fieldInstance->set('columntype', $importerType->integer(10)->defaultValue(0)->notNull()->unsigned());
			$fieldInstance->set('table', 'u_yf_ssingleorders');
			$fieldInstance->set('label', 'FL_MAGENTO_SERVER');
			$fieldInstance->set('uitype', 325);
			$fieldInstance->set('displaytype', 9);
			$fieldInstance->set('maximumlength', '4294967295');
			$fieldInstance->set('typeofdata', 'I~O');
			$fieldInstance->save($blockModel);
		}
		if (!(new \App\Db\Query())->from('vtiger_field')->where(['tabid' => $sSingleOrderTabId, 'fieldname' => 'magento_id'])->exists()) {
			if (empty($blockModel)) {
				$blockModel = Vtiger_Block_Model::getInstance('LBL_CUSTOM_INFORMATION', 'SSingleOrders');
				$blockModel = vtlib\Block::getInstance('LBL_CUSTOM_INFORMATION', 'SSingleOrders');
				if (!$blockModel) {
					$blocks = vtlib\Block::getAllForModule(vtlib\Module::getInstance('SSingleOrders'));
					$blockModel = current($blocks);
				}
			}
			$fieldInstance = new Vtiger_Field_Model();
			$fieldInstance->set('name', 'magento_id');
			$fieldInstance->set('column', 'magento_id');
			$fieldInstance->set('columntype', $importerType->integer(10)->defaultValue(0)->notNull()->unsigned());
			$fieldInstance->set('table', 'u_yf_ssingleorders');
			$fieldInstance->set('label', 'FL_MAGENTO_ID');
			$fieldInstance->set('uitype', 7);
			$fieldInstance->set('displaytype', 2);
			$fieldInstance->set('maximumlength', '4294967295');
			$fieldInstance->set('typeofdata', 'I~O');
			$fieldInstance->save($blockModel);
		}
		if (!(new \App\Db\Query())->from('vtiger_field')->where(['tabid' => $sSingleOrderTabId, 'fieldname' => 'status_magento'])->exists()) {
			if (empty($blockModel)) {
				$blockModel = Vtiger_Block_Model::getInstance('LBL_CUSTOM_INFORMATION', 'SSingleOrders');
				$blockModel = vtlib\Block::getInstance('LBL_CUSTOM_INFORMATION', 'SSingleOrders');
				if (!$blockModel) {
					$blocks = vtlib\Block::getAllForModule(vtlib\Module::getInstance('SSingleOrders'));
					$blockModel = current($blocks);
				}
			}
			$fieldInstance = new Vtiger_Field_Model();
			$fieldInstance->set('name', 'status_magento');
			$fieldInstance->set('column', 'status_magento');
			$fieldInstance->set('columntype', $importerType->stringType()->defaultValue(''));
			$fieldInstance->set('table', 'u_yf_ssingleorders');
			$fieldInstance->set('label', 'FL_MAGENTO_STATUS');
			$fieldInstance->set('uitype', 16);
			$fieldInstance->set('displaytype', 2);
			$fieldInstance->set('maxlengthtext', '255');
			$fieldInstance->set('typeofdata', 'V~O');
			$fieldInstance->save($blockModel);
			$fieldInstance->setNoRolePicklistValues(['PLL_PENDING', 'PLL_PENDING_PAYMENT', 'PLL_PENDING_PAYPAL',  'PLL_PROCESSING', 'PLL_HOLDED', 'PLL_FRAUD', 'PLL_PAYMENT_REVIEW', 'PLL_PAYPAL_CANCELED_REVERSAL', 'PLL_PAYPAL_REVERSED', 'PLL_CANCELLED', 'PLL_CLOSED', 'PLL_COMPLETE']);
		}
		if (!(new \App\Db\Query())->from('vtiger_field')->where(['tabid' => $fInvoiceTabId, 'fieldname' => 'magento_server_id'])->exists()) {
			$blockModel = Vtiger_Block_Model::getInstance('LBL_CUSTOM_INFORMATION', 'FInvoice');
			$blockModel = vtlib\Block::getInstance('LBL_CUSTOM_INFORMATION', 'FInvoice');
			if (!$blockModel) {
				$blocks = vtlib\Block::getAllForModule(vtlib\Module::getInstance('FInvoice'));
				$blockModel = current($blocks);
			}
			$fieldInstance = new Vtiger_Field_Model();
			$fieldInstance->set('name', 'magento_server_id');
			$fieldInstance->set('column', 'magento_server_id');
			$fieldInstance->set('columntype', $importerType->integer(10)->defaultValue(0)->notNull()->unsigned());
			$fieldInstance->set('table', 'u_yf_finvoice');
			$fieldInstance->set('label', 'FL_MAGENTO_SERVER');
			$fieldInstance->set('uitype', 325);
			$fieldInstance->set('displaytype', 9);
			$fieldInstance->set('maximumlength', '4294967295');
			$fieldInstance->set('typeofdata', 'I~O');
			$fieldInstance->save($blockModel);
		}
		if (!(new \App\Db\Query())->from('vtiger_field')->where(['tabid' => $fInvoiceTabId, 'fieldname' => 'magento_id'])->exists()) {
			if (empty($blockModel)) {
				$blockModel = Vtiger_Block_Model::getInstance('LBL_CUSTOM_INFORMATION', 'FInvoice');
				$blockModel = vtlib\Block::getInstance('LBL_CUSTOM_INFORMATION', 'FInvoice');
				if (!$blockModel) {
					$blocks = vtlib\Block::getAllForModule(vtlib\Module::getInstance('FInvoice'));
					$blockModel = current($blocks);
				}
			}
			$fieldInstance = new Vtiger_Field_Model();
			$fieldInstance->set('name', 'magento_id');
			$fieldInstance->set('column', 'magento_id');
			$fieldInstance->set('columntype', $importerType->integer(10)->defaultValue(0)->notNull()->unsigned());
			$fieldInstance->set('table', 'u_yf_finvoice');
			$fieldInstance->set('label', 'FL_MAGENTO_ID');
			$fieldInstance->set('uitype', 7);
			$fieldInstance->set('displaytype', 2);
			$fieldInstance->set('maxlengthtext', '4294967295');
			$fieldInstance->set('typeofdata', 'I~O');
			$fieldInstance->save($blockModel);
		}
		if (!(new \App\Db\Query())->from('vtiger_field')->where(['tabid' => $productCategoryId, 'fieldname' => 'magento_server_id'])->exists()) {
			$blockModel = Vtiger_Block_Model::getInstance('LBL_BASIC_INFORMATION', 'ProductCategory');
			$blockModel = vtlib\Block::getInstance('LBL_BASIC_INFORMATION', 'ProductCategory');
			if (!$blockModel) {
				$blocks = vtlib\Block::getAllForModule(vtlib\Module::getInstance('ProductCategory'));
				$blockModel = current($blocks);
			}
			$fieldInstance = new Vtiger_Field_Model();
			$fieldInstance->set('name', 'magento_server_id');
			$fieldInstance->set('column', 'magento_server_id');
			$fieldInstance->set('columntype', $importerType->integer(10)->defaultValue(0)->notNull()->unsigned());
			$fieldInstance->set('table', 'u_yf_productcategory');
			$fieldInstance->set('label', 'FL_MAGENTO_SERVER');
			$fieldInstance->set('uitype', 325);
			$fieldInstance->set('displaytype', 9);
			$fieldInstance->set('maximumlength', '4294967295');
			$fieldInstance->set('typeofdata', 'I~O');
			$fieldInstance->save($blockModel);
		}
		if (!(new \App\Db\Query())->from('vtiger_field')->where(['tabid' => $productCategoryId, 'fieldname' => 'magento_id'])->exists()) {
			$fieldInstance = new Vtiger_Field_Model();
			$fieldInstance->set('name', 'magento_id');
			$fieldInstance->set('column', 'magento_id');
			$fieldInstance->set('columntype', $importerType->integer(10)->defaultValue(0)->notNull()->unsigned());
			$fieldInstance->set('table', 'u_yf_productcategory');
			$fieldInstance->set('label', 'FL_MAGENTO_ID');
			$fieldInstance->set('uitype', 7);
			$fieldInstance->set('displaytype', 2);
			$fieldInstance->set('maxlengthtext', '4294967295');
			$fieldInstance->set('typeofdata', 'I~O');
			$fieldInstance->save($blockModel);
		}
		\App\EventHandler::setActive('IStorages_RecalculateStockHandler_Handler', 'IStoragesAfterUpdateStock');
		\App\Cron::updateStatus(\App\Cron::STATUS_ENABLED, 'LBL_MAGENTO');
		header('Location: index.php?parent=Settings&module=Magento&view=List');
	}
}
