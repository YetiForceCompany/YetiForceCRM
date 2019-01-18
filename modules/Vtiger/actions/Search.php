<?php

/**
 * Base search action class.
 *
 * @package   Action
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Base Search Action Class.
 */
class Vtiger_Search_Action extends \App\Controller\Action
{
	use \App\Controller\ExposeMethod;

	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(\App\Request $request)
	{
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('owners');
	}

	/**
	 * Search owners.
	 *
	 * @param \App\Request $request
	 */
	public function owners(\App\Request $request)
	{
		$owner = App\Fields\Owner::getInstance();
		$owner->showRoleName = true;
		$owner->find($request->getByType('value', 'Text'));
		$data = [];
		if ($users = $owner->getAccessibleUsers('private', 'owner')) {
			foreach ($users as $key => $value) {
				$data[] = ['type' => 'Users', 'id' => $key, 'name' => $value, 'image' => \App\User::getImageById($key)['url'] ?? ''];
			}
		}
		$grup = $owner->getAccessibleGroups('private', 'owner', true);
		if (!empty($grup)) {
			foreach ($grup as $key => $value) {
				$data[] = ['type' => 'Groups', 'id' => $key, 'name' => $value];
			}
		}
		$response = new Vtiger_Response();
		$response->setResult($data);
		$response->emit();
	}
}
