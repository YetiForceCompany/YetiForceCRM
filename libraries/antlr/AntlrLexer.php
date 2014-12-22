<?php
/**
 * Base class for lexers
 */
abstract class AntlrLexer extends BaseRecognizer{
	public static $DEFAULT_TOKEN_CHANNEL = 0;
	protected $input;
	
	public function __construct($input, $state=null) {
		if($state==null){
			$state = new RecognizerSharedState();
		}
		$this->state = $state;
		$this->input = $input;
	}
	
	public function reset() {
		parent::reset(); // reset all recognizer state variables
		// wack Lexer state variables
		if ( $this->input!=null ) {
			$this->input->seek(0); // rewind the input
		}
		if ( $this->state==null ) {
			return; // no shared state work to do
		}
		$this->state->token = null;
		$this->state->type = TokenConst::$INVALID_TOKEN_TYPE;
		$this->state->channel = TokenConst::$DEFAULT_CHANNEL;
		$this->state->tokenStartCharIndex = -1;
		$this->state->tokenStartCharPositionInLine = -1;
		$this->state->tokenStartLine = -1;
		$this->state->text = null;
	}
	
	
	/** Return a token from this source; i.e., match a token on the char
	 *  stream.
	 */
	public function nextToken() {
		while (true) {
			$this->state->token = null;
			$this->state->channel = 0;//Token::DEFAULT_CHANNEL;
			$this->state->tokenStartCharIndex = $this->input->index();
			$this->state->tokenStartCharPositionInLine = $this->input->getCharPositionInLine();
			$this->state->tokenStartLine = $this->input->getLine();
			$this->state->text = null;
			if ( $this->input->LA(1)==CharStreamConst::$EOF ) {
				return TokenConst::$EOF_TOKEN;
			}
			try {
				$this->mTokens();
				if ( $this->state->token==null ) {
					$this->emit();
				}
				else if ( $this->state->token==Token::$SKIP_TOKEN ) {
					continue;
				}
				return $this->state->token;
			}
			catch (NoViableAltException $nva) {
				$this->reportError($nva);
				$this->recover($nva); // throw out current char and try again
			}
			catch (RecognitionException $re) {
				$this->reportError($re);
				// match() routine has already called recover()
			}
		}
	}
	
	/** Instruct the lexer to skip creating a token for current lexer rule
	 *  and look for another token.  nextToken() knows to keep looking when
	 *  a lexer rule finishes with token set to SKIP_TOKEN.  Recall that
	 *  if token==null at end of any token rule, it creates one for you
	 *  and emits it.
	 */
	public function skip() {
		$this->state->token = TokenConst::$SKIP_TOKEN;
	}

	/** This is the lexer entry point that sets instance var 'token' */
	public abstract function mTokens();

	/** Set the char stream and reset the lexer */
	public function setCharStream($input) {
		$this->input = null;
		$this->reset();
		$this->input = $input;
	}

	public function getCharStream() {
		return $this->input;
	}
	
	public function getSourceName() {
		return $this->input->getSourceName();
	}
	
	/** Currently does not support multiple emits per nextToken invocation
	 *  for efficiency reasons.  Subclass and override this method and
	 *  nextToken (to push tokens into a list and pull from that list rather
	 *  than a single variable as this implementation does).
	 */
	/** The standard method called to automatically emit a token at the
	 *  outermost lexical rule.  The token object should point into the
	 *  char buffer start..stop.  If there is a text override in 'text',
	 *  use that to set the token's text.  Override this method to emit
	 *  custom Token objects.
	 *
	 *  If you are building trees, then you should also override
	 *  Parser or TreeParser.getMissingSymbol().
	 */
	public function emit($token=null) {
		if($token==null){
			$token = CommonToken::forInput($this->input, $this->state->type, $this->state->channel,
				$this->state->tokenStartCharIndex, $this->getCharIndex()-1);
			$token->setLine($this->state->tokenStartLine);
			$token->setText($this->state->text);
			$token->setCharPositionInLine($this->state->tokenStartCharPositionInLine);
		}
		$this->state->token = $token;
		return $token;
	}
	
	function matchString($s){
		$i = 0;
		while ( $i<strlen($s)) {
			if ( $this->input->LA(1)!=charAt($s, $i) ) {
				if ( $this->state->backtracking>0 ) {
					$this->state->failed = true;
					return;
				}
				$mte = new MismatchedTokenException(charAt($s, $i), $this->input);
				$this->recover($mte);
				throw $mte;
			}
			$i++;
			$this->input->consume();
			$state->failed = false;
		}
	}
	
	public function matchAny() {
		$this->input->consume();
	}
	
	public function matchChar($c) {
		if ($this->input->LA(1)!=$c ) {
			if ( $this->state->backtracking>0 ) {
				$this->state->failed = true;
				return;
			}
			$mte = new MismatchedTokenException($c, $this->input);
			$this->recover($mte);  // don't really recover; just consume in lexer
			throw $mte;
		}
		$this->input->consume();
		$this->state->failed = false;
	}
	
	public function matchRange($a, $b) {
		if ( $this->input->LA(1)<$a || $this->input->LA(1)>$b ) {
			if ( $this->state->backtracking>0 ) {
				$this->state->failed = true;
				return;
			}
			$mre = new MismatchedRangeException($a, $b, $this->input);
			$this->recover($mre);
			throw $mre;
		}
		$this->input->consume();
		$this->state->failed = false;
	}
	
	public function getLine() {
		return $this->input->getLine();
	}

	public function getCharPositionInLine() {
		return $this->input->getCharPositionInLine();
	}
	
	/** What is the index of the current character of lookahead? */
	public function getCharIndex() {
		return $this->input->index();
	}
	

	/** Return the text matched so far for the current token or any
	 *  text override.
	 */
	public function getText() {
		if ( $this->state->text!=null ) {
			return $this->state->text;
		}
		return $this->input->substring($this->state->tokenStartCharIndex,$this->getCharIndex()-1);
	}

	/** Set the complete text of this token; it wipes any previous
	 *  changes to the text.
	 */
	public function setText($text) {
		$this->state->text = $text;
	}
	
	public function reportError($e) {
		/** TODO: not thought about recovery in lexer yet.
		 *
		// if we've already reported an error and have not matched a token
		// yet successfully, don't report any errors.
		if ( errorRecovery ) {
			//System.err.print("[SPURIOUS] ");
			return;
		}
		errorRecovery = true;
		 */

		$this->displayRecognitionError($this->getTokenNames(), $e);
	}
	
	public function getErrorMessage($e, $tokenNames) {
		$msg = null;
		if ( $e instanceof MismatchedTokenException ) {
			$mte = $e;
			$msg = "mismatched character ".$this->getCharErrorDisplay($e->c).
				" expecting ".$this->getCharErrorDisplay($mte->expecting);
		}
		else if ( $e instanceof NoViableAltException ) {
			$nvae = $e;
			// for development, can add "decision=<<"+nvae.grammarDecisionDescription+">>"
			// and "(decision="+nvae.decisionNumber+") and
			// "state "+nvae.stateNumber
			$msg = "no viable alternative at character ".$this->getCharErrorDisplay($e->c);
		}
		else if ( $e instanceof EarlyExitException ) {
			$eee = $e;
			// for development, can add "(decision="+eee.decisionNumber+")"
			$msg = "required (...)+ loop did not match anything at character ".$this->getCharErrorDisplay($e->c);
		}
		else if ( $e instanceof MismatchedNotSetException ) {
			$mse = $e;
			$msg = "mismatched character ".$this->getCharErrorDisplay($e->c)." expecting set ".$mse->expecting;
		}
		else if ( $e instanceof MismatchedSetException ) {
			$mse = $e;
			$msg = "mismatched character ".$this->getCharErrorDisplay($e->c)." expecting set ".$mse->expecting;
		}
		else if ( $e instanceof MismatchedRangeException ) {
			$mre = $e;
			$msg = "mismatched character ".$this->getCharErrorDisplay($e->c)." expecting set ".
				  $this->getCharErrorDisplay($mre->a)."..".$this->getCharErrorDisplay($mre->b);
		}
		else {
			$msg = parent::getErrorMessage($e, $tokenNames);
		}
		return $msg;
	}
	
	public function getCharErrorDisplay($c) {
		$s = chr($c);
		switch ( $s ) {
			case '\n' :
				$s = "\\n";
				break;
			case '\t' :
				$s = "\\t";
				break;
			case '\r' :
				$s = "\\r";
				break;
		}
		if ($c==TokenConst::$EOF){
			$s = "<EOF>";
		}
		return "'".$s."'";
	}
	
	/** Lexers can normally match any char in it's vocabulary after matching
	 *  a token, so do the easy thing and just kill a character and hope
	 *  it all works out.  You can instead use the rule invocation stack
	 *  to do sophisticated error recovery if you are in a fragment rule.
	 */
	public function recover($re) {
		$this->input->consume();
	}
	
	
	public function traceIn($ruleName, $ruleIndex)  {
		$inputSymbol = $this->input->LT(1)." line=".$this->getLine().":".$this->getCharPositionInLine();
		parent::traceIn($ruleName, $ruleIndex, $inputSymbol);
	}

	public function traceOut($ruleName, $ruleIndex)  {
		$inputSymbol = $this->input->LT(1)." line=".$this->getLine().":".$this->getCharPositionInLine();
		parent::traceOut($ruleName, $ruleIndex, $inputSymbol);
	}
}

?>