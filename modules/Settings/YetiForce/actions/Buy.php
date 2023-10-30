<?php

/**
 * YetiForce register action class file.
 *
 * @package   Settings
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Class for YetiForce registration actions.
 */
class Settings_YetiForce_Buy_Action extends Settings_Vtiger_Save_Action
{
	/**
	 * Process user request.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function process(App\Request $request)
	{
		try {
			$orderId = $message = '';
			$responseType = 'success';
			$order = new \App\YetiForce\Order();
			$order->setPackageId($request->getByType('packageId', \App\Purifier::ALNUM2));
			foreach ($order->getFieldInstances() as $field) {
				$fieldName = $field->getName();
				if ($request->has($fieldName) && !$request->isEmpty($fieldName)) {
					$value = $request->getRaw($fieldName);
					if ($value && preg_match('/[^A-Za-zÀ-ž\W\d\s]+/u', (string) $value, $mas)) {
						throw new \App\Exceptions\AppException('ERR_PLEASE_USE_LATIN_CHARACTERS');
					}
					$value = $request->getByType($fieldName, $field->get('purifyType'));
					$field->getUITypeModel()->validate($value, true);
					$order->set($fieldName, $field->getDBValue($value));
				} else {
					throw new \App\Exceptions\AppException('LBL_NOT_FILLED_MANDATORY_FIELDS');
				}
			}
			$result = $order->send();
			if ($error = $order->getError()) {
				throw new \App\Exceptions\AppException($error);
			}
			if ($result) {
				$orderId = $order->getId();
			}
		} catch (\App\Exceptions\AppException $e) {
			$result = false;
			$responseType = 'error';
			$message = $e->getDisplayMessage();
		} catch (\Throwable $e) {
			$result = false;
			$responseType = 'error';
			$message = $e->getMessage();
		}

		$response = new Vtiger_Response();
		$response->setResult([
			'success' => $result ?? false,
			'message' => $message ?? '',
			'type' => $responseType,
			'orderId' => $orderId
		]);
		$response->emit();
	}
}
