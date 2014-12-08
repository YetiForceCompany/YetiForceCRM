<?php

abstract class BaseRecognizer{
	
	public static $MEMO_RULE_FAILED = -2;
	public static $MEMO_RULE_UNKNOWN = -1;
	public static $INITIAL_FOLLOW_STACK_SIZE = 100;

	// copies from Token object for convenience in actions
	public static $DEFAULT_TOKEN_CHANNEL; //= TokenConst::$DEFAULT_CHANNEL;
	public static $HIDDEN; //= TokenConst::$HIDDEN_CHANNEL;

	public static $NEXT_TOKEN_RULE_NAME = "nextToken";
	
	public function __construct($state = null) {
		if ( $state==null ) {
			$state = new RecognizerSharedState();
		}
		$this->state = $state;
	}
	
	/** reset the parser's state; subclasses must rewinds the input stream */
	public function reset() {
		// wack everything related to error recovery
		if ( $this->state==null ) {
			return; // no shared state work to do
		}
		$this->state->_fsp = -1;
		$this->state->errorRecovery = false;
		$this->state->lastErrorIndex = -1;
		$this->state->failed = false;
		$this->state->syntaxErrors = 0;
		// wack everything related to backtracking and memoization
		$this->state->backtracking = 0;
		for ($i = 0; $this->state->ruleMemo!=null && $i < $this->state->ruleMemo->length; $i++) { // wipe cache
			$this->state->ruleMemo[$i] = null;
		}
	}


	/** Match current input symbol against ttype.  Attempt
	 *  single token insertion or deletion error recovery.  If
	 *  that fails, throw MismatchedTokenException.
	 *
	 *  To turn off single token insertion or deletion error
	 *  recovery, override mismatchRecover() and have it call
	 *  plain mismatch(), which does not recover.  Then any error
	 *  in a rule will cause an exception and immediate exit from
	 *  rule.  Rule would recover by resynchronizing to the set of
	 *  symbols that can follow rule ref.
	 */
	public function match($input, $ttype, $follow)
	{
		//System.out.println("match "+((TokenStream)input).LT(1));
		$matchedSymbol = $this->getCurrentInputSymbol($input);
		if ( $input->LA(1)==$ttype ) {
			$input->consume();
			$this->state->errorRecovery = false;
			$this->state->failed = false;
			return $matchedSymbol;
		}
		if ( $this->state->backtracking>0 ) {
			$this->state->failed = true;
			return $matchedSymbol;
		}
		$matchedSymbol = $this->recoverFromMismatchedToken($input, $ttype, $follow);
		return $matchedSymbol;
	}

	/** Match the wildcard: in a symbol */
	public function matchAny($input) {
		$this->state->errorRecovery = false;
		$this->state->failed = false;
		$input->consume();
	}

	public function mismatchIsUnwantedToken($input, $ttype) {
		return $input->LA(2)==$ttype;
	}

	public function mismatchIsMissingToken($input, $follow) {
		if ( $follow==null ) {
			// we have no information about the follow; we can only consume
			// a single token and hope for the best
			return $false;
		}
		// compute what can follow this grammar element reference
		if ( $follow->member(TokenConst::$EOR_TOKEN_TYPE) ) {
			$viableTokensFollowingThisRule = $this->computeContextSensitiveRuleFOLLOW();
			$follow = $follow->union($viableTokensFollowingThisRule);
            if ( $this->state->_fsp>=0 ) { // remove EOR if we're not the start symbol
                $follow->remove(TokenConst::$EOR_TOKEN_TYPE);
            }
		}
		// if current token is consistent with what could come after set
		// then we know we're missing a token; error recovery is free to
		// "insert" the missing token

		//System.out.println("viable tokens="+follow.toString(getTokenNames()));
		//System.out.println("LT(1)="+((TokenStream)input).LT(1));

		// BitSet cannot handle negative numbers like -1 (EOF) so I leave EOR
		// in follow set to indicate that the fall of the start symbol is
		// in the set (EOF can follow).
		if ( $follow->member($input->LA(1)) || $follow->member(TokenConst::$EOR_TOKEN_TYPE) ) {
			//System.out.println("LT(1)=="+((TokenStream)input).LT(1)+" is consistent with what follows; inserting...");
			return true;
		}
		return false;
	}

	/** Factor out what to do upon token mismatch so tree parsers can behave
	 *  differently.  Override and call mismatchRecover(input, ttype, follow)
	 *  to get single token insertion and deletion.  Use this to turn of
	 *  single token insertion and deletion. Override mismatchRecover
	 *  to call this instead.
	 */
	protected function mismatch($input, $ttype, $follow)
	{
		if ( $this->mismatchIsUnwantedToken($input, $ttype) ) {
			throw new UnwantedTokenException($ttype, $input);
		}
		else if ( $this->mismatchIsMissingToken($input, $follow) ) {
			throw new MissingTokenException($ttype, $input, null);
		}
		throw new MismatchedTokenException($ttype, $input);
	}

	/** Report a recognition problem.
	 *
	 *  This method sets errorRecovery to indicate the parser is recovering
	 *  not parsing.  Once in recovery mode, no errors are generated.
	 *  To get out of recovery mode, the parser must successfully match
	 *  a token (after a resync).  So it will go:
	 *
	 * 		1. error occurs
	 * 		2. enter recovery mode, report error
	 * 		3. consume until token found in resynch set
	 * 		4. try to resume parsing
	 * 		5. next match() will reset errorRecovery mode
	 *
	 *  If you override, make sure to update syntaxErrors if you care about that.
	 */
	public function reportError($e) {
		// if we've already reported an error and have not matched a token
		// yet successfully, don't report any errors.
		if ( $this->state->errorRecovery ) {
			//System.err.print("[SPURIOUS] ");
			return;
		}
		$this->state->syntaxErrors++; // don't count spurious
		$this->state->errorRecovery = true;

		$this->displayRecognitionError($this->getTokenNames(), $e);
	}
	
	
	public function displayRecognitionError($tokenNames, $e){
		$hdr = $this->getErrorHeader($e);
		$msg = $this->getErrorMessage($e, $tokenNames);
		$this->emitErrorMessage($hdr." ".$msg);
	}
	
	/** What error message should be generated for the various
	 *  exception types?
	 *
	 *  Not very object-oriented code, but I like having all error message
	 *  generation within one method rather than spread among all of the
	 *  exception classes. This also makes it much easier for the exception
	 *  handling because the exception classes do not have to have pointers back
	 *  to this object to access utility routines and so on. Also, changing
	 *  the message for an exception type would be difficult because you
	 *  would have to subclassing exception, but then somehow get ANTLR
	 *  to make those kinds of exception objects instead of the default.
	 *  This looks weird, but trust me--it makes the most sense in terms
	 *  of flexibility.
	 *
	 *  For grammar debugging, you will want to override this to add
	 *  more information such as the stack frame with
	 *  getRuleInvocationStack(e, this.getClass().getName()) and,
	 *  for no viable alts, the decision description and state etc...
	 *
	 *  Override this to change the message generated for one or more
	 *  exception types.
	 */
	public function getErrorMessage($e, $tokenNames) {
		$msg = $e->getMessage();
		if ( $e instanceof UnwantedTokenException ) {
			$ute = $e;
			$tokenName="<unknown>";
			if ( $ute->expecting== TokenConst::$EOF ) {
				$tokenName = "EOF";
			}
			else {
				$tokenName = $tokenNames[$ute->expecting];
			}
			$msg = "extraneous input ".$this->getTokenErrorDisplay($ute->getUnexpectedToken()).
				" expecting ".$tokenName;
		}
		else if ( $e instanceof MissingTokenException ) {
			$mte = $e;
			$tokenName="<unknown>";
			if ( $mte->expecting== TokenConst::$EOF ) {
				$tokenName = "EOF";
			}
			else {
				$tokenName = $tokenNames[$mte->expecting];
			}
			$msg = "missing ".$tokenName." at ".$this->getTokenErrorDisplay($e->token);
		}
		else if ( $e instanceof MismatchedTokenException ) {
			$mte = $e;
			$tokenName="<unknown>";
			if ( $mte->expecting== TokenConst::$EOF ) {
				$tokenName = "EOF";
			}
			else {
				$tokenName = $tokenNames[$mte->expecting];
			}
			$msg = "mismatched input ".$this->getTokenErrorDisplay($e->token).
				" expecting ".$tokenName;
		}
		else if ( $e instanceof MismatchedTreeNodeException ) {
			$mtne = $e;
			$tokenName="<unknown>";
			if ( $mtne->expecting==TokenConst::$EOF ) {
				$tokenName = "EOF";
			}
			else {
				$tokenName = $tokenNames[$mtne->expecting];
			}
			$msg = "mismatched tree node: ".$mtne->node.
				" expecting ".$tokenName;
		}
		else if ( $e instanceof NoViableAltException ) {
			$nvae = $e;
			// for development, can add "decision=<<"+nvae.grammarDecisionDescription+">>"
			// and "(decision="+nvae.decisionNumber+") and
			// "state "+nvae.stateNumber
			$msg = "no viable alternative at input ".$this->getTokenErrorDisplay($e->token);
		}
		else if ( $e instanceof EarlyExitException ) {
			$eee = $e;
			// for development, can add "(decision="+eee.decisionNumber+")"
			$msg = "required (...)+ loop did not match anything at input ".
				getTokenErrorDisplay($e->token);
		}
		else if ( $e instanceof MismatchedSetException ) {
			$mse = $e;
			$msg = "mismatched input ".$this->getTokenErrorDisplay($e->token).
				" expecting set ".$mse->expecting;
		}
		else if ( $e instanceof MismatchedNotSetException ) {
			$mse = $e;
			$msg = "mismatched input ".$this->getTokenErrorDisplay($e->token).
				" expecting set ".$mse->expecting;
		}
		else if ( $e instanceof FailedPredicateException ) {
			$fpe = $e;
			$msg = "rule ".$fpe->ruleName." failed predicate: {".
				$fpe->predicateText."}?";
		}
		return $msg;
	}

	/** Get number of recognition errors (lexer, parser, tree parser).  Each
	 *  recognizer tracks its own number.  So parser and lexer each have
	 *  separate count.  Does not count the spurious errors found between
	 *  an error and next valid token match
	 *
	 *  See also reportError()
	 */
	public function getNumberOfSyntaxErrors() {
		return $state->syntaxErrors;
	}

	/** What is the error header, normally line/character position information? */
	public function getErrorHeader($e) {
		return "line ".$e->line.":".$e->charPositionInLine;
	}
	
	
	/** How should a token be displayed in an error message? The default
	 *  is to display just the text, but during development you might
	 *  want to have a lot of information spit out.  Override in that case
	 *  to use t.toString() (which, for CommonToken, dumps everything about
	 *  the token). This is better than forcing you to override a method in
	 *  your token objects because you don't have to go modify your lexer
	 *  so that it creates a new Java type.
	 */
	public function getTokenErrorDisplay($t) {
		$s = $t->getText();
		if ( $s==null ) {
			if ( $t->getType()==TokenConst::$EOF ) {
				$s = "<EOF>";
			}
			else {
				$s = "<".$t->getType().">";
			}
		}
		$s = str_replace("\n", '\n', $s);
		$s = str_replace("\r",'\r', $s);
		$s = str_replace("\t",'\t', $s);
		return "'".$s."'";
	}

	/** Override this method to change where error messages go */
	public function emitErrorMessage($msg) {
		echo $msg;
	}

	/** Recover from an error found on the input stream.  This is
	 *  for NoViableAlt and mismatched symbol exceptions.  If you enable
	 *  single token insertion and deletion, this will usually not
	 *  handle mismatched symbol exceptions but there could be a mismatched
	 *  token that the match() routine could not recover from.
	 */
	public function recover($input, $re) {
		if ( $this->state->lastErrorIndex==$input->index() ) {
			// uh oh, another error at same token index; must be a case
			// where LT(1) is in the recovery token set so nothing is
			// consumed; consume a single token so at least to prevent
			// an infinite loop; this is a failsafe.
			$input->consume();
		}
		$this->state->lastErrorIndex = $input->index();
		$followSet = $this->computeErrorRecoverySet();
		$this->beginResync();
		$this->consumeUntilInSet($input, $followSet);
		$this->endResync();
	}

	/** A hook to listen in on the token consumption during error recovery.
	 *  The DebugParser subclasses this to fire events to the listenter.
	 */
	public function beginResync() {
	}

	public function endResync() {
	}

	/*  Compute the error recovery set for the current rule.  During
	 *  rule invocation, the parser pushes the set of tokens that can
	 *  follow that rule reference on the stack; this amounts to
	 *  computing FIRST of what follows the rule reference in the
	 *  enclosing rule. This local follow set only includes tokens
	 *  from within the rule; i.e., the FIRST computation done by
	 *  ANTLR stops at the end of a rule.
	 *
	 *  EXAMPLE
	 *
	 *  When you find a "no viable alt exception", the input is not
	 *  consistent with any of the alternatives for rule r.  The best
	 *  thing to do is to consume tokens until you see something that
	 *  can legally follow a call to r *or* any rule that called r.
	 *  You don't want the exact set of viable next tokens because the
	 *  input might just be missing a token--you might consume the
	 *  rest of the input looking for one of the missing tokens.
	 *
	 *  Consider grammar:
	 *
	 *  a : '[' b ']'
	 *    | '(' b ')'
	 *    ;
	 *  b : c '^' INT ;
	 *  c : ID
	 *    | INT
	 *    ;
	 *
	 *  At each rule invocation, the set of tokens that could follow
	 *  that rule is pushed on a stack.  Here are the various "local"
	 *  follow sets:
	 *
	 *  FOLLOW(b1_in_a) = FIRST(']') = ']'
	 *  FOLLOW(b2_in_a) = FIRST(')') = ')'
	 *  FOLLOW(c_in_b) = FIRST('^') = '^'
	 *
	 *  Upon erroneous input "[]", the call chain is
	 *
	 *  a -> b -> c
	 *
	 *  and, hence, the follow context stack is:
	 *
	 *  depth  local follow set     after call to rule
	 *    0         <EOF>                    a (from main())
	 *    1          ']'                     b
	 *    3          '^'                     c
	 *
	 *  Notice that ')' is not included, because b would have to have
	 *  been called from a different context in rule a for ')' to be
	 *  included.
	 *
	 *  For error recovery, we cannot consider FOLLOW(c)
	 *  (context-sensitive or otherwise).  We need the combined set of
	 *  all context-sensitive FOLLOW sets--the set of all tokens that
	 *  could follow any reference in the call chain.  We need to
	 *  resync to one of those tokens.  Note that FOLLOW(c)='^' and if
	 *  we resync'd to that token, we'd consume until EOF.  We need to
	 *  sync to context-sensitive FOLLOWs for a, b, and c: {']','^'}.
	 *  In this case, for input "[]", LA(1) is in this set so we would
	 *  not consume anything and after printing an error rule c would
	 *  return normally.  It would not find the required '^' though.
	 *  At this point, it gets a mismatched token error and throws an
	 *  exception (since LA(1) is not in the viable following token
	 *  set).  The rule exception handler tries to recover, but finds
	 *  the same recovery set and doesn't consume anything.  Rule b
	 *  exits normally returning to rule a.  Now it finds the ']' (and
	 *  with the successful match exits errorRecovery mode).
	 *
	 *  So, you cna see that the parser walks up call chain looking
	 *  for the token that was a member of the recovery set.
	 *
	 *  Errors are not generated in errorRecovery mode.
	 *
	 *  ANTLR's error recovery mechanism is based upon original ideas:
	 *
	 *  "Algorithms + Data Structures = Programs" by Niklaus Wirth
	 *
	 *  and
	 *
	 *  "A note on error recovery in recursive descent parsers":
	 *  http://portal.acm.org/citation.cfm?id=947902.947905
	 *
	 *  Later, Josef Grosch had some good ideas:
	 *
	 *  "Efficient and Comfortable Error Recovery in Recursive Descent
	 *  Parsers":
	 *  ftp://www.cocolab.com/products/cocktail/doca4.ps/ell.ps.zip
	 *
	 *  Like Grosch I implemented local FOLLOW sets that are combined
	 *  at run-time upon error to avoid overhead during parsing.
	 */
	protected function computeErrorRecoverySet() {
		return $this->combineFollows(false);
	}

	/** Compute the context-sensitive FOLLOW set for current rule.
	 *  This is set of token types that can follow a specific rule
	 *  reference given a specific call chain.  You get the set of
	 *  viable tokens that can possibly come next (lookahead depth 1)
	 *  given the current call chain.  Contrast this with the
	 *  definition of plain FOLLOW for rule r:
	 *
	 *   FOLLOW(r)={x | S=>*alpha r beta in G and x in FIRST(beta)}
	 *
	 *  where x in T* and alpha, beta in V*; T is set of terminals and
	 *  V is the set of terminals and nonterminals.  In other words,
	 *  FOLLOW(r) is the set of all tokens that can possibly follow
	 *  references to r in *any* sentential form (context).  At
	 *  runtime, however, we know precisely which context applies as
	 *  we have the call chain.  We may compute the exact (rather
	 *  than covering superset) set of following tokens.
	 *
	 *  For example, consider grammar:
	 *
	 *  stat : ID '=' expr ';'      // FOLLOW(stat)=={EOF}
	 *       | "return" expr '.'
	 *       ;
	 *  expr : atom ('+' atom)* ;   // FOLLOW(expr)=={';','.',')'}
	 *  atom : INT                  // FOLLOW(atom)=={'+',')',';','.'}
	 *       | '(' expr ')'
	 *       ;
	 *
	 *  The FOLLOW sets are all inclusive whereas context-sensitive
	 *  FOLLOW sets are precisely what could follow a rule reference.
	 *  For input input "i=(3);", here is the derivation:
	 *
	 *  stat => ID '=' expr ';'
	 *       => ID '=' atom ('+' atom)* ';'
	 *       => ID '=' '(' expr ')' ('+' atom)* ';'
	 *       => ID '=' '(' atom ')' ('+' atom)* ';'
	 *       => ID '=' '(' INT ')' ('+' atom)* ';'
	 *       => ID '=' '(' INT ')' ';'
	 *
	 *  At the "3" token, you'd have a call chain of
	 *
	 *    stat -> expr -> atom -> expr -> atom
	 *
	 *  What can follow that specific nested ref to atom?  Exactly ')'
	 *  as you can see by looking at the derivation of this specific
	 *  input.  Contrast this with the FOLLOW(atom)={'+',')',';','.'}.
	 *
	 *  You want the exact viable token set when recovering from a
	 *  token mismatch.  Upon token mismatch, if LA(1) is member of
	 *  the viable next token set, then you know there is most likely
	 *  a missing token in the input stream.  "Insert" one by just not
	 *  throwing an exception.
	 */
	protected function computeContextSensitiveRuleFOLLOW() {
		return $this->combineFollows(true);
	}

	protected function combineFollows($exact) {
		$top = $this->state->_fsp;
		$followSet = new Set(array());
		for ($i=$top; $i>=0; $i--) {
			$localFollowSet = $this->state->following[$i];
			/*
			System.out.println("local follow depth "+i+"="+
							   localFollowSet.toString(getTokenNames())+")");
			 */
			$followSet->unionInPlace($localFollowSet);
			if ( $this->exact ) {
				// can we see end of rule?
				if ( $localFollowSet->member(TokenConst::$EOR_TOKEN_TYPE) ) {
					// Only leave EOR in set if at top (start rule); this lets
					// us know if have to include follow(start rule); i.e., EOF
					if ( $i>0 ) {
						$followSet->remove(TokenConst::$EOR_TOKEN_TYPE);
					}
				}
				else { // can't see end of rule, quit
					break;
				}
			}
		}
		return $followSet;
	}

	/** Attempt to recover from a single missing or extra token.
	 *
	 *  EXTRA TOKEN
	 *
	 *  LA(1) is not what we are looking for.  If LA(2) has the right token,
	 *  however, then assume LA(1) is some extra spurious token.  Delete it
	 *  and LA(2) as if we were doing a normal match(), which advances the
	 *  input.
	 *
	 *  MISSING TOKEN
	 *
	 *  If current token is consistent with what could come after
	 *  ttype then it is ok to "insert" the missing token, else throw
	 *  exception For example, Input "i=(3;" is clearly missing the
	 *  ')'.  When the parser returns from the nested call to expr, it
	 *  will have call chain:
	 *
	 *    stat -> expr -> atom
	 *
	 *  and it will be trying to match the ')' at this point in the
	 *  derivation:
	 *
	 *       => ID '=' '(' INT ')' ('+' atom)* ';'
	 *                          ^
	 *  match() will see that ';' doesn't match ')' and report a
	 *  mismatched token error.  To recover, it sees that LA(1)==';'
	 *  is in the set of tokens that can follow the ')' token
	 *  reference in rule atom.  It can assume that you forgot the ')'.
	 */
	protected function recoverFromMismatchedToken($input, $ttype, $follow)
	{
		$e = null;
		// if next token is what we are looking for then "delete" this token

		if ( $this->mismatchIsUnwantedToken($input, $ttype) ) {
			$e = new UnwantedTokenException($ttype, $input);
			/*
			System.err.println("recoverFromMismatchedToken deleting "+
							   ((TokenStream)input).LT(1)+
							   " since "+((TokenStream)input).LT(2)+" is what we want");
			 */
			$this->beginResync();
			$input->consume(); // simply delete extra token
			$this->endResync();
			$this->reportError($e);  // report after consuming so AW sees the token in the exception
			// we want to return the token we're actually matching
			$matchedSymbol = $this->getCurrentInputSymbol($input);
			$input->consume(); // move past ttype token as if all were ok
			return $matchedSymbol;
		}
		// can't recover with single token deletion, try insertion
		if ( $this->mismatchIsMissingToken($input, $follow) ) {
			$inserted = $this->getMissingSymbol($input, $e, $ttype, $follow);
			$e = new MissingTokenException($ttype, $input, $inserted);
			$this->reportError($e);  // report after inserting so AW sees the token in the exception
			return $inserted;
		}
		// even that didn't work; must throw the exception
		$e = new MismatchedTokenException($ttype, $input);
		throw $e;
	}

	/** Not currently used */
	public function recoverFromMismatchedSet($input, $e, $follow) {
		if ( $this->mismatchIsMissingToken($input, $follow) ) {
			// System.out.println("missing token");
			reportError($e);
			// we don't know how to conjure up a token for sets yet
			return $this->getMissingSymbol($input, $e, TokenConst::$INVALID_TOKEN_TYPE, $follow);
		}
		// TODO do single token deletion like above for Token mismatch
		throw $e;
	}

	/** Match needs to return the current input symbol, which gets put
	 *  into the label for the associated token ref; e.g., x=ID.  Token
	 *  and tree parsers need to return different objects. Rather than test
	 *  for input stream type or change the IntStream interface, I use
	 *  a simple method to ask the recognizer to tell me what the current
	 *  input symbol is.
	 * 
	 *  This is ignored for lexers.
	 */
	protected function getCurrentInputSymbol($input) { return null; }

	/** Conjure up a missing token during error recovery.
	 *
	 *  The recognizer attempts to recover from single missing
	 *  symbols. But, actions might refer to that missing symbol.
	 *  For example, x=ID {f($x);}. The action clearly assumes
	 *  that there has been an identifier matched previously and that
	 *  $x points at that token. If that token is missing, but
	 *  the next token in the stream is what we want we assume that
	 *  this token is missing and we keep going. Because we
	 *  have to return some token to replace the missing token,
	 *  we have to conjure one up. This method gives the user control
	 *  over the tokens returned for missing tokens. Mostly,
	 *  you will want to create something special for identifier
	 *  tokens. For literals such as '{' and ',', the default
	 *  action in the parser or tree parser works. It simply creates
	 *  a CommonToken of the appropriate type. The text will be the token.
	 *  If you change what tokens must be created by the lexer,
	 *  override this method to create the appropriate tokens.
	 */
	protected function getMissingSymbol($input, $e, $expectedTokenType, $follow) {
		return null;
	}

	public function consumeUntilMatchesType($input, $tokenType) {
		//System.out.println("consumeUntil "+tokenType);
		$ttype = $input->LA(1);
		while ($ttype != TokenConst::$EOF && $ttype != $tokenType) {
			$input->consume();
			$ttype = $input->LA(1);
		}
	}

	/** Consume tokens until one matches the given token set */
	public function consumeUntilInSet($input, $set) {
		//System.out.println("consumeUntil("+set.toString(getTokenNames())+")");
		$ttype = $input->LA(1);
		while ($ttype != TokenConst::$EOF && !$set->member($ttype) ) {
			//System.out.println("consume during recover LA(1)="+getTokenNames()[input.LA(1)]);
			$input->consume();
			$ttype = $input->LA(1);
		}
	}

	/** Push a rule's follow set using our own hardcoded stack */
	protected function pushFollow($fset) {
		// if ( ($this->state->_fsp +1)>=sizeof($this->state->following) ) {
		// 			$f = array();
		// 			System.arraycopy(state.following, 0, f, 0, state.following.length-1);
		// 			$this->state->following = f;
		// 		}
 		$this->state->following[++$this->state->_fsp] = $fset;
	}

	/** Return List<String> of the rules in your parser instance
	 *  leading up to a call to this method.  You could override if
	 *  you want more details such as the file/line info of where
	 *  in the parser java code a rule is invoked.
	 *
	 *  This is very useful for error messages and for context-sensitive
	 *  error recovery.
	 */

	/** A more general version of getRuleInvocationStack where you can
	 *  pass in, for example, a RecognitionException to get it's rule
	 *  stack trace.  This routine is shared with all recognizers, hence,
	 *  static.
	 *
	 *  TODO: move to a utility class or something; weird having lexer call this
	 */
	public static function getRuleInvocationStack($e=null,
											  $recognizerClassName=null)
	{
		if($e==null){
			$e = new Exception();
		}
		if($recognizerClassName==null){
			$recognizerClassName = get_class($this);
		}
		throw new Exception("Not implemented yet");
		// List rules = new ArrayList();
		// 		StackTraceElement[] stack = e.getStackTrace();
		// 		int i = 0;
		// 		for (i=stack.length-1; i>=0; i--) {
		// 			StackTraceElement t = stack[i];
		// 			if ( t.getClassName().startsWith("org.antlr.runtime.") ) {
		// 				continue; // skip support code such as this method
		// 			}
		// 			if ( t.getMethodName().equals(NEXT_TOKEN_RULE_NAME) ) {
		// 				continue;
		// 			}
		// 			if ( !t.getClassName().equals(recognizerClassName) ) {
		// 				continue; // must not be part of this parser
		// 			}
		//             rules.add(t.getMethodName());
		// 		}
		// 		return rules;
	}

	public function getBacktrackingLevel() {
		return $this->state->backtracking;
	}

	/** Used to print out token names like ID during debugging and
	 *  error reporting.  The generated parsers implement a method
	 *  that overrides this to point to their String[] tokenNames.
	 */
	public function getTokenNames() {
		return null;
	}

	/** For debugging and other purposes, might want the grammar name.
	 *  Have ANTLR generate an implementation for this method.
	 */
	public function getGrammarFileName() {
		return null;
	}

	public abstract function getSourceName();

	/** A convenience method for use most often with template rewrites.
	 *  Convert a List<Token> to List<String>
	 */
	public function toStrings($tokens) {
		if ( $tokens==null ) return null;
		$strings = array();
		for ($i=0; $i<$tokens->size(); $i++) {
			$strings[] = $tokens[$i]->getText();
		}
		return $strings;
	}

	/** Given a rule number and a start token index number, return
	 *  MEMO_RULE_UNKNOWN if the rule has not parsed input starting from
	 *  start index.  If this rule has parsed input starting from the
	 *  start index before, then return where the rule stopped parsing.
	 *  It returns the index of the last token matched by the rule.
	 *
	 *  For now we use a hashtable and just the slow Object-based one.
	 *  Later, we can make a special one for ints and also one that
	 *  tosses out data after we commit past input position i.
	 */
	public function getRuleMemoization($ruleIndex, $ruleStartIndex) {
		if ( $this->state->ruleMemo[$ruleIndex]==null ) {
			$this->state->ruleMemo[$ruleIndex] = array();
		}
		$stopIndexI =
			$this->state->ruleMemo[$ruleIndex][$ruleStartIndex];
		if ( $stopIndexI==null ) {
			return self::$MEMO_RULE_UNKNOWN;
		}
		return $stopIndexI;
	}

	/** Has this rule already parsed input at the current index in the
	 *  input stream?  Return the stop token index or MEMO_RULE_UNKNOWN.
	 *  If we attempted but failed to parse properly before, return
	 *  MEMO_RULE_FAILED.
	 *
	 *  This method has a side-effect: if we have seen this input for
	 *  this rule and successfully parsed before, then seek ahead to
	 *  1 past the stop token matched for this rule last time.
	 */
	public function alreadyParsedRule($input, $ruleIndex) {
		$stopIndex = $this->getRuleMemoization($ruleIndex, $input->index());
		if ( $stopIndex==self::$MEMO_RULE_UNKNOWN ) {
			return false;
		}
		if ( $stopIndex==self::$MEMO_RULE_FAILED ) {
			//System.out.println("rule "+ruleIndex+" will never succeed");
			$this->state->failed=true;
		}
		else {
			//System.out.println("seen rule "+ruleIndex+" before; skipping ahead to @"+(stopIndex+1)+" failed="+state.failed);
			$input->seek($stopIndex+1); // jump to one past stop token
		}
		return true;
	}

	/** Record whether or not this rule parsed the input at this position
	 *  successfully.  Use a standard java hashtable for now.
	 */
	public function memoize($input, $ruleIndex, $ruleStartIndex){
		$stopTokenIndex = $this->state->failed?self::$MEMO_RULE_FAILED:$input->index()-1;
		if ( $this->state->ruleMemo==null ) {
			echo("!!!!!!!!! memo array is null for ". getGrammarFileName());
		}
		if ( $ruleIndex >= sizeof($this->state->ruleMemo) ) {
			echo("!!!!!!!!! memo size is ".sizeof($this->state->ruleMemo).", but rule index is ".$ruleIndex);
		}
		if ( $this->state->ruleMemo[$ruleIndex]!=null ) {
			$this->state->ruleMemo[$ruleIndex][$ruleStartIndex] = $stopTokenIndex;
		}
	}

	/** return how many rule/input-index pairs there are in total.
	 *  TODO: this includes synpreds. :(
	 */
	public function getRuleMemoizationCacheSize() {
		$n = 0;
		for ($i = 0; $this->state->ruleMemo!=null && $i < sizeof($this->state->ruleMemo); $i++) {
			$ruleMap = $this->state->ruleMemo[$i];
			if ( $ruleMap!=null ) {
				$n += sizeof($ruleMap); // how many input indexes are recorded?
			}
		}
		return $n;
	}

	public function traceIn($ruleName, $ruleIndex, $inputSymbol)  {
		echo("enter ".$ruleName." ".$inputSymbol);
		if ( $this->state->failed ) {
			echo(" failed=".$this->state->failed);
		}
		if ( $this->state->backtracking>0 ) {
			echo(" backtracking=".$this->state->backtracking);
		}
		echo "\n";
	}

	public function traceOut($ruleName, $ruleIndex, $inputSymbol) {
		echo("exit ".$ruleName." ".$inputSymbol);
		if ( $this->state->failed ) {
			echo(" failed=".$this->state->failed);
		}
		if ( $this->state->backtracking>0 ) {
			echo(" backtracking="+$this->state->backtracking);
		}
		echo "\n";
	}

	public function getToken($name){
		if(preg_match("/\d+/", $name)){
			return (integer)$name;
		}else{
			return $this->$name;
		}
	}
	
	public function getTokenName($tokenId){
		
	}

}

BaseRecognizer::$DEFAULT_TOKEN_CHANNEL = TokenConst::$DEFAULT_CHANNEL;
BaseRecognizer::$HIDDEN = TokenConst::$HIDDEN_CHANNEL;
?>