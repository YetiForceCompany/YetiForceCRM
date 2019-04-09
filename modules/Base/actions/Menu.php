<?php
/**
 * Menu action file.
 *
 * @package   Action
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace Modules\Base\Actions;

/**
 * Menu action class.
 */
class Menu extends \App\Controller\Action
{
	/**
	 * {@inheritdoc}
	 */
	public function checkPermission()
	{
		if (!\App\User::isLoggedIn()) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function process()
	{
		$userModel = \App\User::getCurrentUserModel();
		$menu = \Modules\Base\Models\Menu::getMenu($userModel->getRole());
		$this->response->set($menu);
	}
}
