<?php
/**
 * OAuth controller file to handle communication via web services.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>m>
 */

namespace Api\OAuth;

/**
 * OAuth controller class to handle communication via web services.
 */
class Controller extends \Api\Controller
{
	/** {@inheritdoc}  */
	protected function getActionClassName(): string
	{
		$module = $this->request->getModule('module');
		$className = "Api\\OAuth\\BaseAction\\{$module}";
		if (!$module || !class_exists($className)) {
			throw new \Api\Core\Exception('No action found', 405);
		}

		return $className;
	}

	/** {@inheritdoc}  */
	public function handleError(\Throwable $e): void
	{
		http_response_code($e->getCode());
		echo 'Internal Server Error';
	}
}
