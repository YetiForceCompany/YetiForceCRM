<?php
/**
 * Multi email condition record field class.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Conditions\RecordFields;

/**
 * Multi email condition record field class.
 */
class MultiEmailField extends BaseField
{
	/** @var string Separator. */
	protected $separator = ',';

	/** {@inheritdoc} */
	public function getValue(): array
	{
		$valueArray = [];
		if (!empty(parent::getValue()) && \in_array($this->operator, ['e', 'n'])) {
			foreach (\App\Json::decode(parent::getValue()) as $value) {
				if (!empty($value[$this->operator])) {
					$valueArray[] = $value[$this->operator];
				}
			}
		}
		return $valueArray;
	}

	/** {@inheritdoc} */
	public function operatorE(): bool
	{
		return (bool) array_intersect($this->getValue(), explode($this->separator, $this->value));
	}

	/** {@inheritdoc} */
	public function operatorN(): bool
	{
		return (bool) !array_intersect($this->getValue(), explode($this->separator, $this->value));
	}

	/** {@inheritdoc} */
	public function operatorC(): bool
	{
		return $this->operatorE();
	}

	/** {@inheritdoc} */
	public function operatorK(): bool
	{
		return $this->operatorN();
	}
}
