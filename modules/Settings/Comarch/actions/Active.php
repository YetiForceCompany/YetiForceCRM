<?php
/**
 * Settings Comarch active action file.
 *
 * @package Settings.Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Settings Comarch active action class.
 */
class Settings_Comarch_Active_Action extends Settings_Vtiger_Save_Action
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		Settings_Comarch_Activation_Model::activate();
		header('Location: index.php?parent=Settings&module=Comarch&view=List');
	}
}
