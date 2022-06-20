<?php
/**
 * Settings WAPRO ERP activation action file.
 *
 * @package Settings.Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Settings WAPRO ERP activation action class.
 */
class Settings_Wapro_Activation_Action extends Settings_Vtiger_Save_Action
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		Settings_Wapro_Activation_Model::activate();
		header('Location: index.php?parent=Settings&module=Wapro&view=List');
	}
}
