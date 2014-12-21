<?php
/**
 * Base component property class. version, attach, rrule are all examples
 * of component properties.
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * 
 * RFC 2445 Definition
 * 
 * A property is the definition of an individual attribute describing a
 * calendar or a calendar component. A property takes the form defined
 * by the "contentline" notation defined in section 4.1.1.
 * 
 * The following is an example of a property:
 * 
 *  DTSTART:19960415T133000Z
 * 
 * This memo imposes no ordering of properties within an iCalendar
 * object.
 * 
 * Property names, parameter names and enumerated parameter values are
 * case insensitive. For example, the property name "DUE" is the same as
 * "due" and "Due", DTSTART;TZID=US-Eastern:19980714T120000 is the same
 * as DtStart;TzID=US-Eastern:19980714T120000.
 */
abstract class qCal_Property {

	/**
	 * Property name (dtstart, rrule, etc)
	 * This can be auto-generated from the class name
	 * @var string
	 */
	protected $name;
	/**
	 * Property value
	 * @var qCal_Value object
	 */
	protected $value;
	/**
	 * Default value - if set to false, there is no default value
	 * @var mixed
	 */
	protected $default = false;
	/**
	 * Property Data Type (this name gets converted to class name)
	 * @var string
	 */
	protected $type;
	/**
	 * Property parameters
	 * @var array
	 */
	protected $params = array();
	/**
	 * Contains a list of components this property is allowed to be specified
	 * for
	 * @var array
	 */
	protected $allowedComponents = array();
	/**
	 * Some properties can be specified multiple times in a component. This
	 * determines whether or not that is allowed for this property.
	 */
	protected $allowMultiple = false;
	/**
	 * Class constructor
	 * 
	 * @todo Cast $value to whatever data type this is ($this->type)
	 * @todo Determine if there can be multiple params of the same name 
	 */
	public function __construct($value = null, $params = array()) {
	
		if (is_null($this->name)) $this->name = $this->getPropertyNameFromClassName(get_class($this));
		foreach ($params as $pname => $pval) {
			$this->setParam($pname, $pval);
		}
		// this must be set after parameters because the VALUE parameter can affect it
		$this->setValue($value);
	
	}
	/**
	 * Generates a qCal_Property class based on property name, params, and value
	 * which can come directly from an icalendar file
	 * @todo I need a way to detect INVALID properties as they are being parsed. This
	 * way there can be an option to NOT stop on errors. To just log and then continue.
	 */
	static public function factory($name, $value, $params = array()) {
	
		$className = self::getClassNameFromPropertyName($name);
		$fileName = str_replace("_", DIRECTORY_SEPARATOR, $className) . ".php";
		try {
			qCal_Loader::loadFile($fileName);
			$class = new $className($value, $params);
		} catch (qCal_Exception_InvalidFile $e) {
			// if there is no class available for this property, check if it is non-standard
			$xname = strtoupper(substr($name, 0, 2));
			// non-standard property
			if ($xname == "X-") {
				$class = new qCal_Property_NonStandard($value, $params, $name);
			} else {
				// if it's not a non-standard property, rethrow
				throw $e;
			}
		}
		return $class;
	
	}
	/**
	 * Returns the property name (formatted and exactly to spec)
	 * @return string
	 */
	public function getName() {
	
		return $this->name;
	
	}
	/**
	 * Returns the property value (as a string)
	 * If you want the actual object, use getValueObject()
	 * I wish I could just pass the object back and have php do some overloading magicness, but
	 * it doesn't know how :(
	 * @return string
	 */
	public function getValue() {
	
		return $this->value->__toString();
	
	}
	/**
	 * Just returns getValue()
	 */
	public function __toString() {
	
		return $this->getValue();
	
	}
	/**
	 * Returns raw value object (or for multi-value, an array)
	 * @return string
	 */
	public function getValueObject() {
	
		return $this->value;
	
	}
	/**
	 * Sets the property value
	 * @param mixed
	 */
	public function setValue($value) {
	
		// if value sent is null and this property doesn't have a default value,
		// the property can't be created, so throw an invalidpropertyvalue exception
		if (is_null($value)) {
			if ($this->default === false) {
				// this is caught by factory and reported as a conformance error
				throw new qCal_Exception_InvalidPropertyValue($this->getName() . ' property must have a value');
			} else {
				$value = $this->default;
			}
		}
		$this->value = $this->convertValue($value);
		return $this;
	
	}
	/**
	 * Converts a value into whatever internal storage mechanism the property uses
	 */
	protected function convertValue($value) {
	
		return qCal_Value::factory($this->getType(), $value);
	
	}
	/**
	 * Returns the property type
	 * @return string
	 */
	public function getType() {
	
		return $this->type;
	
	}
	/**
	 * Check if this is a property of a certain component. Some properties
	 * can only be set on certain Components. This method looks inside this
	 * property's $allowedComponents and returns true if $component is allowed
	 *
	 * @return boolean True if this is a property of $component, false otherwise
	 * @param qCal_Component The component we're evaluating
	 **/
	public function of(qCal_Component $component) {
	
		return in_array($component->getName(), $this->allowedComponents);
	
	}
	/**
	 * Retreive the value of a parameter
	 *
	 * @return mixed parameter value
	 */
	public function getParam($name) {
	
		if (isset($this->params[strtoupper($name)])) {
			return $this->params[strtoupper($name)];
		}
	
	}
	/**
	 * Returns an array of all params
	 */
	public function getParams() {
	
		return $this->params;
	
	}
	/**
	 * Set the value of a parameter
	 */
	public function setParam($name, $value) {
	
		$name = strtoupper($name);
		// if value param has been passed in, change the type of this property to its value
		if ($name == "VALUE") {
			$value = strtoupper($value);
			$this->type = $value;
		}
		$this->params[$name] = $value;
		return $this;
	
	}
	/**
	 * Determine's this property's name from the class name by adding a dash after 
	 * every capital letter and upper-casing
	 *
	 * @return string The RFC property name
	 * @todo This method is flawed. The class name XLvFoo gets converted to X-L-VFOO when
	 * it should be X-LV-FOO
	 **/
	protected function getPropertyNameFromClassName($classname) {
	
		// determine the property name by class name
		$parts = explode("_", $classname);
		end($parts);
		// find where capital letters are and insert dash
		$chars = str_split(current($parts));
		// make a copy @todo Why make a copy? 
		$newchars = $chars;
		foreach ($chars as $pos => $char) {
			// don't add a dash for the first letter
			if (!$pos) continue;
			$num = ord($char);
			// if character is a capital letter
			if ($num >= 65 && $num <= 90) {
				// insert dash
				array_splice($newchars, $pos, 0, '-');
			}
		}
		return strtoupper(implode("", $newchars));
	
	}
	/**
	 * Determine's this property's class name from the property name
	 *
	 * @return string The property class name
	 **/
	protected function getClassNameFromPropertyName($name) {
	
		// remove dashes, capitalize properly
		$parts = explode("-", $name);
		$property = "";
		foreach ($parts as $part) $property .= trim(ucfirst(strtolower($part)));
		// get the class, and instantiate
		$className = "qCal_Property_" . $property;
		return $className;
	
	}
	/**
	 * Is this property allowed to be specified multiple times in a component?
	 * @return boolean 
	 */
	public function allowMultiple() {
	
		return (boolean) $this->allowMultiple;
	
	}
	
}