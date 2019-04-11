<?php
/**
 * Environment action file.
 *
 * @package   Action
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Modules\Users\Actions;

/**
 * Environment action class.
 */
class Env extends \App\Controller\Action
{
	/**
	 * {@inheritdoc}
	 */
	public function checkPermission()
	{
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function process()
	{
		$mode = \App\User::isLoggedIn() ? 'update' : 'loginPage';
		$env = [];
		switch ($this->request->getMode()) {
	case 'Env':
	$env = \App\Layout\Env::getEnv($mode);
	break;
	case 'Language':
	$env = \App\Layout\Env::getLanguage($mode);
	break;
	case 'Debug':
	$env = \App\Layout\Env::getDebug($mode);
	break;
	case 'Users':
	$env = \App\Layout\Env::getUser($mode);
	break;
	}
		$this->response->setEnv($env);
	}
}
