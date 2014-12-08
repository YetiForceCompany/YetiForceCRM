<?php

/*
 [The "BSD licence"]
 Copyright (c) 2005-2008 Terence Parr
 All rights reserved.

 Redistribution and use in source and binary forms, with or without
 modification, are permitted provided that the following conditions
 are met:
 1. Redistributions of source code must retain the above copyright
    notice, this list of conditions and the following disclaimer.
 2. Redistributions in binary form must reproduce the above copyright
    notice, this list of conditions and the following disclaimer in the
    documentation and/or other materials provided with the distribution.
 3. The name of the author may not be used to endorse or promote products
    derived from this software without specific prior written permission.

 THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS OR
 IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES
 OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT,
 INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
 NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF
 THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

/** A parser for TokenStreams.  "parser grammars" result in a subclass
 *  of this.
 */
class AntlrParser extends BaseRecognizer {
	public $input;


	public function __construct($input, $state = null) {
		parent::__construct($state); // share the state object with another parser
		$this->setTokenStream($input);
    }

	public function reset() {
		parent::reset(); // reset all recognizer state variables
		if ( $this->input!=null ) {
			$this->input->seek(0); // rewind the input
		}
	}

	protected function getCurrentInputSymbol($input) {
		return $this->input->LT(1);
	}

	protected function getMissingSymbol($input, $e, $expectedTokenType, $follow)
	{
		$tokenText = null;
		if ( $expectedTokenType==TokenConst::$EOF ){ 
			$tokenText = "<missing EOF>";
		} else {
			$tokenNames = $this->getTokenNames();
			$tokenText = "<missing ".$tokenNames[$expectedTokenType].">";
		}
		$t = CommonToken::forTypeAndText($expectedTokenType, $tokenText);
		$current = $input->LT(1);
		if ( $current->getType() == TokenConst::$EOF ) {
			$current = $this->input->LT(-1);
		}
		$t->line = $current->getLine();
		$t->charPositionInLine = $current->getCharPositionInLine();
		$t->channel = $DEFAULT_TOKEN_CHANNEL;
		return $t;
	}

	/** Set the token stream and reset the parser */
	public function setTokenStream($input) {
		$this->input = null;
		$this->reset();
		$this->input = $input;
	}

    public function getTokenStream() {
		return $this->input;
	}

	public function getSourceName() {
		return $this->input->getSourceName();
	}

	public function traceIn($ruleName, $ruleIndex)  {
		parent::traceIn($ruleName, $ruleIndex, $this->input->LT(1));
	}

	public function traceOut($ruleName, $ruleIndex)  {
		parent::traceOut($ruleName, $ruleIndex, $this->input->LT(1));
	}
	

}

?>