<?php
/**
 * MailScannerFields query field.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */

namespace App\Conditions\QueryFields;

/**
 * MailScannerFieldsField class.
 */
class MailScannerFieldsField extends MultiListFieldsField
{
	/** {@inheritdoc} */
	protected $separator = ',';
}
