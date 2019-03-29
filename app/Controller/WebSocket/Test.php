<?php
/**
 * Test container web socket controller file.
 *
 * @package   Controller
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Controller\WebSocket;

/**
 * Test container web socket controller class.
 *
 * @internal
 * @coversNothing
 */
final class Test extends Base
{
	/**
	 * {@inheritdoc}
	 */
	public function process()
	{
		$this->pushRaw('Test OK');
		$this->pushRaw('Request body: ' . $this->getFrame()->data);
	}
}
