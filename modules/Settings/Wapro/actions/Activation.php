<?php
/**
 * Settings WAPRO ERP activation action file.
 *
 * @package Settings.Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Settings WAPRO ERP activation action class.
 */
class Settings_Wapro_Activation_Action extends Settings_Vtiger_Save_Action
{
	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		parent::checkPermission($request);
		if (!\App\YetiForce\Register::getProduct('YetiForceWaproERP')) {
			throw new \App\Exceptions\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		Settings_Wapro_Activation_Model::activate();
		header('Location: index.php?parent=Settings&module=Wapro&view=List');
	}
}
