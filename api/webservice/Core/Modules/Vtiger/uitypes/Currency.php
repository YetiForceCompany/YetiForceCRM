<?php
/**
 * The file contains: UIType Currency Field Class.
 *
 * @package   Api
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */

namespace Api\Core\Modules\Vtiger\UiTypes;

/**
 * UIType Currency Field Class.
 */
class Currency extends \Vtiger_Currency_UIType
{
	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		return $this->getDBValue($value);
	}
}
