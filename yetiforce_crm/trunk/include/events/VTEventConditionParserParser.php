<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

// $ANTLR 3.1 VTEventConditionParser.g 2009-01-23 20:13:10              

class VTEventConditionParserParser extends AntlrParser {
    public static $tokenNames = array(
        "<invalid>", "<EOR>", "<DOWN>", "<UP>", "SYMBOL", "IN", "STRING", "DIGIT", "INTEGER", "LETTER", "DOT", "ELEMENT_ID", "WHITESPACE", "'=='", "'['", "','", "']'"
    );
    public $INTEGER=8;
    public $T__16=16;
    public $IN=5;
    public $T__15=15;
    public $SYMBOL=4;
    public $LETTER=9;
    public $T__14=14;
    public $T__13=13;
    public $WHITESPACE=12;
    public $DIGIT=7;
    public $DOT=10;
    public $EOF=-1;
    public $ELEMENT_ID=11;
    public $STRING=6;

    // delegates
    // delegators
    
    static $FOLLOW_comparision_in_statement57;
    static $FOLLOW_inclause_in_statement63;
    static $FOLLOW_SYMBOL_in_comparision78;
    static $FOLLOW_13_in_comparision80;
    static $FOLLOW_value_in_comparision84;
    static $FOLLOW_SYMBOL_in_inclause101;
    static $FOLLOW_IN_in_inclause103;
    static $FOLLOW_listelement_in_inclause107;
    static $FOLLOW_14_in_listelement122;
    static $FOLLOW_value_in_listelement126;
    static $FOLLOW_15_in_listelement137;
    static $FOLLOW_value_in_listelement141;
    static $FOLLOW_16_in_listelement147;
    static $FOLLOW_STRING_in_value162;

    
    

        public function __construct($input, $state = null) {
            if($state==null){
                $state = new RecognizerSharedState();
            }
            parent::__construct($input, $state);
             
            
            
        }
        

    public function getTokenNames() { return VTEventConditionParserParser::$tokenNames; }
    public function getGrammarFileName() { return "VTEventConditionParser.g"; }

     
    	protected function mismatch($input, $ttype, $follow){ 
    		throw new MismatchedTokenException($ttype, $input); 
    	}
    	public function recoverFromMismatchedSet($input, $e, $follow){ 
    		throw $e;
    	} 



    // $ANTLR start "statement"
    ///* VTEventConditionParser.g:33:1: statement returns [result] : (exp= comparision | exp= inclause ) ; */
    public function statement(){
        $result = null;

        $exp = null;


        try {
            {
            $alt1=2;
            $LA1_0 = $this->input->LA(1);

            if ( ($LA1_0==$this->getToken('SYMBOL')) ) {
                $LA1_1 = $this->input->LA(2);

                if ( ($LA1_1==$this->getToken('13')) ) {
                    $alt1=1;
                }
                else if ( ($LA1_1==$this->getToken('IN')) ) {
                    $alt1=2;
                }
                else {
                    if ($this->state->backtracking>0) {$this->state->failed=true; return $result;}
                    $nvae = new NoViableAltException("", 1, 1, $this->input);

                    throw $nvae;
                }
            }
            else {
                if ($this->state->backtracking>0) {$this->state->failed=true; return $result;}
                $nvae = new NoViableAltException("", 1, 0, $this->input);

                throw $nvae;
            }
            switch ($alt1) {
                case 1 :
                    {
                    $this->pushFollow(self::$FOLLOW_comparision_in_statement57);
                    $exp=$this->comparision();

                    $this->state->_fsp--;
                    if ($this->state->failed) return $result;

                    }
                    break;
                case 2 :
                    {
                    $this->pushFollow(self::$FOLLOW_inclause_in_statement63);
                    $exp=$this->inclause();

                    $this->state->_fsp--;
                    if ($this->state->failed) return $result;

                    }
                    break;

            }

            if ( $this->state->backtracking==0 ) {
              $result=$exp;
            }

            }

        }
         
        	catch (RecognitionException $e) { 
        		throw $e; 
        	} 
        catch(Exception $e) {
            throw $e;
        }
        
        return $result;
    }
    // $ANTLR end "statement"


    // $ANTLR start "comparision"
    ///* VTEventConditionParser.g:35:1: comparision returns [result] : lhs= SYMBOL '==' rhs= value ; */
    public function comparision(){
        $result = null;

        $lhs=null;
        $rhs = null;


        try {
            {
            $lhs=$this->match($this->input,$this->getToken('SYMBOL'),self::$FOLLOW_SYMBOL_in_comparision78); if ($this->state->failed) return $result;
            $this->match($this->input,$this->getToken('13'),self::$FOLLOW_13_in_comparision80); if ($this->state->failed) return $result;
            $this->pushFollow(self::$FOLLOW_value_in_comparision84);
            $rhs=$this->value();

            $this->state->_fsp--;
            if ($this->state->failed) return $result;
            if ( $this->state->backtracking==0 ) {
              $result=array('==', new VTEventConditionSymbol(($lhs!=null?$lhs->getText():null)), $rhs); echo $value;
            }

            }

        }
         
        	catch (RecognitionException $e) { 
        		throw $e; 
        	} 
        catch(Exception $e) {
            throw $e;
        }
        
        return $result;
    }
    // $ANTLR end "comparision"


    // $ANTLR start "inclause"
    ///* VTEventConditionParser.g:38:1: inclause returns [result] : lhs= SYMBOL IN rhs= listelement ; */
    public function inclause(){
        $result = null;

        $lhs=null;
        $rhs = null;


        try {
            {
            $lhs=$this->match($this->input,$this->getToken('SYMBOL'),self::$FOLLOW_SYMBOL_in_inclause101); if ($this->state->failed) return $result;
            $this->match($this->input,$this->getToken('IN'),self::$FOLLOW_IN_in_inclause103); if ($this->state->failed) return $result;
            $this->pushFollow(self::$FOLLOW_listelement_in_inclause107);
            $rhs=$this->listelement();

            $this->state->_fsp--;
            if ($this->state->failed) return $result;
            if ( $this->state->backtracking==0 ) {
              $result=array('in', new VTEventConditionSymbol(($lhs!=null?$lhs->getText():null)), $rhs);
            }

            }

        }
         
        	catch (RecognitionException $e) { 
        		throw $e; 
        	} 
        catch(Exception $e) {
            throw $e;
        }
        
        return $result;
    }
    // $ANTLR end "inclause"


    // $ANTLR start "listelement"
    ///* VTEventConditionParser.g:40:1: listelement returns [result] : '[' val= value ( ',' val= value )* ']' ; */
    public function listelement(){
        $result = null;

        $val = null;


        try {
            {
            $this->match($this->input,$this->getToken('14'),self::$FOLLOW_14_in_listelement122); if ($this->state->failed) return $result;
            $this->pushFollow(self::$FOLLOW_value_in_listelement126);
            $val=$this->value();

            $this->state->_fsp--;
            if ($this->state->failed) return $result;
            if ( $this->state->backtracking==0 ) {
              $result = array('list', $val);
            }
            //loop2:
            do {
                $alt2=2;
                $LA2_0 = $this->input->LA(1);

                if ( ($LA2_0==$this->getToken('15')) ) {
                    $alt2=1;
                }


                switch ($alt2) {
            	case 1 :
            	    {
            	    $this->match($this->input,$this->getToken('15'),self::$FOLLOW_15_in_listelement137); if ($this->state->failed) return $result;
            	    $this->pushFollow(self::$FOLLOW_value_in_listelement141);
            	    $val=$this->value();

            	    $this->state->_fsp--;
            	    if ($this->state->failed) return $result;
            	    if ( $this->state->backtracking==0 ) {
            	      $result[] = $val;
            	    }

            	    }
            	    break;

            	default :
            	    break 2;//loop2;
                }
            } while (true);

            $this->match($this->input,$this->getToken('16'),self::$FOLLOW_16_in_listelement147); if ($this->state->failed) return $result;

            }

        }
         
        	catch (RecognitionException $e) { 
        		throw $e; 
        	} 
        catch(Exception $e) {
            throw $e;
        }
        
        return $result;
    }
    // $ANTLR end "listelement"


    // $ANTLR start "value"
    ///* VTEventConditionParser.g:44:1: value returns [result] : val= STRING ; */
    public function value(){
        $result = null;

        $val=null;

        try {
            {
            $val=$this->match($this->input,$this->getToken('STRING'),self::$FOLLOW_STRING_in_value162); if ($this->state->failed) return $result;
            if ( $this->state->backtracking==0 ) {
              $result = stripcslashes(substr(($val!=null?$val->getText():null), 1, strlen(($val!=null?$val->getText():null))-2));
            }

            }

        }
         
        	catch (RecognitionException $e) { 
        		throw $e; 
        	} 
        catch(Exception $e) {
            throw $e;
        }
        
        return $result;
    }
    // $ANTLR end "value"

    // Delegated rules


    
}

 



VTEventConditionParserParser::$FOLLOW_comparision_in_statement57 = new Set(array(1));
VTEventConditionParserParser::$FOLLOW_inclause_in_statement63 = new Set(array(1));
VTEventConditionParserParser::$FOLLOW_SYMBOL_in_comparision78 = new Set(array(13));
VTEventConditionParserParser::$FOLLOW_13_in_comparision80 = new Set(array(6));
VTEventConditionParserParser::$FOLLOW_value_in_comparision84 = new Set(array(1));
VTEventConditionParserParser::$FOLLOW_SYMBOL_in_inclause101 = new Set(array(5));
VTEventConditionParserParser::$FOLLOW_IN_in_inclause103 = new Set(array(14));
VTEventConditionParserParser::$FOLLOW_listelement_in_inclause107 = new Set(array(1));
VTEventConditionParserParser::$FOLLOW_14_in_listelement122 = new Set(array(6));
VTEventConditionParserParser::$FOLLOW_value_in_listelement126 = new Set(array(15, 16));
VTEventConditionParserParser::$FOLLOW_15_in_listelement137 = new Set(array(6));
VTEventConditionParserParser::$FOLLOW_value_in_listelement141 = new Set(array(15, 16));
VTEventConditionParserParser::$FOLLOW_16_in_listelement147 = new Set(array(1));
VTEventConditionParserParser::$FOLLOW_STRING_in_value162 = new Set(array(1));

?>