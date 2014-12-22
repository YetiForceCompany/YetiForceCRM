<?php
/**
 * Geographic Position Property
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * 
 * RFC 2445 Definition
 * 
 * Property Name: GEO
 * 
 * Purpose: This property specifies information related to the global
 * position for the activity specified by a calendar component.
 * 
 * Value Type: FLOAT. The value MUST be two SEMICOLON separated FLOAT
 * values.
 * 
 * Property Parameters: Non-standard property parameters can be
 * specified on this property.
 * 
 * Conformance: This property can be specified in  "VEVENT" or "VTODO"
 * calendar components.
 * 
 * Description: The property value specifies latitude and longitude, in
 * that order (i.e., "LAT LON" ordering). The longitude represents the
 * location east or west of the prime meridian as a positive or negative
 * real number, respectively. The longitude and latitude values MAY be
 * specified up to six decimal places, which will allow for accuracy to
 * within one meter of geographical position. Receiving applications
 * MUST accept values of this precision and MAY truncate values of
 * greater precision.
 * 
 * Values for latitude and longitude shall be expressed as decimal
 * fractions of degrees. Whole degrees of latitude shall be represented
 * by a two-digit decimal number ranging from 0 through 90. Whole
 * degrees of longitude shall be represented by a decimal number ranging
 * from 0 through 180. When a decimal fraction of a degree is specified,
 * it shall be separated from the whole number of degrees by a decimal
 * point.
 * 
 * Latitudes north of the equator shall be specified by a plus sign (+),
 * or by the absence of a minus sign (-), preceding the digits
 * designating degrees. Latitudes south of the Equator shall be
 * designated by a minus sign (-) preceding the digits designating
 * degrees. A point on the Equator shall be assigned to the Northern
 * Hemisphere.
 * 
 * Longitudes east of the prime meridian shall be specified by a plus
 * sign (+), or by the absence of a minus sign (-), preceding the digits
 * designating degrees. Longitudes west of the meridian shall be
 * designated by minus sign (-) preceding the digits designating
 * degrees. A point on the prime meridian shall be assigned to the
 * Eastern Hemisphere. A point on the 180th meridian shall be assigned
 * to the Western Hemisphere. One exception to this last convention is
 * permitted. For the special condition of describing a band of latitude
 * around the earth, the East Bounding Coordinate data element shall be
 * assigned the value +180 (180) degrees.
 * 
 * Any spatial address with a latitude of +90 (90) or -90 degrees will
 * specify the position at the North or South Pole, respectively. The
 * component for longitude may have any legal value.
 * 
 * With the exception of the special condition described above, this
 * form is specified in Department of Commerce, 1986, Representation of
 * geographic point locations for information interchange (Federal
 * Information Processing Standard 70-1):  Washington,  Department of
 * Commerce, National Institute of Standards and Technology.
 * 
 * The simple formula for converting degrees-minutes-seconds into
 * decimal degrees is:
 * 
 *   decimal = degrees + minutes/60 + seconds/3600.
 * 
 * Format Definition: The property is defined by the following notation:
 * 
 *   geo        = "GEO" geoparam ":" geovalue CRLF
 * 
 *   geoparam   = *(";" xparam)
 * 
 *   geovalue   = float ";" float
 *   ;Latitude and Longitude components
 * 
 * Example: The following is an example of this property:
 * 
 *   GEO:37.386013;-122.082932
 */
class qCal_Property_Geo extends qCal_Property {

	protected $type = 'FLOAT';
	protected $allowedComponents = array('VEVENT', 'VTODO');

}