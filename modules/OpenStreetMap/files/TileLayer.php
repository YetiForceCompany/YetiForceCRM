<?php
/**
 * Tile layer file.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Tile layer class.
 */
class OpenStreetMap_TileLayer_File extends Vtiger_Basic_File
{
	/** {@inheritdoc} */
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
		if (!\App\RequestUtil::isNetConnection()) {
			$this->error();
			return false;
		}
		$product = \App\YetiForce\Register::getProducts('YetiForceMap');
		if ((empty($product['params']['login']) || empty($product['params']['pass'])) && empty($product['params']['token'])) {
			$this->error('map_server_not_purchased');
			return false;
		}
		$url = str_replace(['{z}', '{x}', '{y}'], [
			$request->getByType('z', 'Integer'),
			$request->getByType('x', 'Integer'),
			$request->getByType('y', 'Integer'),
		], 'https://osm-tile.yetiforce.eu/tile/{z}/{x}/{y}.png');
		$options = [
			'timeout' => 60,
			'headers' => [
				'InsKey' => \App\YetiForce\Register::getInstanceKey()
			]
		];
		if (isset($product['params']['token'])) {
			$url += '?yf_token=' . $product['params']['token'];
		} else {
			$options['auth'] = [$product['params']['login'], $product['params']['pass']];
		}
		try {
			\App\Log::beginProfile("GET|TileLayer::get|{$url}", __NAMESPACE__);
			$response = (new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))->request('GET', $url, $options);
			\App\Log::endProfile("GET|TileLayer::get|{$url}", __NAMESPACE__);
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
			$this->error();
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
