<?php
/**
 * UIType MultiImage Field Class.
 *
 * @package   Api
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */

namespace Api\Core\Modules\Vtiger\UiTypes;

/**
 * UIType MultiImage Field Class.
 */
class MultiImage extends \Vtiger_MultiImage_UIType
{
	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		$value = \App\Json::decode($value);
		$returnValue = [];
		if ($value) {
			foreach ($value as $item) {
				$returnValue[] = base64_encode(file_get_contents($item['path']));
			}
		}
		return $returnValue;
	}
}
