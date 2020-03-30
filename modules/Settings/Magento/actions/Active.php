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
		if (!(new \App\Db\Query())->from('vtiger_field')->where(['tabid' => \App\Module::getModuleId('SSingleOrders'), 'fieldname' => 'magento_id'])->exists()) {
			$blockModel = Vtiger_Block_Model::getInstance('LBL_CUSTOM_INFORMATION', 'SSingleOrders');
			$blockModel = vtlib\Block::getInstance('LBL_CUSTOM_INFORMATION', 'SSingleOrders');
			if (!$blockModel) {
				$blocks = vtlib\Block::getAllForModule(vtlib\Module::getInstance('SSingleOrders'));
				$blockModel = current($blocks);
			}
			$fieldInstance = new vtlib\Field();
			$fieldInstance->name = 'magento_id';
			$fieldInstance->table = 'u_yf_ssingleorders';
			$fieldInstance->label = 'FL_MAGENTO_SERVER';
			$fieldInstance->column = 'magento_id';
			$fieldInstance->uitype = 325;
			$fieldInstance->displaytype = 9;
			$fieldInstance->maxlengthtext = '2147483647';
			$fieldInstance->typeofdata = 'I~O';
			$fieldInstance->save($blockModel);
		}
		if (!(new \App\Db\Query())->from('vtiger_field')->where(['tabid' => \App\Module::getModuleId('FInvoice'), 'fieldname' => 'magento_id'])->exists()) {
			$blockModel = Vtiger_Block_Model::getInstance('LBL_CUSTOM_INFORMATION', 'FInvoice');
			$blockModel = vtlib\Block::getInstance('LBL_CUSTOM_INFORMATION', 'FInvoice');
			if (!$blockModel) {
				$blocks = vtlib\Block::getAllForModule(vtlib\Module::getInstance('FInvoice'));
				$blockModel = current($blocks);
			}
			$fieldInstance = new vtlib\Field();
			$fieldInstance->name = 'magento_id';
			$fieldInstance->table = 'u_yf_finvoice';
			$fieldInstance->label = 'FL_MAGENTO_SERVER';
			$fieldInstance->column = 'magento_id';
			$fieldInstance->uitype = 325;
			$fieldInstance->displaytype = 9;
			$fieldInstance->maxlengthtext = '2147483647';
			$fieldInstance->typeofdata = 'I~O';
			$fieldInstance->save($blockModel);
		}
		header('Location: index.php?parent=Settings&module=Magento&view=List');
	}
}
