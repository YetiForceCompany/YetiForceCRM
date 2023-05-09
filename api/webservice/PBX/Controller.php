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
		$stacktrace = rtrim(str_replace(ROOT_DIRECTORY . \DIRECTORY_SEPARATOR, '', $e->__toString()), PHP_EOL);
		$params = print_r(array_merge(
			$this->request->getAllRaw(), [
				'ip' => $_SERVER['REMOTE_ADDR'],
			]), true) . $stacktrace;
		\App\DB::getInstance('log')->createCommand()
			->insert('l_#__pbx', [
				'error' => true,
				'time' => date('Y-m-d H:i:s'),
				'driver' => $this->request->getModule() . '::' . \App\Request::getRequestMethod(),
				'message' => \App\TextUtils::textTruncate($e->getMessage(), 255),
				'params' => \App\TextUtils::textTruncate($params, 65535),
			])->execute();

		if ($e instanceof \Api\Core\Exception) {
			$e->showError();
		} else {
			if ($e instanceof \App\Exceptions\AppException) {
				$ex = new \Api\Core\Exception($e->getDisplayMessage(), $e->getCode(), $e);
			} else {
				$ex = new \Api\Core\Exception($e->getMessage(), $e->getCode(), $e);
			}
			$ex->showError();
		}
	}
}
