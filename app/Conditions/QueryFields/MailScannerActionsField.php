<?php
/**
 * Mail scanner actions query file.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */

namespace App\Conditions\QueryFields;

/**
 * Mail scanner actions query field class.
 */
class MailScannerActionsField extends MultipicklistField
{
	/** {@inheritdoc} */
	protected $separator = ',';
}
