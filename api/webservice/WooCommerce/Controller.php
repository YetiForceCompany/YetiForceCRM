<?php
/**
 * WooCommerce controller file to handle communication via web services.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\WooCommerce;

use OpenApi\Annotations as OA;

/**
 * WooCommerce controller class to handle communication via web services.
 *
 * @OA\Info(
 * 		title="YetiForce API for WooCommerce. Type: WooCommerce",
 * 		description="",
 * 		version="0.1",
 * 		termsOfService="https://yetiforce.com/",
 *   	@OA\Contact(email="devs@yetiforce.com", name="Devs API Team", url="https://yetiforce.com/"),
 *   	@OA\License(name="YetiForce Public License", url="https://yetiforce.com/en/yetiforce/license"),
 * )
 *	@OA\ExternalDocumentation(
 *		description="Platform API Interactive Docs",
 *		url="https://doc.yetiforce.com/api/?urls.primaryName=WooCommerce"
 *	),
 * @OA\Server(description="Demo server of the development version", url="https://gitdeveloper.yetiforce.com")
 * @OA\Server(description="Demo server of the latest stable version", url="https://gitstable.yetiforce.com")
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
		$className = "Api\\WooCommerce\\$actionName";
		if (class_exists($className)) {
			return $className;
		}
		throw new \Api\Core\Exception('No action found', 405);
	}

	/** {@inheritdoc}  */
	public function handleError(\Throwable $e): void
	{
		if ($e instanceof \Api\Core\Exception) {
			$e->logError();
		}
		$stacktrace = rtrim(str_replace(ROOT_DIRECTORY . \DIRECTORY_SEPARATOR, '', $e->__toString()), PHP_EOL);
		$params = print_r(array_merge(
			$this->request->getAllRaw(), [
				'ip' => $_SERVER['REMOTE_ADDR'],
			]), true) . $stacktrace;

		\App\DB::getInstance('log')->createCommand()
			->insert(\App\Integrations\WooCommerce::LOG_TABLE_NAME, [
				'error' => true,
				'time' => date('Y-m-d H:i:s'),
				'message' => 'Webhook: ' . \App\TextUtils::textTruncate($e->getMessage(), 255),
				'params' => \App\TextUtils::textTruncate($params, 65535),
				'trace' => \App\TextUtils::textTruncate($stacktrace, 65535),
			])->execute();
	}
}
