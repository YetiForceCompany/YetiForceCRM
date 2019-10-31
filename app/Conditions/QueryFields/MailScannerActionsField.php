<?php
/**
 * Mail scanner actions query file.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */

namespace App\Conditions\QueryFields;

/**
 * Mail scanner actions query field class.
 */
class MailScannerActionsField extends MultipicklistField
{
	/**
	 * {@inheritdoc}
	 */
	protected $separator = ',';
}
