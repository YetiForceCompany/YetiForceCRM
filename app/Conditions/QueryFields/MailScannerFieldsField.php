<?php
/**
 * MailScannerFields query field.
 *
 * @package UIType
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */

namespace App\Conditions\QueryFields;

/**
 * MailScannerFieldsField class.
 */
class MailScannerFieldsField extends MultiListFieldsField
{
	/**
	 * {@inheritdoc}
	 */
	protected $separator = ',';
}
