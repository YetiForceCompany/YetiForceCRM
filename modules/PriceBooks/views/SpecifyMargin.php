<?php
/**
 * Specify margin view class.
 *
 * @package View
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Sołek <a.solek@yetiforce.com>
 * @author Arkadiusz Adach <a.adach@yetiforce.com>
 */

/**
 * Class for setting margins.
 */
class PriceBooks_SpecifyMargin_View extends \App\Controller\Modal
{
	/**
	 * {@inheritdoc}
	 */
	public $modalSize = 'modal-md';

	/**
	 * {@inheritdoc}
	 */
	public $modalIcon = 'fas fa-dollar-sign';

	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(App\Request $request)
	{
		$currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$request->isEmpty('related_parent_module') && !$currentUserPrivilegesModel->hasModulePermission($request->getByType('related_parent_module', 2))) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		if (!$request->isEmpty('src_module') && (!$currentUserPrivilegesModel->isAdminUser() && !$currentUserPrivilegesModel->hasModulePermission($request->getByType('src_module', 2)))) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		if (!$request->isEmpty('related_parent_id', true) && !\App\Privilege::isPermitted($request->getByType('related_parent_module', 2), 'DetailView', $request->getInteger('related_parent_id'))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		if (!$request->isEmpty('src_record', true) && !\in_array($request->getByType('src_module', 2), ['Users', 'WebserviceUsers']) && !\App\Privilege::isPermitted($request->getByType('src_module', 2), 'DetailView', $request->getInteger('src_record'))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
		$this->getViewer($request)->view('Modals/SpecifyMargin.tpl', $request->getModule());
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPageTitle(App\Request $request)
	{
		return \App\Language::translate('LBL_SPECIFY_THE_MARGIN', $request->getModule());
	}
}
