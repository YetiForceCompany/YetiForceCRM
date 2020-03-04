<?php
/**
 * Tile layer file.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Tile layer class.
 */
class OpenStreetMap_TileLayer_File extends Vtiger_Basic_File
{
	/**
	 * {@inheritdoc}
	 */
	public function getCheckPermission(App\Request $request)
	{
		if (!\App\Privilege::isPermitted('OpenStreetMap')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		return true;
	}

	/**
	 * Download layer and show.
	 *
	 * @param \App\Request $request
	 *
	 * @return bool
	 */
	public function get(App\Request $request)
	{
		$product = \App\YetiForce\Register::getProducts('YetiForceMap');
		if (!\App\RequestUtil::isNetConnection() || empty($product['params']['login']) || empty($product['params']['pass'])) {
			$this->error();
			return false;
		}
		$url = str_replace(['{z}', '{x}', '{y}'], [
			$request->getByType('z', 'Integer'),
			$request->getByType('x', 'Integer'),
			$request->getByType('y', 'Integer'),
		], 'https://osm-tile.yetiforce.eu/tile/{z}/{x}/{y}.png');
		try {
			$response = (new \GuzzleHttp\Client(\array_merge(\App\RequestHttp::getOptions(), ['timeout' => 30, 'InsKey' => \App\YetiForce\Register::getInstanceKey()])))
				->request('GET', $url, ['auth' => [$product['params']['login'], $product['params']['pass']]]);
			if (200 !== $response->getStatusCode()) {
				\App\Log::error($url . ' | Error: ' . $response->getReasonPhrase(), __CLASS__);
				$this->error();
				return false;
			}
			$body = $response->getBody();
			header('pragma: public');
			header('cache-control: max-age=86400, public');
			header('expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 86400));
			header('content-type: image/png');
			header('content-transfer-encoding: binary');
			header('content-length: ' . $body->getSize());
			echo $body->getContents();
		} catch (\Throwable $ex) {
			\App\Log::error($url . ' | Error: ' . $ex->getMessage(), __CLASS__);
		}
	}

	/**
	 * Error function.
	 *
	 * @param string $type
	 *
	 * @return void
	 */
	public function error(string $type = 'map_server_unavailable'): void
	{
		$fileName = ROOT_DIRECTORY . "/public_html/layouts/basic/images/{$type}.png";
		header('pragma: public');
		header('cache-control: max-age=86400, public');
		header('expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 86400));
		header('content-type: ' . \App\Fields\File::getMimeContentType($fileName));
		header('content-transfer-encoding: binary');
		header('content-length: ' . filesize($fileName));
		readfile($fileName);
	}
}
