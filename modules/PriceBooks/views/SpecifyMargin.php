<?php
/**
 * Specify margin view class.
 *
 * @package View
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Sołek <a.solek@yetiforce.com>
 * @author Arkadiusz Adach <a.adach@yetiforce.com>
 */

/**
 * Class for setting margins.
 */
class PriceBooks_SpecifyMargin_View extends \App\Controller\Modal
{
	/** {@inheritdoc} */
	public $modalSize = 'modal-md';

	/** {@inheritdoc} */
	public $modalIcon = '';

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		if (!Users_Privileges_Model::getCurrentUserPrivilegesModel()->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$this->getViewer($request)->view('Modals/SpecifyMargin.tpl', $request->getModule());
	}

	/** {@inheritdoc} */
	public function getPageTitle(App\Request $request)
	{
		return \App\Language::translate('LBL_SPECIFY_THE_MARGIN', $request->getModule());
	}
}
