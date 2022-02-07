<?php
/**
 * Trait expose method controller file.
 *
 * @package   Controller
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Controller;

/**
 * Trait expose method controller class.
 */
trait ExposeMethod
{
	/**
	 * Control the exposure of methods to be invoked from client (kind-of RPC).
	 *
	 * @var string[]
	 */
	protected $exposedMethods = [];

	/**
	 * Function that will expose methods for external access.
	 *
	 * @param string $name - method name
	 */
	protected function exposeMethod($name)
	{
		if (!\in_array($name, $this->exposedMethods)) {
			$this->exposedMethods[] = $name;
		}
	}

	/**
	 * Function checks if the method is exposed for client usage.
	 *
	 * @param string $name - method name
	 *
	 * @return bool
	 */
	public function isMethodExposed($name)
	{
		if (\in_array($name, $this->exposedMethods)) {
			return true;
		}
		return false;
	}

	/**
	 * Function invokes exposed methods for this class.
	 *
	 * @param string       $name    - method name
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return type
	 */
	public function invokeExposedMethod()
	{
		$parameters = \func_get_args();
		$name = array_shift($parameters);
		if (!empty($name) && $this->isMethodExposed($name)) {
			return \call_user_func_array([$this, $name], $parameters);
		}
		throw new \App\Exceptions\AppException('ERR_NOT_ACCESSIBLE', 406);
	}

	/**
	 * Process action.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		if ($mode = $request->getMode()) {
			$this->invokeExposedMethod($mode, $request);
		}
	}
}
