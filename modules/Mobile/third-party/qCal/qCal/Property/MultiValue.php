<?php
/**
 * Categories Property
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 */
class qCal_Property_MultiValue extends qCal_Property {

	/**
	 * Property value
	 * @var qCal_Value object
	 */
	protected $value = array();
	/**
	 * MultiValue properties contain an array of values rather than one, so we
	 * store them in an array and return them comma-separated.
	 */
	public function getValue() {
	
		$return = array();
		foreach ($this->value as $value) {
			$return[] = $value->__toString();
		}
		return implode(chr(44), $return);
	
	}
	/**
	 * Sets the value of this property. Overwrites any previous values. Use addValue to 
	 * add rather than overwrite.
	 * @todo I'm not sure I like how this is done. Eventually I will come back to it.
	 */
	public function setValue($value) {
	
		if (!is_array($value)) {
			$value = array($value);
		}
		// parent::setValue($value);
		$this->value = array();
		foreach ($value as $val) {
			$this->value[] = $this->convertValue($val);
		}
		return $this;
	
	}
	/**
	 * Add a value to the array of values (rather than overwrite)
	 */
	public function addValue($value) {
	
		$this->value[] = $this->convertValue($value);
		return $this;
	
	}

}