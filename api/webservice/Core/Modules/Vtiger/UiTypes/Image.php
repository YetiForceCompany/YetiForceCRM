<?php
/**
 * UIType Image Field Class.
 *
 * @package   Api
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */

namespace Api\Core\Modules\Vtiger\UiTypes;

/**
 * UIType Image Field Class.
 */
class Image extends \Vtiger_Image_UIType
{
	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		$value = \App\Json::decode($value);
		$returnValue = '';
		if ($value) {
			$returnValue = base64_encode(file_get_contents(current($value)['path']));
		}
		return $returnValue;
	}
}
