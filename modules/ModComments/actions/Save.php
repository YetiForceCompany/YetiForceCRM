<?php
/**
 * ModComments save action file.
 *
 * @package   Action
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * ModComments save action class.
 */
class ModComments_Save_Action extends Vtiger_Save_Action
{
	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
	}
}
