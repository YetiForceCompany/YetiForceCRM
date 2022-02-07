<?php
/**
 * Magento active action file.
 *
 * @package Settings.Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Magento active action class.
 */
class Settings_Magento_Active_Action extends Settings_Vtiger_Save_Action
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$sSingleOrderTabId = \App\Module::getModuleId('SSingleOrders');
		$fInvoiceTabId = \App\Module::getModuleId('FInvoice');
		$productCategoryId = \App\Module::getModuleId('ProductCategory');
		$importerType = new \App\Db\Importers\Base();
		$fields = [
			'magento_server_id' => [
				'columntype' => $importerType->integer(10)->defaultValue(0)->notNull()->unsigned(),
				'label' => 'FL_MAGENTO_SERVER',
				'uitype' => 325,
				'displaytype' => 2,
				'maximumlength' => '4294967295',
				'typeofdata' => 'I~O'
			], 'magento_id' => [
				'columntype' => $importerType->integer(10)->defaultValue(0)->notNull()->unsigned(),
				'label' => 'FL_MAGENTO_ID',
				'uitype' => 7,
				'displaytype' => 2,
				'maximumlength' => '4294967295',
				'typeofdata' => 'I~O'
			], 'status_magento' => [
				'columntype' => $importerType->stringType(255)->defaultValue(''),
				'label' => 'FL_MAGENTO_STATUS',
				'uitype' => 16,
				'displaytype' => 2,
				'maximumlength' => '255',
				'typeofdata' => 'V~O',
				'picklistValues' => ['PLL_PENDING', 'PLL_PENDING_PAYMENT', 'PLL_PENDING_PAYPAL',  'PLL_PAID', 'PLL_PROCESSING', 'PLL_ON_HOLD', 'PLL_SEND', 'PLL_FRAUD', 'PLL_PAYMENT_REVIEW', 'PLL_PAYPAL_CANCELED_REVERSAL', 'PLL_PAYPAL_REVERSED', 'PLL_CANCELLED', 'PLL_CLOSED', 'PLL_COMPLETE']
			]
		];
		$fieldsExists = (new \App\Db\Query())->select(['fieldname'])->from('vtiger_field')->where(['tabid' => $sSingleOrderTabId, 'fieldname' => array_keys($fields)])->column();
		if ($fieldsToAdd = array_diff_key($fields, array_flip($fieldsExists))) {
			$blockModel = vtlib\Block::getInstance('LBL_CUSTOM_INFORMATION', 'SSingleOrders');
			if (!$blockModel) {
				$blocks = vtlib\Block::getAllForModule(vtlib\Module::getInstance('SSingleOrders'));
				$blockModel = current($blocks);
			}
			$this->addFields($fieldsToAdd, $blockModel);
		}
		unset($fields['status_magento']);
		$fieldsExists = (new \App\Db\Query())->select(['fieldname'])->from('vtiger_field')->where(['tabid' => $fInvoiceTabId, 'fieldname' => array_keys($fields)])->column();
		if ($fieldsToAdd = array_diff_key($fields, array_flip($fieldsExists))) {
			$blockModel = vtlib\Block::getInstance('LBL_CUSTOM_INFORMATION', 'FInvoice');
			if (!$blockModel) {
				$blocks = vtlib\Block::getAllForModule(vtlib\Module::getInstance('FInvoice'));
				$blockModel = current($blocks);
			}
			$this->addFields($fieldsToAdd, $blockModel);
		}
		$fieldsExists = (new \App\Db\Query())->select(['fieldname'])->from('vtiger_field')->where(['tabid' => $productCategoryId, 'fieldname' => array_keys($fields)])->column();
		if ($fieldsToAdd = array_diff_key($fields, array_flip($fieldsExists))) {
			$blockModel = vtlib\Block::getInstance('LBL_BASIC_INFORMATION', 'ProductCategory');
			if (!$blockModel) {
				$blocks = vtlib\Block::getAllForModule(vtlib\Module::getInstance('ProductCategory'));
				$blockModel = current($blocks);
			}
			$this->addFields($fieldsToAdd, $blockModel);
		}
		\App\EventHandler::setActive('IStorages_RecalculateStockHandler_Handler', 'IStoragesAfterUpdateStock');
		\App\Cron::updateStatus(\App\Cron::STATUS_ENABLED, 'LBL_MAGENTO');
		header('Location: index.php?parent=Settings&module=Magento&view=List');
	}

	/**
	 * Add fields.
	 *
	 * @param array       $fieldsToAdd
	 * @param vtlib\Block $blockModel
	 *
	 * @return void
	 */
	public function addFields(array $fieldsToAdd, vtlib\Block $blockModel): void
	{
		foreach ($fieldsToAdd as $fieldName => $fieldData) {
			$fieldInstance = new Vtiger_Field_Model();
			$fieldInstance->set('name', $fieldName);
			$fieldInstance->set('column', $fieldName);
			$fieldInstance->set('columntype', $fieldData['columntype']);
			$fieldInstance->set('table', $blockModel->module->basetable);
			$fieldInstance->set('label', $fieldData['label']);
			$fieldInstance->set('uitype', $fieldData['uitype']);
			$fieldInstance->set('displaytype', $fieldData['displaytype']);
			$fieldInstance->set('maximumlength', $fieldData['maximumlength']);
			$fieldInstance->set('typeofdata', $fieldData['typeofdata']);
			$fieldInstance->save($blockModel);
			if (isset($fieldData['picklistValues'])) {
				$fieldInstance->setNoRolePicklistValues($fieldData['picklistValues']);
			}
		}
	}
}
