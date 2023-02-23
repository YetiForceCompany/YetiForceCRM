<?php
/**
 * MultiDomain Query Field Class.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Kon <a.kon@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Conditions\QueryFields;

/**
 * Class MultiDomainField.
 */
class MultiDomainField extends BaseField
{
	/** @var string Separator. */
	protected $separator = ',';

	/** {@inheritdoc} */
	public function getValue()
	{
		$valueArray = array_filter(explode($this->separator, $this->value));
		if (\in_array($this->operator, ['e', 'n'])) {
			foreach ($valueArray as $key => $value) {
				$valueArray[$key] = "{$this->separator}{$value}{$this->separator}";
			}
		}

		return $valueArray;
	}

	/** {@inheritdoc} */
	public function getOperator(): string
	{
		return 'a' === $this->operator ? 'c' : $this->operator;
	}

	/** {@inheritdoc} */
	public function operatorE(): array
	{
		return ['or like', $this->getColumnName(), $this->getValue()];
	}

	/** {@inheritdoc} */
	public function operatorN(): array
	{
		return ['not like', $this->getColumnName(), $this->getValue()];
	}

	/** {@inheritdoc} */
	public function operatorC(): array
	{
		return ['like', $this->getColumnName(), $this->getValue()];
	}

	/** {@inheritdoc} */
	public function operatorK(): array
	{
		return ['not like', $this->getColumnName(), $this->getValue()];
	}
}
