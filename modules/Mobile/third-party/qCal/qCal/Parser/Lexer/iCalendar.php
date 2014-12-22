<?php
/**
 * qCal_Parser_Lexer_iCalendar
 * The lexer for iCalendar RFC 2445 format. Other formats will need their
 * own lexer. The lexer converts text to an array of "tokens", which, at least
 * for now, are just arrays.
 * 
 * @package qCal
 * @subpackage qCal_Parser
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * @todo Make sure that multi-value properties are taken care of properly
 */
class qCal_Parser_Lexer_iCalendar extends qCal_Parser_Lexer {
	
    /**
     * @var string character(s) used to terminate lines
     */
    protected $line_terminator;
	/**
	 * Constructor 
	 */
	public function __construct($content) {
	
		parent::__construct($content);
		$this->line_terminator = chr(13) . chr(10);
	
	}
    /**
     * Return a list of tokens (to be fed to the parser)
     * @returns array tokens
     */
    public function tokenize() {
    
		$lines = $this->unfold($this->content);
        // loop through chunks of input text by separating by properties and components
        // and create tokens for each one, creating a multi-dimensional array of tokens to return
        $stack = array();
        foreach ($lines as $line) {
        	// begin a component
        	if (preg_match('#^BEGIN:([a-z]+)$#i', $line, $matches)) {
        		// create new array representing the new component
        		$array = array(
        			'component' => $matches[1],
        			'properties' => array(),
        			'children' => array(),
        		);
        		$stack[] = $array;
        	} elseif (strpos($line, "END:") === 0) {
        		// end component, pop the stack
        		$child = array_pop($stack);
				if (empty($stack)) {
					$tokens = $child;
				} else {
					$parent =& $stack[count($stack)-1];
					array_push($parent['children'], $child);
				}
        	} else {
        		// continue component
        		if (preg_match('#^([^:]+):"?([^\n]+)?"?$#i', $line, $matches)) {
					// @todo What do I do with empty values?
					$value = isset($matches[2]) ? $matches[2] : "";
					$component =& $stack[count($stack)-1];
        			// if line is a property line, start a new property, but first determine if there are any params
					$property = $matches[1];
					$params = array();
					$propparts = explode(";", $matches[1]);
					if (count($propparts) > 1) {
						foreach ($propparts as $key => $part) {
							// the first one is the property name
							if ($key == 0) {
								$property = $part;
							} else {
								// the rest are params
								// @todo Quoted param values need to be taken care of...
								list($paramname, $paramvalue) = explode("=", $part, 2);
								$params[] = array(
									'param' => $paramname,
									'value' => $paramvalue,
								);
							}
						}
					}
					$proparray = array(
						'property' => $property,
						'value' => $value,
						'params' => $params,
					);
        			$component['properties'][] = $proparray;
        		}
        	}
        }
        return $tokens;
    
    }
	/**
	 * Unfold the file before trying to parse it
	 */
	protected function unfold($content) {
	
		$return = array();
		$lines = explode($this->line_terminator, $content);
		foreach ($lines as $line) {
			$checkempty = trim($line);
			if (empty($checkempty)) continue;
			$chr1 = substr($line, 0, 1);
			$therest = substr($line, 1);
			// if character 1 is a whitespace character... (tab or space)
			if ($chr1 == chr(9) || $chr1 == chr(32)) {
				$return[count($return)-1] .= $therest;
			} else {
				$return[] = $line;
			}
		}
		return $return;
	
	}

}