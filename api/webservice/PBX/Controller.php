<?php
/**
 * PBX controller file to handle communication via web services.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\PBX;

/**
 * PBX controller class to handle communication via web services.
 */
class Controller extends \Api\Controller
{
	/** {@inheritdoc}  */
	protected function getActionClassName(): string
	{
		$this->request->delete('_container');
		if ($moduleName = $this->request->getByType('module', \App\Purifier::ALNUM)) {
			$actionName = $moduleName;
		} else {
			$actionName = $this->request->getByType('action', 'Alnum');
		}
		$className = "Api\\PBX\\$actionName";
		if (class_exists($className)) {
			return $className;
		}
		throw new \Api\Core\Exception('No action found', 405);
	}

	/** {@inheritdoc}  */
	public function handleError(\Throwable $e): void
	{
		if (is_numeric($e->getCode())) {
			http_response_code($e->getCode());
		}
		echo 'Internal Server Error';
		file_put_contents(__DIR__ . '/_Genesys_' . date('Y-m-d-H') . '_error.log', print_r([
			'datetime' => date('Y-m-d H:i:s'),
			'method' => \App\Request::getRequestMethod(),
			'REQUEST' => $_REQUEST,
			'error' => $e->__toString()
		], true), FILE_APPEND);
	}
}
