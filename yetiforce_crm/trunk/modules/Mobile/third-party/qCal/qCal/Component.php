<?php
/**
 * Base calendar component class. Events, Todos, and Calendars are
 * examples of components in qCal
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * 
 * RFC 2445 Definition
 * 
 * The body of the iCalendar object consists of a sequence of calendar
 * properties and one or more calendar components. The calendar
 * properties are attributes that apply to the calendar as a whole. The
 * calendar components are collections of properties that express a
 * particular calendar semantic. For example, the calendar component can
 * specify an event, a to-do, a journal entry, time zone information, or
 * free/busy time information, or an alarm.
 * 
 * The body of the iCalendar object is defined by the following
 * notation:
 * 
 *  icalbody   = calprops component
 * 
 *  calprops   = 2*(
 * 
 *             ; 'prodid' and 'version' are both REQUIRED,
 *             ; but MUST NOT occur more than once
 * 
 *             prodid /version /
 * 
 *             ; 'calscale' and 'method' are optional,
 *             ; but MUST NOT occur more than once
 * 
 *             calscale        /
 *             method          /
 * 
 *             x-prop
 * 
 *             )
 * 
 *  component  = 1*(eventc / todoc / journalc / freebusyc /
 *             / timezonec / iana-comp / x-comp)
 * 
 *  iana-comp  = "BEGIN" ":" iana-token CRLF
 * 
 *               1*contentline
 * 
 *               "END" ":" iana-token CRLF
 * 
 *  x-comp     = "BEGIN" ":" x-name CRLF
 * 
 *               1*contentline
 * 
 *               "END" ":" x-name CRLF
 * 
 * An iCalendar object MUST include the "PRODID" and "VERSION" calendar
 * properties. In addition, it MUST include at least one calendar
 * component. Special forms of iCalendar objects are possible to publish
 * just busy time (i.e., only a "VFREEBUSY" calendar component) or time
 * zone (i.e., only a "VTIMEZONE" calendar component) information. In
 * addition, a complex iCalendar object is possible that is used to
 * capture a complete snapshot of the contents of a calendar (e.g.,
 * composite of many different calendar components). More commonly, an
 * iCalendar object will consist of just a single "VEVENT", "VTODO" or
 * "VJOURNAL" calendar component.
 */
abstract class qCal_Component {

	/**
	 * The name of this component
	 * @var string
	 */
	protected $name;
	/**
	 * Contains a list of allowed parent components.
	 * @var array
	 */
	protected $allowedComponents = array();
	/**
	 * Contains an array of this component's child components (if any). It uses
	 * @var array
	 */
	protected $children = array();
	/**
	 * Contains an array of this component's properties. Properties provide
	 * information about their respective components. This array is associative.
	 * It uses property name as key and property object as value (or array of them
	 * if said property can be set multiple times). This is so that I can quickly
	 * look up any certain property.
	 * @var array
	 */
	protected $properties = array();
	/**
	 * Contains an array of this component's required properties
	 */
	protected $requiredProperties = array();
	/**
	 * Parent component (all components but vcalendar should have one once attached)
	 */
	protected $parent;
	/**
	 * Class constructor
	 * Accepts an array of properties, which can be simple values or actual property objects
	 * Pass in a null value to use a property's default value (some dont have defaults, so beware)
	 * Example:
	 * $cal = new qCal_Component_Calendar(array(
     *     'prodid' => '-// Some Property Id//',
     *     'someotherproperty' => null,
     *     qCal_Property_Version(2.0),
	 * ), array(
	 * 	   qCal_Component_Daylight(),
	 * ));
	 */
	public  function __construct($properties = array(), $components = array()) {
	
		foreach ($components as $component) {
			// if value is an array, then each value inside of it will be a component
			if ($component instanceof qCal_Component) {
				$this->attach($component);
			}
			else throw new qCal_Exception_InvalidComponent('The second argument is optional, but if provided, must be an array of components');
		} 
		foreach ($properties as $name => $value) {
			// if value is an array, then each value inside of it will be a property
			if (is_array($value)) {
				foreach ($value as $val) {
					if ($val instanceof qCal_Property) {
						$this->addProperty($val);
					} else {
						$this->addProperty($name, $val);
					}
				}
			} else {
				if ($value instanceof qCal_Property) {
					$this->addProperty($value);
				} else {
					$this->addProperty($name, $value);
				}
			}
		}
		// I think it would make more sense to do validation at render time. That way you don't have
		// to have all of the required components and properties when you instantiate. Also, that way
		// components don't need to be aware of eachother until render time (or until validate() is called
		// explicitly). @todo
		// $this->validate();
	
	}
	/**
	 * @todo (lazy load functionality) Check that this is a valid component. This method is sort of lazy-loaded. It only gets called
	 * if the user has requested data that requires validation and the component has not been validated already.
	 * @todo Shouldn't this loop over children and validate them too? Maybe optionally?
	 */
	public function validate() {
	
		// if we're missing any required properties and they have no default, throw an exception
		$properties = array();
		foreach ($this->getProperties() as $property) {
			if (is_array($property)) {
				foreach ($property as $prop) {
					$properties[] = $prop->getName();
				}
			} else {
				$properties[] = $property->getName();
			}
		}
		$missing = array_diff($this->requiredProperties, array_unique($properties));
		foreach ($missing as $propertyname) {
			// the property factory will throw an exception if it's passed a null value for a property with no default
			try {
				$property = qCal_Property::factory($propertyname, null);
				$this->addProperty($property);
			} catch (qCal_Exception_InvalidPropertyValue $e) {
				// if that's the case, catch the exception and throw a missing property exception
				throw new qCal_Exception_MissingProperty($this->getName() . " component requires " . $propertyname . " property");
			}
		}
		// this allows per-component validation :)
		$this->doValidation();
	
	}
	/**
	 * Returns the component name
	 * @return string
	 */
	public function getName() {
	
		return $this->name;
	
	}
	/**
	 * Returns true if this component can be attached to $component
	 * I'm sure there's a better way to do this, but this works for now
	 */
	public function canAttachTo(qCal_Component $component) {
	
		if (in_array($component->getName(), $this->allowedComponents)) return true;
	
	}
	/**
	 * Attach a component to this component (alarm inside event for example)
	 * @todo There may be an issue with the way this is done. When parsing a file, if a component
	 * or property with a tzid comes before its corresponding vtimezone component, an exception
	 * will be thrown. I'm don't think the RFC specifies that requirement (that timezone components
	 * must come before their corresponding tzids)
	 * @todo Sub-components such as Vevent need to be able to access the main vcalendar object
	 * for several reasons. 
	 * 		 - If a vtodo has a tzid, it needs to be able to determine that the corresponding 
	 * 		   vtimezone component is available.
	 * 		 - If components need to relate to eachother, they can only find eachother through
	 * 		   the main vcalendar object.
	 * 		 - Freebusy time can only be determined by polling all components in the main vcalendar
	 * 		   object.
	 * 		 - More to come probably
	 */
	public function attach(qCal_Component $component) {
	
		if (!$component->canAttachTo($this)) {
			throw new qCal_Exception_InvalidComponent($component->getName() . ' cannot be attached to ' . $this->getName());
		}
		$component->setParent($this);
		// make sure if a timezone is requested that it is available...
		$timezones = $this->getTimezones(); 
		$tzids = array_keys($timezones);
		// we only need to check if tzid exists if we are attaching something other than a timezone...
		if (!($component instanceof qCal_Component_Vtimezone)) {
			foreach ($component->getProperties() as $pname => $properties) {
				$pname = strtoupper($pname); // probably redundant...
				foreach ($properties as $property) {
					switch ($pname) {
						case "TZID":
							$tzid = $property->getValue();
							if (!array_key_exists($tzid, $tzids)) {
								throw new qCal_Exception_MissingComponent('TZID "' . $tzid . '" not defined');
							}
							break;
					}
					$params = $property->getParams();
					foreach ($params as $param => $val) {
						$param = strtoupper($param); // probably redundant...
						switch ($param) {
							case "TZID":
								$tzid = $val;
								if (!array_key_exists($tzid, $tzids)) {
									throw new qCal_Exception_MissingComponent('TZID "' . $tzid . '" not defined');
								}
								break;
						}
					}
				}
			}
		}
		$this->children[$component->getName()][] = $component;
	
	}
	/**
	 * Set the parent of this component
	 * @todo I'm not sure this will suffice. See the attach method for reasoning behind this.
	 */
	public function setParent(qCal_Component $component) {
	
		$this->parent = $component;
	
	}
	/**
	 * Get the parent of this component (if there is one)
	 */
	public function getParent() {
	
		return $this->parent;
	
	}
	/**
	 * The only thing I need this for so far is the parser, but it may come in handy for the facade as well
	 */
	static public function factory($name, $properties = array()) {
	
		if (empty($name)) return false;
		// capitalize
		$component = ucfirst(strtolower($name));
		$className = "qCal_Component_" . $component;
		$fileName = str_replace("_", DIRECTORY_SEPARATOR, $className) . ".php";
		qCal_Loader::loadFile($fileName);
		$class = new $className($properties);
		return $class;
	
	}

	/**
	 * I'm not sure how this should work. Not sure if it should be setProperty,
	 * addProperty, both? Because properties on some components can be set multiple
	 * times, while some properties have multiple values. :( I am trying to consider
	 * a case where somebody needs to open a calendar, change a few properties on a
	 * component (change event time for instance). I think the way I'll handle properties
	 * that can be set multiple times is I'll create a method do delete properties based
	 * on values, parameters, etc. since they don't really have IDs. So I tihnk I'll go
	 * with addProperty :) 
	 */
	public function addProperty($property, $value = null, $params = array()) {
	
		if (!($property instanceof qCal_Property)) {
			$property = qCal_Property::factory($property, $value, $params);
		}
		if (!$property->of($this)) {
			throw new qCal_Exception_InvalidProperty($this->getName() . " component does not allow " . $property->getName() . " property");
		}
		if (!$property->allowMultiple()) {
			unset($this->properties[$property->getName()]);
		}
		$this->properties[$property->getName()][] = $property;
	
	}
	/**
	 * Returns property of this component by name
	 *
	 * @todo Since the same property can appear in a component more than once, this method
	 * doesn't make that much sense unless it returns all of the instances of the property
	 * @return array of qCal_Property
	 */
	public function getProperty($name) {
	
		$name = strtoupper($name);
		if ($this->hasProperty($name)) {
			return $this->properties[$name];
		}
	
	}
	
	/**
	 * Returns true if this component contains a property of $name
	 *
	 * @return boolean
	 */
	public function hasProperty($name) {
	
		$name = strtoupper($name);
		return isset($this->properties[$name]);
	
	}
	
	/**
	 * Returns true if this component contains a property of $name
	 *
	 * @return boolean
	 */
	public function hasComponent($name) {
	
		$name = strtoupper($name);
		return isset($this->children[$name]);
	
	}
	/**
	 * Returns the child component requested
	 */
	public function getComponent($name) {
	
		$name = strtoupper($name);
		if ($this->hasComponent($name)) {
			return $this->children[$name];
		}
	
	}
	
	/*
	public function clearProperties() {
	
		$this->properties = array();
	
	}
	
	public function clearChildren() {
	
		$this->children = array();
	
	}
	*/
	
	public function getProperties() {
	
		return $this->properties;
	
	}
	
	public function getChildren() {
	
		return $this->children;
	
	}
	
	/**
	 * Gets the parent-most component in the tree. I would really like to come up
	 * with a cleaner way to access other components from within a component, but oh well.
	 */
	public function getRootComponent() {
	
		$parent = $this;
		while (!($parent instanceof qCal_Component_Vcalendar)) {
			if (!$parent->getParent()) break;
			$parent = $parent->getParent();
		}
		return $parent;
	
	}
	
	/**
	 * Renders the calendar, by default in icalendar format. If you pass
	 * in a renderer, it will use that instead
	 *
	 * @return mixed Depends on the renderer
	 * @todo Would it make more sense to pass the component to the renderer, or the renderer
	 * to the component? I'm not sure components should know about rendering.
	 */
	public function render(qCal_Renderer $renderer = null) {
	
		$this->validate();
		if (is_null($renderer)) $renderer = new qCal_Renderer_iCalendar();
		return $renderer->render($this);
	
	}
	
	/**
	 * Output the icalendar component as a string (render it)
	 */
	public function __toString() {
	
		return $this->render();
	
	}
	
	/**
	 * getFreeBusyTime
	 * Looks through all of the data in the calendar and returns a qCal_Component_Vfreebusy object
	 * with free/busy time from $startdate to $enddate. The component will contain all components, but some
	 * may have their transparency set to "transparent".
	 * @todo This cannot be finished until recurring events are finished, since free/busy does not allow
	 * recurrence rules, each instance of a recurrence would need to be calculated out and passed into the free/busy
	 * component, so that the component would contain concrete instances of each event recurrence.
	 */
	public function getFreeBusyTime() {
	
		$root = $this->getRootComponent();
		foreach ($root->getChildren() as $children) {
			foreach ($children as $child) {
				// now get the object's free/busy time
			}
		}
	
	}
	/**
	 * getTimeZones
	 */
	public function getTimezones() {
	
		$tzs = array();
		$root = $this->getRootComponent();
		foreach ($root->getChildren() as $children) {
			foreach ($children as $child) {
				// if the child is a vtimezone, add it to the results
				// @todo make sure that tzid is available, throw exception otherwise
				if ($child instanceof qCal_Component_Vtimezone) {
					$tzid = $child->getTzid();
					$tzid = strtoupper($tzid);
					$tzs[$tzid] = $child;
				}
			}
		}
		return $tzs;
	
	}
	/**
	 * Get a specific timezone by tzid
	 * @param string The timezone identifier
	 */
	public function getTimezone($tzid) {
	
		$tzid = strtoupper($tzid);
		$root = $this->getRootComponent();
		$timezones = $root->getTimezones();
		if (array_key_exists($tzid, $timezones)) {
			return $timezones[$tzid];
		}
		return false;
	
	}
	/**
	 * Allows for components to get and set property values by calling
	 * qCal_Component::getPropertyName() and qCal_Component::setPropertyName('2.0') where propertyName is the property name
	 * to be set and $val is the property value.
	 * This is just a convenience facade, it isn't going to be used within the library as much as by end-users
	 * @todo I can't decided whether to maybe get rid of the facade methods at least for now since some properties
	 * can potentially return multiple values and that makes the interface inconsistent
	 */
	public function __call($method, $params) {
	
		$firstthree = substr($method, 0, 3);
		$name = substr($method, 3);
		if ($firstthree == "get") {
			// if property is allowed multiple times, an array is returned, otherwise just the one component
			if ($this->hasProperty($name)) {
				$property = $this->getProperty($name);
				if (!$property[0]->allowMultiple()) {
					return $property[0];
				} else {
					return $property;
				}
			}
		} elseif ($firstthree == "set") {
			$value = isset($params[0]) ? $params[0] : null;
			$params = isset($params[1]) ? $params[1] : array();
			$property = qCal_Property::factory($name, $value, $params);
			$this->addProperty($property);
		} elseif ($firstthree == "add") {
			// add property type
			$property = qCal_Property::factory($name, $params);
			$this->addProperty($property);
			return $this;
		}
		// throw exception here?
		// throw new qCal_Exception();
	
	}

}