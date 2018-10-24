<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

/**
 * Template class will enable you to replace a merge fields defined in the String
 * with values set dynamically.
 *
 * @author Prasad
 */
class Vtiger_StringTemplate
{
	// Template variables set dynamically
	public $tplvars = [];

	/**
	 * Identify variable with the following pattern
	 * $VARIABLE_KEY$.
	 */
	public $_lookfor = '/\$([^\$]+)\$/';

	/**
	 * Constructor.
	 */
	public function __construct()
	{
	}

	/**
	 * Assign replacement value for the variable.
	 */
	public function assign($key, $value)
	{
		$this->tplvars[$key] = $value;
	}

	/**
	 * Get replacement value for the variable.
	 */
	public function get($key)
	{
		$value = false;
		if (isset($this->tplvars[$key])) {
			$value = $this->tplvars[$key];
		}
		return $value;
	}

	/**
	 * Clear all the assigned variable values.
	 * (except the once in the given list).
	 */
	public function clear($exceptvars = false)
	{
		$restorevars = [];
		if ($exceptvars) {
			foreach ($exceptvars as $varkey) {
				$restorevars[$varkey] = $this->get($varkey);
			}
		}
		unset($this->tplvars);

		$this->tplvars = [];
		foreach ($restorevars as $key => $val) {
			$this->assign($key, $val);
		}
	}

	/**
	 * Merge the given file with variable values assigned.
	 *
	 * @param $instring    input string template
	 * @param $avoidLookup should be true if only verbatim file copy needs to be done
	 * @returns merged contents
	 */
	public function merge($instring, $avoidLookup = false)
	{
		if (empty($instring)) {
			return $instring;
		}

		if (!$avoidLookup) {
			/** Look for variables */
			$matches = [];
			preg_match_all($this->_lookfor, $instring, $matches);

			/** Replace variables found with value assigned. */
			$matchcount = count($matches[1]);
			for ($index = 0; $index < $matchcount; ++$index) {
				$matchstr = $matches[0][$index];
				$matchkey = $matches[1][$index];

				$matchstr_regex = $this->__formatAsRegex($matchstr);

				$replacewith = $this->get($matchkey);
				if ($replacewith && !is_array($replacewith)) {
					$instring = preg_replace(
						"/$matchstr_regex/", $replacewith, $instring);
				}
			}
		}
		return $instring;
	}

	/**
	 * Clean up the input to be used as a regex.
	 */
	public function __formatAsRegex($value)
	{
		// If / is not already escaped as \/ do it now
		$value = preg_replace('/\//', '\\/', $value);
		// If $ is not already escaped as \$ do it now
		return preg_replace('/(?<!\\\)\$/', '\\\\$', $value);
	}
}
