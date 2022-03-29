<?php
/**
 * Transfer ownership modal view file.
 *
 * @package   View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Transfer ownership modal view class.
 */
class Vtiger_TransferOwnership_View extends \App\Controller\Modal
{
	/** {@inheritdoc} */
	public $modalSize = '';

	/** {@inheritdoc} */
	protected $pageTitle = 'LBL_TRANSFER_OWNERSHIP';

	/** {@inheritdoc} */
	public $modalIcon = 'yfi yfi-change-of-owner';

	/** {@inheritdoc} */
	public $showFooter = false;

	/** @var array|null Parent record id. */
	public $parent;

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		$moduleName = $request->getModule();
		$currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$sourceView = $request->getByType('sourceView');
		if (
			$currentUserPrivilegesModel->hasModuleActionPermission($moduleName, 'EditView')
			&& ('Detail' !== $sourceView && 'List' !== $sourceView)
			|| ('List' === $sourceView && !$currentUserPrivilegesModel->hasModuleActionPermission($moduleName, 'MassTransferOwnership'))
			|| ('Detail' === $sourceView && !$currentUserPrivilegesModel->hasModuleActionPermission($moduleName, 'DetailTransferOwnership'))
		) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * Users list modal.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function process(App\Request $request): void
	{
		$moduleName = $request->getModule();
		$transferModel = Vtiger_TransferOwnership_Model::getInstance($moduleName);
		$viewer = $this->getViewer($request);
		$viewer->assign('REL_BY_FIELDS', $transferModel->getRelationsByFields());
		$viewer->assign('REL_BY_RELATEDLIST', $transferModel->getRelationsByRelatedList());
		$viewer->assign('SKIP_MODULES', $transferModel->getSkipModules());
		$viewer->view('Modals/TransferOwnership.tpl', $moduleName);
	}
}
