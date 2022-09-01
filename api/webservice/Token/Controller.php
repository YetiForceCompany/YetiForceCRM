<?php
/**
 * Token controller file to handle communication via web services.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\Token;

/**
 * Token controller class to handle communication via web services.
 */
class Controller extends \Api\Controller
{
	/** {@inheritdoc}  */
	protected function getActionClassName(): string
	{
		return 'Api\Token\Action';
	}

	/** {@inheritdoc}  */
	public function handleError(\Throwable $e): void
	{
		http_response_code($e->getCode());
		echo 'Internal Server Error';
	}
}
