<?php

namespace App\Conditions\QueryFields;

/**
 * Taxes Query Field Class.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class TaxesField extends MultipicklistField
{
	/**
	 * Separator.
	 *
	 * @var string
	 */
	protected $separator = ',';
}
