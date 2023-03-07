<?php
/**
 * Settings WooCommerce active action file.
 *
 * @package Settings.Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Settings WooCommerce active action class.
 */
class Settings_WooCommerce_Active_Action extends Settings_Vtiger_Save_Action
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		Settings_WooCommerce_Activation_Model::activate();
		header('Location: index.php?parent=Settings&module=WooCommerce&view=List');
	}
}
