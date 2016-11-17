<?php
namespace App\QueryField;

/**
 * Taxes Query Field Class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class TaxesField extends BaseField
{

	/**
	 * Separator
	 * @var string
	 */
	const SEPARATOR = ',';

	/**
	 * Get value
	 * @return mixed
	 */
	public function getValue()
	{
		$value = $this->value;
		if ($value) {
			return explode(',', $value);
		}
		return [];
	}

	/**
	 * Not equal operator
	 * @return array
	 */
	public function operatorN()
	{
		return ['not', [$this->getColumnName() => $this->getValue()]];
	}

	/**
	 * Contains operator
	 * @return array
	 */
	public function operatorC()
	{
		$condition = ['or'];
		foreach ($this->getValue() as $value) {
			array_push($condition, [$this->getColumnName() => $value], ['or like', $this->getColumnName(),
					[
					'%' . self::SEPARATOR . $value . self::SEPARATOR . '%',
					'%' . $value . self::SEPARATOR,
					$value . self::SEPARATOR . '%'
				], false
			]);
		}
		return $condition;
	}

	/**
	 * Does not contain operator
	 * @return array
	 */
	public function operatorK()
	{
		$condition = ['and'];
		foreach ($this->getValue() as $value) {
			array_push($condition, ['<>', $this->getColumnName(), $value], ['not', ['or like', $this->getColumnName(),
						[
						'%' . self::SEPARATOR . $value . self::SEPARATOR . '%',
						'%' . $value . self::SEPARATOR,
						$value . self::SEPARATOR . '%'
					], false
			]]);
		}
		return $condition;
	}
}
