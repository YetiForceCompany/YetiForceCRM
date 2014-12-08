<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

// $ANTLR 3.1 VTEventConditionParser.g 2009-01-23 20:13:11
      
function VTEventConditionParserLexer_DFA9_static(){
    $eotS =
        "\x5\xff\x1\x8\x1\xc\x4\xff\x1\xe\x3\xff";
    $eofS =
        "\xf\xff";
    $minS =
        "\x1\x9\x4\xff\x1\x4e\x1\x30\x4\xff\x1\x30\x3\xff";
    $maxS =
        "\x1\x7a\x4\xff\x1\x6e\x1\x78\x4\xff\x1\x7a\x3\xff";
    $acceptS =
        "\x1\xff\x1\x1\x1\x2\x1\x3\x1\x4\x2\xff\x1\x7\x1\x8\x1\x9\x1\xb\x1".
    "\xff\x1\x6\x1\xa\x1\x5";
    $specialS =
        "\xf\xff}>";
    $transitionS = array(
        "\x2\xa\x2\xff\x1\xa\x12\xff\x1\xa\x6\xff\x1\x7\x4\xff\x1\x3\x1\xff".
        "\x1\x9\x1\xff\xa\x6\x3\xff\x1\x1\x3\xff\x8\x8\x1\x5\x11\x8\x1\x2".
        "\x1\xff\x1\x4\x2\xff\x9\x8\x1\x5\x11\x8",
        "",
        "",
        "",
        "",
        "\x1\xb\x1f\xff\x1\xb",
        "\xa\x6\x3e\xff\x1\xd",
        "",
        "",
        "",
        "",
        "\xa\x8\x7\xff\x1a\x8\x4\xff\x1\x8\x1\xff\x1a\x8",
        "",
        "",
        ""
    );
    $arr = array();
    $arr['eot'] = DFA::unpackEncodedString($eotS);
    $arr['eof'] = DFA::unpackEncodedString($eofS);
    $arr['min'] = DFA::unpackEncodedString($minS);
    $arr['max'] = DFA::unpackEncodedString($maxS);
    $arr['accept'] = DFA::unpackEncodedString($acceptS);
    $arr['special'] = DFA::unpackEncodedString($specialS);


    $numStates = sizeof($transitionS);
    $arr['transition'] = array();
    for ($i=0; $i<$numStates; $i++) {
        $arr['transition'][$i] = DFA::unpackEncodedString($transitionS[$i]);
    }
    return $arr;
}
$VTEventConditionParserLexer_DFA9 = VTEventConditionParserLexer_DFA9_static();

class VTEventConditionParserLexer_DFA9 extends DFA {

    public function __construct($recognizer) {
        global $VTEventConditionParserLexer_DFA9;
        $DFA = $VTEventConditionParserLexer_DFA9;
        $this->recognizer = $recognizer;
        $this->decisionNumber = 9;
        $this->eot = $DFA['eot'];
        $this->eof = $DFA['eof'];
        $this->min = $DFA['min'];
        $this->max = $DFA['max'];
        $this->accept = $DFA['accept'];
        $this->special = $DFA['special'];
        $this->transition = $DFA['transition'];
    }
    public function getDescription() {
        return "1:1: Tokens : ( T__13 | T__14 | T__15 | T__16 | IN | INTEGER | STRING | SYMBOL | DOT | ELEMENT_ID | WHITESPACE );";
    }
}
 

class VTEventConditionParserLexer extends AntlrLexer {
    static $INTEGER=8;
    static $T__16=16;
    static $IN=5;
    static $T__15=15;
    static $SYMBOL=4;
    static $T__14=14;
    static $LETTER=9;
    static $T__13=13;
    static $WHITESPACE=12;
    static $DIGIT=7;
    static $DOT=10;
    static $EOF=-1;
    static $ELEMENT_ID=11;
    static $STRING=6;

        public function reportError($e) {
            print_r($e);
            throw new Exception("The condition you provided is invalid");
        }


    // delegates
    // delegators

    function __construct($input, $state=null){
        parent::__construct($input,$state);

        
            $this->dfa9 = new VTEventConditionParserLexer_DFA9($this);
    }
    function getGrammarFileName() { return "VTEventConditionParser.g"; }

    // $ANTLR start "T__13"
    function mT__13(){
        try {
            $_type = VTEventConditionParserLexer::$T__13;
            $_channel = VTEventConditionParserLexer::$DEFAULT_TOKEN_CHANNEL;
            {
            $this->matchString("=="); 


            }

            $this->state->type = $_type;
            $this->state->channel = $_channel;
        }
        catch(Exception $e){
            throw $e;
        }
    }
    // $ANTLR end "T__13"

    // $ANTLR start "T__14"
    function mT__14(){
        try {
            $_type = VTEventConditionParserLexer::$T__14;
            $_channel = VTEventConditionParserLexer::$DEFAULT_TOKEN_CHANNEL;
            {
            $this->matchChar(91); 

            }

            $this->state->type = $_type;
            $this->state->channel = $_channel;
        }
        catch(Exception $e){
            throw $e;
        }
    }
    // $ANTLR end "T__14"

    // $ANTLR start "T__15"
    function mT__15(){
        try {
            $_type = VTEventConditionParserLexer::$T__15;
            $_channel = VTEventConditionParserLexer::$DEFAULT_TOKEN_CHANNEL;
            {
            $this->matchChar(44); 

            }

            $this->state->type = $_type;
            $this->state->channel = $_channel;
        }
        catch(Exception $e){
            throw $e;
        }
    }
    // $ANTLR end "T__15"

    // $ANTLR start "T__16"
    function mT__16(){
        try {
            $_type = VTEventConditionParserLexer::$T__16;
            $_channel = VTEventConditionParserLexer::$DEFAULT_TOKEN_CHANNEL;
            {
            $this->matchChar(93); 

            }

            $this->state->type = $_type;
            $this->state->channel = $_channel;
        }
        catch(Exception $e){
            throw $e;
        }
    }
    // $ANTLR end "T__16"

    // $ANTLR start "IN"
    function mIN(){
        try {
            $_type = VTEventConditionParserLexer::$IN;
            $_channel = VTEventConditionParserLexer::$DEFAULT_TOKEN_CHANNEL;
            {
            if ( $this->input->LA(1)==73||$this->input->LA(1)==105 ) {
                $this->input->consume();

            }
            else {
                $mse = new MismatchedSetException(null,$this->input);
                $this->recover($mse);
                throw $mse;}

            if ( $this->input->LA(1)==78||$this->input->LA(1)==110 ) {
                $this->input->consume();

            }
            else {
                $mse = new MismatchedSetException(null,$this->input);
                $this->recover($mse);
                throw $mse;}


            }

            $this->state->type = $_type;
            $this->state->channel = $_channel;
        }
        catch(Exception $e){
            throw $e;
        }
    }
    // $ANTLR end "IN"

    // $ANTLR start "INTEGER"
    function mINTEGER(){
        try {
            $_type = VTEventConditionParserLexer::$INTEGER;
            $_channel = VTEventConditionParserLexer::$DEFAULT_TOKEN_CHANNEL;
            {
            $cnt1=0;
            //loop1:
            do {
                $alt1=2;
                $LA1_0 = $this->input->LA(1);

                if ( (($LA1_0>=$this->getToken('48') && $LA1_0<=$this->getToken('57'))) ) {
                    $alt1=1;
                }


                switch ($alt1) {
            	case 1 :
            	    {
            	    $this->mDIGIT(); 

            	    }
            	    break;

            	default :
            	    if ( $cnt1 >= 1 ) break 2;//loop1;
                        $eee =
                            new EarlyExitException(1, $this->input);
                        throw $eee;
                }
                $cnt1++;
            } while (true);


            }

            $this->state->type = $_type;
            $this->state->channel = $_channel;
        }
        catch(Exception $e){
            throw $e;
        }
    }
    // $ANTLR end "INTEGER"

    // $ANTLR start "STRING"
    function mSTRING(){
        try {
            $_type = VTEventConditionParserLexer::$STRING;
            $_channel = VTEventConditionParserLexer::$DEFAULT_TOKEN_CHANNEL;
            {
            $this->matchChar(39); 
            $cnt2=0;
            //loop2:
            do {
                $alt2=3;
                $LA2_0 = $this->input->LA(1);

                if ( ($LA2_0==$this->getToken('39')) ) {
                    $LA2_1 = $this->input->LA(2);

                    if ( ($LA2_1==$this->getToken('39')) ) {
                        $alt2=2;
                    }


                }
                else if ( (($LA2_0>=$this->getToken('0') && $LA2_0<=$this->getToken('38'))||($LA2_0>=$this->getToken('40') && $LA2_0<=$this->getToken('65534'))) ) {
                    $alt2=1;
                }


                switch ($alt2) {
            	case 1 :
            	    {
            	    if ( ($this->input->LA(1)>=$this->getToken('0') && $this->input->LA(1)<=$this->getToken('38'))||($this->input->LA(1)>=$this->getToken('40') && $this->input->LA(1)<=$this->getToken('65534')) ) {
            	        $this->input->consume();

            	    }
            	    else {
            	        $mse = new MismatchedSetException(null,$this->input);
            	        $this->recover($mse);
            	        throw $mse;}


            	    }
            	    break;
            	case 2 :
            	    {
            	    $this->matchString("\'\'"); 


            	    }
            	    break;

            	default :
            	    if ( $cnt2 >= 1 ) break 2;//loop2;
                        $eee =
                            new EarlyExitException(2, $this->input);
                        throw $eee;
                }
                $cnt2++;
            } while (true);

            $this->matchChar(39); 

            }

            $this->state->type = $_type;
            $this->state->channel = $_channel;
        }
        catch(Exception $e){
            throw $e;
        }
    }
    // $ANTLR end "STRING"

    // $ANTLR start "SYMBOL"
    function mSYMBOL(){
        try {
            $_type = VTEventConditionParserLexer::$SYMBOL;
            $_channel = VTEventConditionParserLexer::$DEFAULT_TOKEN_CHANNEL;
            $alt5=2;
            $LA5_0 = $this->input->LA(1);

            if ( (($LA5_0>=$this->getToken('65') && $LA5_0<=$this->getToken('90'))||($LA5_0>=$this->getToken('97') && $LA5_0<=$this->getToken('122'))) ) {
                $alt5=1;
            }
            else if ( ($LA5_0==$this->getToken('96')) ) {
                $alt5=2;
            }
            else {
                $nvae = new NoViableAltException("", 5, 0, $this->input);

                throw $nvae;
            }
            switch ($alt5) {
                case 1 :
                    {
                    $this->mLETTER(); 
                    {
                    //loop3:
                    do {
                        $alt3=2;
                        $LA3_0 = $this->input->LA(1);

                        if ( (($LA3_0>=$this->getToken('48') && $LA3_0<=$this->getToken('57'))||($LA3_0>=$this->getToken('65') && $LA3_0<=$this->getToken('90'))||$LA3_0==$this->getToken('95')||($LA3_0>=$this->getToken('97') && $LA3_0<=$this->getToken('122'))) ) {
                            $alt3=1;
                        }


                        switch ($alt3) {
                    	case 1 :
                    	    {
                    	    if ( ($this->input->LA(1)>=$this->getToken('48') && $this->input->LA(1)<=$this->getToken('57'))||($this->input->LA(1)>=$this->getToken('65') && $this->input->LA(1)<=$this->getToken('90'))||$this->input->LA(1)==95||($this->input->LA(1)>=$this->getToken('97') && $this->input->LA(1)<=$this->getToken('122')) ) {
                    	        $this->input->consume();

                    	    }
                    	    else {
                    	        $mse = new MismatchedSetException(null,$this->input);
                    	        $this->recover($mse);
                    	        throw $mse;}


                    	    }
                    	    break;

                    	default :
                    	    break 2;//loop3;
                        }
                    } while (true);


                    }


                    }
                    break;
                case 2 :
                    {
                    $this->matchChar(96); 
                    $cnt4=0;
                    //loop4:
                    do {
                        $alt4=2;
                        $LA4_0 = $this->input->LA(1);

                        if ( (($LA4_0>=$this->getToken('0') && $LA4_0<=$this->getToken('95'))||($LA4_0>=$this->getToken('97') && $LA4_0<=$this->getToken('65534'))) ) {
                            $alt4=1;
                        }


                        switch ($alt4) {
                    	case 1 :
                    	    {
                    	    if ( ($this->input->LA(1)>=$this->getToken('0') && $this->input->LA(1)<=$this->getToken('95'))||($this->input->LA(1)>=$this->getToken('97') && $this->input->LA(1)<=$this->getToken('65534')) ) {
                    	        $this->input->consume();

                    	    }
                    	    else {
                    	        $mse = new MismatchedSetException(null,$this->input);
                    	        $this->recover($mse);
                    	        throw $mse;}


                    	    }
                    	    break;

                    	default :
                    	    if ( $cnt4 >= 1 ) break 2;//loop4;
                                $eee =
                                    new EarlyExitException(4, $this->input);
                                throw $eee;
                        }
                        $cnt4++;
                    } while (true);

                    $this->matchChar(96); 

                    }
                    break;

            }
            $this->state->type = $_type;
            $this->state->channel = $_channel;
        }
        catch(Exception $e){
            throw $e;
        }
    }
    // $ANTLR end "SYMBOL"

    // $ANTLR start "DOT"
    function mDOT(){
        try {
            $_type = VTEventConditionParserLexer::$DOT;
            $_channel = VTEventConditionParserLexer::$DEFAULT_TOKEN_CHANNEL;
            {
            $this->matchChar(46); 

            }

            $this->state->type = $_type;
            $this->state->channel = $_channel;
        }
        catch(Exception $e){
            throw $e;
        }
    }
    // $ANTLR end "DOT"

    // $ANTLR start "ELEMENT_ID"
    function mELEMENT_ID(){
        try {
            $_type = VTEventConditionParserLexer::$ELEMENT_ID;
            $_channel = VTEventConditionParserLexer::$DEFAULT_TOKEN_CHANNEL;
            {
            $cnt6=0;
            //loop6:
            do {
                $alt6=2;
                $LA6_0 = $this->input->LA(1);

                if ( (($LA6_0>=$this->getToken('48') && $LA6_0<=$this->getToken('57'))) ) {
                    $alt6=1;
                }


                switch ($alt6) {
            	case 1 :
            	    {
            	    $this->mDIGIT(); 

            	    }
            	    break;

            	default :
            	    if ( $cnt6 >= 1 ) break 2;//loop6;
                        $eee =
                            new EarlyExitException(6, $this->input);
                        throw $eee;
                }
                $cnt6++;
            } while (true);

            $this->matchChar(120); 
            $cnt7=0;
            //loop7:
            do {
                $alt7=2;
                $LA7_0 = $this->input->LA(1);

                if ( (($LA7_0>=$this->getToken('48') && $LA7_0<=$this->getToken('57'))) ) {
                    $alt7=1;
                }


                switch ($alt7) {
            	case 1 :
            	    {
            	    $this->mDIGIT(); 

            	    }
            	    break;

            	default :
            	    if ( $cnt7 >= 1 ) break 2;//loop7;
                        $eee =
                            new EarlyExitException(7, $this->input);
                        throw $eee;
                }
                $cnt7++;
            } while (true);


            }

            $this->state->type = $_type;
            $this->state->channel = $_channel;
        }
        catch(Exception $e){
            throw $e;
        }
    }
    // $ANTLR end "ELEMENT_ID"

    // $ANTLR start "LETTER"
    function mLETTER(){
        try {
            {
            if ( ($this->input->LA(1)>=$this->getToken('65') && $this->input->LA(1)<=$this->getToken('90'))||($this->input->LA(1)>=$this->getToken('97') && $this->input->LA(1)<=$this->getToken('122')) ) {
                $this->input->consume();

            }
            else {
                $mse = new MismatchedSetException(null,$this->input);
                $this->recover($mse);
                throw $mse;}


            }

        }
        catch(Exception $e){
            throw $e;
        }
    }
    // $ANTLR end "LETTER"

    // $ANTLR start "DIGIT"
    function mDIGIT(){
        try {
            {
            $this->matchRange(48,57); 

            }

        }
        catch(Exception $e){
            throw $e;
        }
    }
    // $ANTLR end "DIGIT"

    // $ANTLR start "WHITESPACE"
    function mWHITESPACE(){
        try {
            $_type = VTEventConditionParserLexer::$WHITESPACE;
            $_channel = VTEventConditionParserLexer::$DEFAULT_TOKEN_CHANNEL;
            {
            $cnt8=0;
            //loop8:
            do {
                $alt8=2;
                $LA8_0 = $this->input->LA(1);

                if ( (($LA8_0>=$this->getToken('9') && $LA8_0<=$this->getToken('10'))||$LA8_0==$this->getToken('13')||$LA8_0==$this->getToken('32')) ) {
                    $alt8=1;
                }


                switch ($alt8) {
            	case 1 :
            	    {
            	    if ( ($this->input->LA(1)>=$this->getToken('9') && $this->input->LA(1)<=$this->getToken('10'))||$this->input->LA(1)==13||$this->input->LA(1)==32 ) {
            	        $this->input->consume();

            	    }
            	    else {
            	        $mse = new MismatchedSetException(null,$this->input);
            	        $this->recover($mse);
            	        throw $mse;}


            	    }
            	    break;

            	default :
            	    if ( $cnt8 >= 1 ) break 2;//loop8;
                        $eee =
                            new EarlyExitException(8, $this->input);
                        throw $eee;
                }
                $cnt8++;
            } while (true);

            $_channel=self::$HIDDEN;

            }

            $this->state->type = $_type;
            $this->state->channel = $_channel;
        }
        catch(Exception $e){
            throw $e;
        }
    }
    // $ANTLR end "WHITESPACE"

    function mTokens(){
        $alt9=11;
        $alt9 = $this->dfa9->predict($this->input);
        switch ($alt9) {
            case 1 :
                {
                $this->mT__13(); 

                }
                break;
            case 2 :
                {
                $this->mT__14(); 

                }
                break;
            case 3 :
                {
                $this->mT__15(); 

                }
                break;
            case 4 :
                {
                $this->mT__16(); 

                }
                break;
            case 5 :
                {
                $this->mIN(); 

                }
                break;
            case 6 :
                {
                $this->mINTEGER(); 

                }
                break;
            case 7 :
                {
                $this->mSTRING(); 

                }
                break;
            case 8 :
                {
                $this->mSYMBOL(); 

                }
                break;
            case 9 :
                {
                $this->mDOT(); 

                }
                break;
            case 10 :
                {
                $this->mELEMENT_ID(); 

                }
                break;
            case 11 :
                {
                $this->mWHITESPACE(); 

                }
                break;

        }

    }



}
?>