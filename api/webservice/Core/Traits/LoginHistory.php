<?php

/**
 * Login history trait.
 *
 * @package   API
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\Core\Traits;

/**
 * Login history trait.
 */
trait LoginHistory
{
	/**
	 * Function to store the login history.
	 *
	 * @param array $data
	 *
	 * @return void
	 */
	protected function saveLoginHistory(array $data): void
	{
		\App\Db::getInstance('webservice')->createCommand()
			->insert($this->controller->app['tables']['loginHistory'], array_merge([
				'time' => date('Y-m-d H:i:s'),
				'ip' => $this->controller->request->getServer('REMOTE_ADDR'),
				'agent' => \App\TextParser::textTruncate($this->controller->request->getServer('HTTP_USER_AGENT', '-'), 100, false),
				'user_name' => $this->controller->request->has('userName') ? $this->controller->request->get('userName') : $this->userData['user_name'],
				'user_id' => $this->userData['id'] ?? null,
			],
			$data))->execute();
	}
}
