<?php
/**
 * Text Value
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * 
 * Value Name: TEXT
 * 
 * Purpose This value type is used to identify values that contain human
 * readable text.
 * 
 * Formal Definition: The character sets supported by this revision of
 * iCalendar are UTF-8 and US ASCII thereof. The applicability to other
 * character sets is for future work. The value type is defined by the
 * following notation.
 * 
 *  text       = *(TSAFE-CHAR / ":" / DQUOTE / ESCAPED-CHAR)
 *  ; Folded according to description above
 * 
 *  ESCAPED-CHAR = "\\" / "\;" / "\," / "\N" / "\n")
 *     ; \\ encodes \, \N or \n encodes newline
 *     ; \; encodes ;, \, encodes ,
 * 
 *  TSAFE-CHAR = %x20-21 / %x23-2B / %x2D-39 / %x3C-5B
 *               %x5D-7E / NON-US-ASCII
 *     ; Any character except CTLs not needed by the current
 *     ; character set, DQUOTE, ";", ":", "\", ","
 * 
 *  Note: Certain other character sets may require modification of the
 *  above definitions, but this is beyond the scope of this document.
 * 
 * Description: If the property permits, multiple "text" values are
 * specified by a COMMA character (US-ASCII decimal 44) separated list
 * of values.
 * 
 * The language in which the text is represented can be controlled by
 * the "LANGUAGE" property parameter.
 * 
 * An intentional formatted text line break MUST only be included in a
 * "TEXT" property value by representing the line break with the
 * character sequence of BACKSLASH (US-ASCII decimal 92), followed by a
 * LATIN SMALL LETTER N (US-ASCII decimal 110) or a LATIN CAPITAL LETTER
 * N (US-ASCII decimal 78), that is "\n" or "\N".
 * 
 * The "TEXT" property values may also contain special characters that
 * are used to signify delimiters, such as a COMMA character for lists
 * of values or a SEMICOLON character for structured values. In order to
 * support the inclusion of these special characters in "TEXT" property
 * values, they MUST be escaped with a BACKSLASH character. A BACKSLASH
 * character (US-ASCII decimal 92) in a "TEXT" property value MUST be
 * escaped with another BACKSLASH character. A COMMA character in a
 * "TEXT" property value MUST be escaped with a BACKSLASH character
 * (US-ASCII decimal 92). A SEMICOLON character in a "TEXT" property
 * value MUST be escaped with a BACKSLASH character (US-ASCII decimal
 * 92).  However, a COLON character in a "TEXT" property value SHALL NOT
 * be escaped with a BACKSLASH character.Example: A multiple line value
 * of:
 * 
 *  Project XYZ Final Review
 *  Conference Room - 3B
 *  Come Prepared.
 * 
 * would be represented as:
 * 
 *  Project XYZ Final Review\nConference Room - 3B\nCome Prepared.
 */
class qCal_Value_Text extends qCal_Value {

	/**
	 * @todo: implement this
	 */
	protected function doCast($value) {
	
		return $value;
	
	}

}