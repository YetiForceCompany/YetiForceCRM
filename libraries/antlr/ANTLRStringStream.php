<?php
	CharStreamConst::$EOF = -1;

	class ANTLRStringStream implements CharStream {

		/** Copy data in string to a local char array */
		public function __construct($input) {
			$this->p=0;
			$this->line = 1;
			$this->charPositionInLine = 0;
			$this->markDepth = 0;
			$this->markers = null;
			$this->lastMarker=0;
			$this->name=null;
			
			$this->data = strToIntArray($input);
			$this->n = strlen($input);
		}

		/** Reset the stream so that it's in the same state it was
		 *  when the object was created *except* the data array is not
		 *  touched.
		 */
		public function reset() {
			$this->p = 0;
			$this->line = 1;
			$this->charPositionInLine = 0;
			$this->markDepth = 0;
		}

	    public function consume() {
	        if ( $this->p < $this->n ) {
				$this->charPositionInLine++;
				if ( $this->data[$this->p]==ord("\n") ) {
					$this->line++;
					$this->charPositionInLine=0;
				}
	            $this->p++;
	        }
	    }

	    public function LA($i) {
			if ( $i==0 ) {
				return 0; // undefined
			}
			if ( $i<0 ) {
				$i++; // e.g., translate LA(-1) to use offset i=0; then data[p+0-1]
				if ( ($this->p+$i-1) < 0 ) {
					return CharStreamConst::$EOF; // invalid; no char before first char
				}
			}

			if ( ($this->p+$i-1) >= $this->n ) {
	            //System.out.println("char LA("+i+")=EOF; p="+p);
	            return CharStreamConst::$EOF;
	        }
	        //System.out.println("char LA("+i+")="+(char)data[p+i-1]+"; p="+p);
			//System.out.println("LA("+i+"); p="+p+" n="+n+" data.length="+data.length);
			return $this->data[$this->p+$i-1];
	    }

		public function LT($i) {
			return $this->LA($i);
		}

		/** Return the current input symbol index 0..n where n indicates the
	     *  last symbol has been read.  The index is the index of char to
		 *  be returned from LA(1).
	     */
	    public function index() {
	        return $this->p;
	    }

		public function size() {
			return $this->n;
		}

		public function mark() {
	        if ( $this->markers == null) {
	            $this->markers = array();
	            $this->markers[] = null; // depth 0 means no backtracking, leave blank
	        }
	        $this->markDepth++;
			$state = null;
			if ($this->markDepth>=sizeof($this->markers)) {
				$state = new CharStreamState();
				$this->markers[] = $state;
			}
			else {
				$state = $this->markers[$this->markDepth];
			}
			$state->p = $this->p;
			$state->line = $this->line;
			$state->charPositionInLine = $this->charPositionInLine;
			$this->lastMarker = $this->markDepth;
			return $this->markDepth;
	    }

	    public function rewind($m=null) {
			if($m===null){
				$this->rewind((int)$this->lastMarker);
			}else{
				$state = $this->markers[$m];
				// restore stream state
				$this->seek($state->p);
				$this->line = $state->line;
				$this->charPositionInLine = $state->charPositionInLine;
				$this->release($m);
			}
		}

		public function release($marker) {
			// unwind any other markers made after m and release m
			$this->markDepth = $marker;
			// release this marker
			$this->markDepth--;
		}

		/** consume() ahead until p==index; can't just set p=index as we must
		 *  update line and charPositionInLine.
		 */
		public function seek($index) {
			if ( $index<=$this->p ) {
				$this->p = $index; // just jump; don't update stream state (line, ...)
				return;
			}
			// seek forward, consume until p hits index
			while ( $this->p<$index ) {
				$this->consume();
			}
		}

		public function substring($start, $stop) {
			return implode(array_map('chr', array_slice($this->data, $start, $stop-$start+1)));
		}

		public function getLine() {
			return $this->line;
		}

		public function getCharPositionInLine() {
			return $this->charPositionInLine;
		}

		public function setLine($line) {
			$this->line = $line;
		}

		public function setCharPositionInLine($pos) {
			$this->charPositionInLine = $pos;
		}

		public function getSourceName() {
			return $this->name;
		}
	}

?>