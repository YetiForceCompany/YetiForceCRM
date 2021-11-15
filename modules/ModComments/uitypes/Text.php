<?php
/**
 * Text uitype file.
 *
 * @package   UIType
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Kon <a.kon@yetiforce.com>
 */

/**
 * UIType Text Field Class.
 */
class ModComments_Text_UIType extends Vtiger_Text_UIType
{
	/** {@inheritdoc} */
	public function getApiDisplayValue($value, Vtiger_Record_Model $recordModel)
	{
		return \App\Utils\Completions::decode($value, \App\Utils\Completions::FORMAT_TEXT);
	}
}
