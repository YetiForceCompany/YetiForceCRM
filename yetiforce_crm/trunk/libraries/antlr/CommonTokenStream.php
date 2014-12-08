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

/** The most common stream of tokens is one where every token is buffered up
 *  and tokens are prefiltered for a certain channel (the parser will only
 *  see these tokens and cannot change the filter channel number during the
 *  parse).
 *
 *  TODO: how to access the full token stream?  How to track all tokens matched per rule?
 */
class CommonTokenStream implements TokenStream {
    protected $tokenSource;

	/** Record every single token pulled from the source so we can reproduce
	 *  chunks of it later.
	 */
	protected $tokens;

	/** Map<tokentype, channel> to override some Tokens' channel numbers */
	protected $channelOverrideMap;

	/** Set<tokentype>; discard any tokens with this type */
	protected $discardSet;

	/** Skip tokens on any channel but this one; this is how we skip whitespace... */
	protected $channel;

	/** By default, track all incoming tokens */
	protected $discardOffChannelTokens = false;

	/** Track the last mark() call result value for use in rewind(). */
	protected $lastMarker = 0;

	/** The index into the tokens list of the current token (next token
     *  to consume).  p==-1 indicates that the tokens list is empty
     */
    protected $p = -1;

	public function __construct($tokenSource, $channel=null) {
		$this->channel = TokenConst::$DEFAULT_CHANNEL;
		$this->tokens = array();
		$this->tokenSource = $tokenSource;
		if($channel != null){
			$this->channel = $channel;
		}
	}

	/** Reset this token stream by setting its token source. */
	public function setTokenSource($tokenSource) {
		$this->tokenSource = $tokenSource;
		$this->tokens = array();
		$this->p = -1;
		$this->channel = TokenConst::$DEFAULT_CHANNEL;
	}

	/** Load all tokens from the token source and put in tokens.
	 *  This is done upon first LT request because you might want to
	 *  set some token type / channel overrides before filling buffer.
	 */
	protected function fillBuffer() {
		$index = 0;
		$t = $this->tokenSource->nextToken();
		while ( $t!=null && $t->getType()!=CharStreamConst::$EOF ) {
			$discard = false;
			// is there a channel override for token type?
			if ( $this->channelOverrideMap!=null ) {
				$channelI = $this->channelOverrideMap[$t->getType()];
				if ( $channelI!=null ) {
					$t->setChannel($channelI);
				}
			}
			if ( $this->discardSet!=null &&
				 $this->discardSet->contains($t->getType()))
			{
				$discard = true;
			}
			else if ( $this->discardOffChannelTokens && $t->getChannel()!=$this->channel ) {
				$discard = true;
			}
			if ( !$discard )	{
				$t->setTokenIndex($index);
				$this->tokens[] = $t;
				$index++;
			}
			$t = $this->tokenSource->nextToken();
		}
		// leave p pointing at first token on channel
		$this->p = 0;
		$this->p = $this->skipOffTokenChannels($this->p);
    }

	/** Move the input pointer to the next incoming token.  The stream
	 *  must become active with LT(1) available.  consume() simply
	 *  moves the input pointer so that LT(1) points at the next
	 *  input symbol. Consume at least one token.
	 *
	 *  Walk past any token not on the channel the parser is listening to.
	 */
	public function consume() {
		if ( $this->p<sizeof($this->tokens)) {
            $this->p++;
			$this->p = $this->skipOffTokenChannels($this->p); // leave p on valid token
        }
    }

	/** Given a starting index, return the index of the first on-channel
	 *  token.
	 */
	protected function skipOffTokenChannels($i) {
		$n = sizeof($this->tokens);
		while ( $i<$n && $this->tokens[$i]->getChannel()!=$this->channel ) {
			$i++;
		}
		return $i;
	}

	protected function skipOffTokenChannelsReverse($i) {
		while ( $i>=0 && $this->tokens[$i]->getChannel()!=$this->channel) {
			$i--;
		}
		return $i;
	}

	/** A simple filter mechanism whereby you can tell this token stream
	 *  to force all tokens of type ttype to be on channel.  For example,
	 *  when interpreting, we cannot exec actions so we need to tell
	 *  the stream to force all WS and NEWLINE to be a different, ignored
	 *  channel.
	 */
	public function setTokenTypeChannel($ttype, $channel) {
		if ( $this->channelOverrideMap==null ) {
			$this->channelOverrideMap = array();
		}
        $this->channelOverrideMap[$ttype] = $channel;
	}

	public function discardTokenType($ttype) {
		if ( $this->discardSet==null ) {
			$this->discardSet = new Set();
		}
        $this->discardSet.add($ttype);
	}

	public function discardOffChannelTokens($discardOffChannelTokens) {
		$this->discardOffChannelTokens = $discardOffChannelTokens;
	}

	public function getTokens() {
		if ( $this->p == -1 ) {
			$this->fillBuffer();
		}
		return $this->tokens;
	}

	public function getTokensBetween($start, $stop) {
		return $this->getTokens($start, $stop, null);
	}

	/** Given a start and stop index, return a List of all tokens in
	 *  the token type BitSet.  Return null if no tokens were found.  This
	 *  method looks at both on and off channel tokens.
	 */
	public function getTokensOfTypeInSet($start, $stop, $types) {
		if ( $p == -1 ) {
			fillBuffer();
		}
		if ( $stop>=sizeof($this->tokens)) {
			$stop=sizeof($this->tokens) - 1;
		}
		if ( $start<0 ) {
			$start=0;
		}
		if ( $start>$stop ) {
			return null;
		}

		// list = tokens[start:stop]:{Token t, t.getType() in types}
		$filteredTokens = array();
		for ($i=$start; $i<=$stop; $i++) {
			$t = $this->tokens[$i];
			if ( $types==null || $types->member($t->getType())) {
				$filteredTokens->add($t);
			}
		}
		if ( sizeof($filteredTokens)==0 ) {
			$filteredTokens = null;
		}
		return $filteredTokens;
	}

	public function getTokensOfTypeInArray($start, $stop, $types) {
		return $this->getTokens($start, $stop,new Set(types));
	}

	public function getTokensofType($start, $stop, $ttype) {
		return $this->getTokens($start, $stop, new Set(array(ttype)));
	}

	/** Get the ith token from the current position 1..n where k=1 is the
	 *  first symbol of lookahead.
	 */
	public function LT($k) {
		if ( $this->p == -1 ) {
			$this->fillBuffer();
		}
		if ( $k==0 ) {
			return null;
		}
		if ( $k<0 ) {
			return $this->LB(-$k);
		}
		//System.out.print("LT(p="+p+","+k+")=");
		if ( ($this->p+$k-1) >= sizeof($this->tokens)) {
			return TokenConst::$EOF_TOKEN;
		}
		//System.out.println(tokens.get(p+k-1));
		$i = $this->p;
		$n = 1;
		// find k good tokens
		while ( $n<$k ) {
			// skip off-channel tokens
			$i = $this->skipOffTokenChannels($i+1); // leave p on valid token
			$n++;
		}
		if ( $i>=sizeof($this->tokens)) {
			return TokenConst::$EOF_TOKEN;
		}
        return $this->tokens[$i];
    }

	/** Look backwards k tokens on-channel tokens */
	protected function LB($k) {
		//System.out.print("LB(p="+p+","+k+") ");
		if ( $this->p == -1 ) {
			$this->fillBuffer();
		}
		if ( $k==0 ) {
			return null;
		}
		if ( ($this->p-$k)<0 ) {
			return null;
		}
		

		$i = $this->p;
		$n = 1;
		// find k good tokens looking backwards
		while ( $n<=$k ) {
			// skip off-channel tokens
			$i = $this->skipOffTokenChannelsReverse($i-1); // leave p on valid token
			$n++;
		}
		if ( $i<0 ) {
			return null;
		}
		return $this->tokens[$i];
	}

	/** Return absolute token i; ignore which channel the tokens are on;
	 *  that is, count all tokens not just on-channel tokens.
	 */
	public function get($i) {
		return $this->tokens[$i];
	}

    public function LA($i) {
		$lt = $this->LT($i);
        return $this->LT($i)->getType();
    }

    public function mark() {
		if ( $this->p == -1 ) {
			$this->fillBuffer();
		}
		$this->lastMarker = $this->index();
		return $this->lastMarker;
	}

	public function release($marker) {
		// no resources to release
	}

	public function size() {
		return sizeof($this->tokens);
	}

    public function index() {
        return $this->p;
    }

	public function rewind($marker = null) {
		if($marker===null){
			$marker = $this->lastmarker;
		}
		$this->seek($marker);
	}


	public function reset() {
		$this->p = 0;
		$this->lastMarker = 0;
	}
	
	public function seek($index) {
		$this->p = $index;
	}

	public function getTokenSource() {
		return $this->tokenSource;
	}

	public function getSourceName() {
		return $this->getTokenSource()->getSourceName();
	}

	public function toString() {
		if ( $this->p == -1 ) {
			$this->fillBuffer();
		}
		return $this->toStringBetween(0, sizeof($this->tokens)-1);
	}

	public function toStringBetween($start, $stop) {
		if ( $start<0 || $stop<0 ) {
			return null;
		}
		if ( $this->p == -1 ) {
			$this->fillBuffer();
		}
		if ( $stop>=sizeof($this->tokens)) {
			$stop = sizeof($this->tokens)-1;
		}
		$buf = "";
		for ($i = $start; $i <= $stop; $i++) {
			$t = $this->tokens[$i];
			$buf.=$t->getText();
		}
		return $buf;
	}

	public function toStringBetweenTokens($start, $stop) {
		if ( $start!=null && $stop!=null ) {
			return toString($this->start->getTokenIndex(), $this->stop->getTokenIndex());
		}
		return null;
	}
	
	public function __toString(){
		return $this->toString();
	}
}


?>