<?php
/**
 * OAuth controller file to handle communication via web services.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace Api\OAuth;

use OpenApi\Annotations as OA;

/**
 * OAuth controller class to handle communication via web services.
 *
 * @OA\Info(
 * 		title="YetiForce API for OAuth. Type: OAuth",
 * 		description="",
 * 		version="0.1",
 * 		termsOfService="https://yetiforce.com/",
 *   	@OA\Contact(email="devs@yetiforce.com", name="Devs API Team", url="https://yetiforce.com/"),
 *   	@OA\License(name="YetiForce Public License", url="https://yetiforce.com/en/yetiforce/license"),
 * )
 *	@OA\ExternalDocumentation(
 *		description="Platform API Interactive Docs",
 *		url="https://doc.yetiforce.com/api/?urls.primaryName=OAuth"
 *	),
 * @OA\Server(description="Demo server of the development version", url="https://gitdeveloper.yetiforce.com")
 * @OA\Server(description="Demo server of the latest stable version", url="https://gitstable.yetiforce.com")
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
		if ($e instanceof \Api\Core\Exception) {
			$e->logError();
		}
		if (is_numeric($e->getCode())) {
			http_response_code($e->getCode());
		}
		echo 'Internal Server Error';
	}
}
