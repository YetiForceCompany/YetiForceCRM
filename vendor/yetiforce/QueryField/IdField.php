<?php
namespace App\QueryField;

/**
 * Id Query Field Class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class IdField extends StringField
{

	/**
	 * Get column name
	 * @return string
	 */
	public function getColumnName()
	{
		if ($this->fullColumnName) {
			return $this->fullColumnName;
		}
		return $this->fullColumnName = $this->queryGenerator->getColumnName('id');
	}

	/**
	 * Starts with operator
	 * @return array
	 */
	public function operatorS()
	{
		return ['like', $this->getColumnName(), $this->getValue() . '%', false];
	}

	/**
	 * Ends with operator
	 * @return array
	 */
	public function operatorEw()
	{
		return ['like', $this->getColumnName(), '%' . $this->getValue(), false];
	}

	/**
	 * Greater operator
	 * @return array
	 */
	public function operatorA()
	{
		return ['>', $this->getColumnName(), $this->getValue()];
	}
}
