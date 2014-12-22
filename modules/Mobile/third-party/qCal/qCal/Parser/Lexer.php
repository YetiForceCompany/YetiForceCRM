<?php
/**
 * qCal_Parser_Lexer
 * Not sure if I like the name of this class, but what can you do?
 * Anyway, this class converts a string into "tokens" which are then
 * fed to the parser
 * 
 * @package qCal
 * @subpackage qCal_Parser
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 */ 
abstract class qCal_Parser_Lexer {

    /**
     * @var string input text
     */
    protected $content;
    /**
     * Constructor
     * @param string containing the text to be tokenized
     */
    public function __construct($content) {
    
        $this->content = $content;
    
    }
	/**
	 * Tokenize content into tokens that can be used to build iCalendar objects
	 */
	abstract public function tokenize();

}