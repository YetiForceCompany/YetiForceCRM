<?php
/**
 * Returns price of delivery.
 *
 * @package Api
 *
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */

namespace Api\Portal\SSingleOrders;

/**
 * Delivery class.
 */
class Delivery extends \Api\Core\BaseAction
{
	/** @var string[] Allowed request methods */
	public $allowedMethod = ['GET'];

	/**
	 * {@inheritdoc}
	 */
	public function get(): array
	{
		return ['price' => 0];
	}
}
