<?php
/**
 * WooCommerce webhooks file to handle communication via web services.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\WooCommerce;

/**
 * WooCommerce webhooks class to handle communication via web services.
 */
class Webhooks extends \Api\Core\BaseAction
{
	/** {@inheritdoc}  */
	public $allowedMethod = ['POST'];
	/** @var string WooCommerce webhook resource. */
	protected $resource;
	/** @var string WooCommerce webhook event. */
	protected $event;
	/** @var \App\Integrations\WooCommerce WooCommerce controller */
	protected $connector;

	/** {@inheritdoc}  */
	protected function checkPermission(): void
	{
		if ($source = $this->controller->request->getHeader('x-wc-webhook-source')) {
			foreach (\App\Integrations\WooCommerce\Config::getAllServers() as $serverId => $config) {
				if (0 === (int) $config['status']) {
					continue;
				}
				if ($source === $config['url']) {
					$this->connector = (new \App\Integrations\WooCommerce($serverId));
					break;
				}
			}
			if (empty($this->connector)) {
				throw new \Api\Core\Exception('No WooCommerce found', 405);
			}
		} elseif (!$this->controller->request->has('webhook_id')) {
			throw new \Api\Core\Exception('No WooCommerce found', 405);
		}
		\App\User::setCurrentUserId(\Users::getActiveAdminId());
	}

	/** {@inheritdoc}  */
	protected function checkPermissionToModule(): void
	{
	}

	/**
	 * WooCommerce webhook method.
	 *
	 * @see https://woocommerce.com/document/webhooks/
	 *
	 * @return void
	 *
	 * @OA\Post(
	 *		path="/webservice/WooCommerce/Webhooks",
	 *		description="Retrieve data from webhook",
	 *		summary="WooCommerce webhook",
	 *		@OA\RequestBody(
	 *			required=true,
	 *			description="Contents of the request contains an associative array with the data record.",
	 *		),
	 *		@OA\Response(response=200, description="Contents of the response contains only id"),
	 *		@OA\Response(response=406, description="No input data"),
	 * ),
	 */
	public function post(): void
	{
		$request = $this->controller->request;
		if ($request->has('webhook_id')) {
			return;
		}
		$this->resource = $request->getHeader('x-wc-webhook-resource');
		$this->event = $request->getHeader('x-wc-webhook-event');
		if (empty($this->resource) || !method_exists($this, $this->resource)) {
			throw new \Api\Core\Exception('No action found', 405);
		}
		if ($this->connector->config->get('sync_currency')) {
			$this->connector->getSync('Currency')->process();
		}
		$this->{$this->resource}();
	}

	/**
	 * WooCommerce webhook product method.
	 *
	 * @return void
	 */
	protected function product(): void
	{
		if (!$this->connector->config->get('sync_products')) {
			return;
		}
		$synchronizer = $this->connector->getSync('Product');
		$direction = (int) $synchronizer->config->get('direction_products');
		if (
			($synchronizer::DIRECTION_TWO_WAY === $direction || $synchronizer::DIRECTION_API_TO_YF === $direction)
			&& \App\Module::isModuleActive($synchronizer->getMapModel()->getModule())
		) {
			$request = $this->controller->request;
			if ($synchronizer->config->get('logAll')) {
				$synchronizer->log(
					"Start Webhook {$this->resource}:{$this->event}",
					['X-WC-Webhook-ID' => $request->getHeader('x-wc-webhook-id')]
				);
			}
			switch ($this->event) {
				case 'created':
				case 'updated':
					$this->connector->getSync('ProductAttributes')->process();
					$synchronizer->importProduct($request->getAllRaw());
					break;
				case 'deleted':
				case 'restored':
					$yfId = $synchronizer->getYfId($request->getInteger('id'));
					$recordModel = \Vtiger_Record_Model::getInstanceById($yfId, $synchronizer->getMapModel()->getModule());
					$recordModel->changeState('deleted' === $this->event ? \App\Record::STATE_TRASH : \App\Record::STATE_ACTIVE);
					break;
				default:
					throw new \Api\Core\Exception('Unsupported event', 400);
					break;
			}
			if ($synchronizer->config->get('logAll')) {
				$synchronizer->log("End Webhook {$this->resource}:{$this->event}", null);
			}
		}
	}

	/**
	 * WooCommerce webhook order method.
	 *
	 * @return void
	 */
	protected function order(): void
	{
		if (!$this->connector->config->get('sync_orders')) {
			return;
		}
		$synchronizer = $this->connector->getSync('Orders');
		$direction = (int) $synchronizer->config->get('direction_orders');
		$moduleName = $synchronizer->getMapModel()->getModule();
		if (
			($synchronizer::DIRECTION_TWO_WAY === $direction || $synchronizer::DIRECTION_API_TO_YF === $direction)
			&& \App\Module::isModuleActive($moduleName)
		) {
			$request = $this->controller->request;
			if ($synchronizer->config->get('logAll')) {
				$synchronizer->log(
					"Start Webhook {$this->resource}:{$this->event}",
					['X-WC-Webhook-ID' => $request->getHeader('x-wc-webhook-id')]
				);
			}
			switch ($this->event) {
				case 'created':
				case 'updated':
					$synchronizer->importOrder($request->getAllRaw());
					break;
				case 'deleted':
				case 'restored':
					$yfId = $synchronizer->getYfId($request->getInteger('id'));
					$recordModel = \Vtiger_Record_Model::getInstanceById($yfId, $moduleName);
					$recordModel->changeState('deleted' === $this->event ? \App\Record::STATE_TRASH : \App\Record::STATE_ACTIVE);
					break;
				default:
					throw new \Api\Core\Exception('Unsupported event', 400);
					break;
			}
			if ($synchronizer->config->get('logAll')) {
				$synchronizer->log("End Webhook {$this->resource}:{$this->event}", null);
			}
		}
	}
}
