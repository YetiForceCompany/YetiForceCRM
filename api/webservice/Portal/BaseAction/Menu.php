<?php
/**
 * Get elements of menu.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */

namespace Api\Portal\BaseAction;

/**
 * Action to get menu.
 */
class Menu extends \Api\Core\BaseAction
{
	/** @var string[] Allowed request methods */
	public $allowedMethod = ['GET'];

	/**
	 * Get modules list.
	 *
	 * @return string[]
	 */
	public function get()
	{
		return ['items' => \Settings_Menu_Record_Model::getCleanInstance()->getChildMenu($this->controller->app['id'], 0, \Settings_Menu_Record_Model::SRC_API)];
	}
}
