<?php
/**
 * Action to check configuration.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */

namespace Api\Portal\BaseAction;

/**
 * Install class.
 */
class Install extends \Api\Core\BaseAction
{
	/** @var string[] Allowed request methods */
	public $allowedMethod = ['PUT'];

	/**
	 * {@inheritdoc}
	 */
	public function checkPermission()
	{
		return true;
	}

	/**
	 * Get modules list.
	 *
	 * @return string[]
	 */
	public function put()
	{
		return $this->controller->request->getRaw('data');
	}
}
