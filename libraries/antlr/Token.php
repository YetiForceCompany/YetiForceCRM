<?php

	class TokenConst{
		public static $EOR_TOKEN_TYPE = 1;

		/** imaginary tree navigation type; traverse "get child" link */
		public static $DOWN = 2;
		/** imaginary tree navigation type; finish with a child list */
		public static $UP = 3;

		public static $MIN_TOKEN_TYPE;// = UP+1;

	    public static $EOF;// = CharStream.EOF;
		public static $EOF_TOKEN;// = new CommonToken(EOF);

		public static $INVALID_TOKEN_TYPE = 0;
		public static $INVALID_TOKEN;// = new CommonToken(INVALID_TOKEN_TYPE);

		/** In an action, a lexer rule can set token to this SKIP_TOKEN and ANTLR
		 *  will avoid creating a token for this symbol and try to fetch another.
		 */
		public static $SKIP_TOKEN;// = new CommonToken(INVALID_TOKEN_TYPE);

		/** All tokens go to the parser (unless skip() is called in that rule)
		 *  on a particular "channel".  The parser tunes to a particular channel
		 *  so that whitespace etc... can go to the parser on a "hidden" channel.
		 */
		public static $DEFAULT_CHANNEL = 0;

		/** Anything on different channel than DEFAULT_CHANNEL is not parsed
		 *  by parser.
		 */
		public static $HIDDEN_CHANNEL = 99;
	}
	
	
	interface Token{
	}
	
	
	class CommonToken implements Token {

		
		function __construct(){
			
		}
		
		public static function forInput($input=null, $type, $channel=0, $start=0, $stop=0) {
			$ct = new CommonToken();
			$ct->charPositionInLine=-1;
			$ct->input = $input;
			$ct->type = $type;
			$ct->channel = $channel;
			$ct->start = $start;
			$ct->stop = $stop;
			return $ct;
		}
		
		public static function forType($type){
			return CommonToken::forInput($input=null, $type);
		}
	
		public static function forTypeAndText($type, $text) {
			$ct = new CommonToken();
			$ct->type = $type;
			$ct->channel = TokenConst::$DEFAULT_CHANNEL;
			$ct->text = $text;
			return $ct;
		}
/*
		public CommonToken(Token oldToken) {
			text = oldToken.getText();
			type = oldToken.getType();
			line = oldToken.getLine();
			index = oldToken.getTokenIndex();
			charPositionInLine = oldToken.getCharPositionInLine();
			channel = oldToken.getChannel();
			if ( oldToken instanceof CommonToken ) {
				start = ((CommonToken)oldToken).start;
				stop = ((CommonToken)oldToken).stop;
			}
		}
		*/
		public function getType() {
			return $this->type;
		}

		public function setLine($line) {
			$this->line = $this->line;
		}

		public function getText() {
			if ( $this->text!=null ) {
				return $this->text;
			}
			if ( $this->input==null ) {
				return null;
			}
			$this->text = $this->input->substring($this->start,$this->stop);
			return $this->text;
		}

		/** Override the text for this token.  getText() will return this text
		 *  rather than pulling from the buffer.  Note that this does not mean
		 *  that start/stop indexes are not valid.  It means that that input
		 *  was converted to a new string in the token object.
		 */
		public function setText($text) {
			$this->text = $this->text;
		}

		public function getLine() {
			return $this->line;
		}

		public function getCharPositionInLine() {
			return $this->charPositionInLine;
		}

		public function setCharPositionInLine($charPositionInLine) {
			$this->charPositionInLine = $this->charPositionInLine;
		}

		public function getChannel() {
			return $this->channel;
		}

		public function setChannel($channel) {
			$this->channel = $this->channel;
		}

		public function setType($type) {
			$this->type = $this->type;
		}

		public function getStartIndex() {
			return $this->start;
		}

		public function setStartIndex($start) {
			$this->start = $this->start;
		}

		public function getStopIndex() {
			return $this->stop;
		}

		public function setStopIndex($stop) {
			$this->stop = $this->stop;
		}

		public function getTokenIndex() {
			return $this->index;
		}

		public function setTokenIndex($index) {
			$this->index = $this->index;
		}

		public function getInputStream() {
			return $this->input;
		}

		public function setInputStream($input) {
			$this->input = $this->input;
		}

		public function toString() {
			$channelStr = "";
			if ( $this->channel>0 ) {
				$channelStr=",channel=".$this->channel;
			}
			$txt = $this->getText();
			if ( $txt!=null ) {
				$txt = str_replace("\n",'\n', $txt);
				$txt = str_replace("\r",'\r', $txt);
				$txt = str_replace("\t",'\t', $txt);
			}
			else {
				$txt = "<no text>";
			}
			return "[@".$this->getTokenIndex().",".$this->start.":".$this->stop."='".$txt."',<".$this->type.">".$channelStr.",".$this->line.":".$this->getCharPositionInLine()."]";
		}
		
		public function __toString(){
			return $this->toString();
		}
	}
	
	TokenConst::$DEFAULT_CHANNEL=0;
	TokenConst::$INVALID_TOKEN_TYPE=0;

	TokenConst::$EOF = CharStreamConst::$EOF;
	TokenConst::$EOF_TOKEN = CommonToken::forType(TokenConst::$EOF);
	
	TokenConst::$INVALID_TOKEN_TYPE = 0;
	TokenConst::$INVALID_TOKEN = CommonToken::forType(TokenConst::$INVALID_TOKEN_TYPE);
	/** In an action, a lexer rule can set token to this SKIP_TOKEN and ANTLR
	 *  will avoid creating a token for this symbol and try to fetch another.
	 */
	TokenConst::$SKIP_TOKEN = CommonToken::forType(TokenConst::$INVALID_TOKEN_TYPE);
	
	/** All tokens go to the parser (unless skip() is called in that rule)
	 *  on a particular "channel".  The parser tunes to a particular channel
	 *  so that whitespace etc... can go to the parser on a "hidden" channel.
	 */
	TokenConst::$DEFAULT_CHANNEL = 0;
	
	/** Anything on different channel than DEFAULT_CHANNEL is not parsed
	 *  by parser.
	 */
	TokenConst::$HIDDEN_CHANNEL = 99;
	
	
	
	TokenConst::$MIN_TOKEN_TYPE = TokenConst::$UP+1;



	
	
?>