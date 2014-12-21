<?php
/**
 * Alarm Component
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * 
 * RFC 2445 Definition
 *
 * Purpose: Provide a grouping of component properties that define an
 * alarm.
 * 
 * Formal Definition: A "VALARM" calendar component is defined by the
 * following notation:
 * 
 *       alarmc     = "BEGIN" ":" "VALARM" CRLF
 *                    (audioprop / dispprop / emailprop / procprop)
 *                    "END" ":" "VALARM" CRLF
 * 
 *  audioprop  = 2*(
 * 
 *             ; 'action' and 'trigger' are both REQUIRED,
 *             ; but MUST NOT occur more than once
 * 
 *             action / trigger /
 * 
 *             ; 'duration' and 'repeat' are both optional,
 *             ; and MUST NOT occur more than once each,
 *             ; but if one occurs, so MUST the other
 * 
 *             duration / repeat /
 * 
 *             ; the following is optional,
 *             ; but MUST NOT occur more than once
 * 
 *             attach /
 * 
 *             ; the following is optional,
 *             ; and MAY occur more than once
 * 
 *             x-prop
 * 
 *             )
 * 
 *  dispprop   = 3*(
 * 
 *             ; the following are all REQUIRED,
 *             ; but MUST NOT occur more than once
 * 
 *             action / description / trigger /
 * 
 *             ; 'duration' and 'repeat' are both optional,
 *             ; and MUST NOT occur more than once each,
 *             ; but if one occurs, so MUST the other
 * 
 *             duration / repeat /
 * 
 *             ; the following is optional,
 *             ; and MAY occur more than once
 * 
 *             *x-prop
 * 
 *             )
 * 
 *  emailprop  = 5*(
 * 
 *             ; the following are all REQUIRED,
 *             ; but MUST NOT occur more than once
 * 
 *             action / description / trigger / summary
 * 
 *             ; the following is REQUIRED,
 *             ; and MAY occur more than once
 * 
 *             attendee /
 * 
 *             ; 'duration' and 'repeat' are both optional,
 *             ; and MUST NOT occur more than once each,
 *             ; but if one occurs, so MUST the other
 * 
 *             duration / repeat /
 * 
 *             ; the following are optional,
 *             ; and MAY occur more than once
 * 
 *             attach / x-prop
 * 
 *             )
 * 
 *  procprop   = 3*(
 * 
 *             ; the following are all REQUIRED,
 *             ; but MUST NOT occur more than once
 * 
 *             action / attach / trigger /
 * 
 *             ; 'duration' and 'repeat' are both optional,
 *             ; and MUST NOT occur more than once each,
 *             ; but if one occurs, so MUST the other
 * 
 *             duration / repeat /
 * 
 *             ; 'description' is optional,
 *             ; and MUST NOT occur more than once
 * 
 *             description /
 * 
 *             ; the following is optional,
 *             ; and MAY occur more than once
 * 
 *             x-prop
 * 
 *             )
 * 
 * Description: A "VALARM" calendar component is a grouping of component
 * properties that is a reminder or alarm for an event or a to-do. For
 * example, it may be used to define a reminder for a pending event or
 * an overdue to-do.
 * 
 * The "VALARM" calendar component MUST include the "ACTION" and
 * "TRIGGER" properties. The "ACTION" property further constrains the
 * "VALARM" calendar component in the following ways:
 * 
 * When the action is "AUDIO", the alarm can also include one and only
 * one "ATTACH" property, which MUST point to a sound resource, which is
 * rendered when the alarm is triggered.
 * 
 * When the action is "DISPLAY", the alarm MUST also include a
 * "DESCRIPTION" property, which contains the text to be displayed when
 * the alarm is triggered.
 * 
 * When the action is "EMAIL", the alarm MUST include a "DESCRIPTION"
 * property, which contains the text to be used as the message body, a
 * "SUMMARY" property, which contains the text to be used as the message
 * subject, and one or more "ATTENDEE" properties, which contain the
 * email address of attendees to receive the message. It can also
 * include one or more "ATTACH" properties, which are intended to be
 * sent as message attachments. When the alarm is triggered, the email
 * message is sent.
 * 
 * When the action is "PROCEDURE", the alarm MUST include one and only
 * one "ATTACH" property, which MUST point to a procedure resource,
 * which is invoked when the alarm is triggered.
 * 
 * The "VALARM" calendar component MUST only appear within either a
 * "VEVENT" or "VTODO" calendar component. "VALARM" calendar components
 * cannot be nested. Multiple mutually independent "VALARM" calendar
 * components can be specified for a single "VEVENT" or "VTODO" calendar
 * component.
 * 
 * The "TRIGGER" property specifies when the alarm will be triggered.
 * The "TRIGGER" property specifies a duration prior to the start of an
 * event or a to-do. The "TRIGGER" edge may be explicitly set to be
 * relative to the "START" or "END" of the event or to-do with the
 * "RELATED" parameter of the "TRIGGER" property. The "TRIGGER" property
 * value type can alternatively be set to an absolute calendar date and
 * time of day value.
 * 
 * In an alarm set to trigger on the "START" of an event or to-do, the
 * "DTSTART" property MUST be present in the associated event or to-do.
 * In an alarm in a "VEVENT" calendar component set to trigger on the
 * "END" of the event, either the "DTEND" property MUST be present, or
 * the "DTSTART" and "DURATION" properties MUST both be present. In an
 * alarm in a "VTODO" calendar component set to trigger on the "END" of
 * the to-do, either the "DUE" property MUST be present, or the
 * "DTSTART" and "DURATION" properties MUST both be present.
 * 
 * The alarm can be defined such that it triggers repeatedly. A
 * definition of an alarm with a repeating trigger MUST include both the
 * "DURATION" and "REPEAT" properties. The "DURATION" property specifies
 * the delay period, after which the alarm will repeat. The "REPEAT"
 * property specifies the number of additional repetitions that the
 * alarm will triggered. This repitition count is in addition to the
 * initial triggering of the alarm. Both of these properties MUST be
 * present in order to specify a repeating alarm. If one of these two
 * properties is absent, then the alarm will not repeat beyond the
 * initial trigger.
 * 
 * The "ACTION" property is used within the "VALARM" calendar component
 * to specify the type of action invoked when the alarm is triggered.
 * The "VALARM" properties provide enough information for a specific
 * action to be invoked. It is typically the responsibility of a
 * "Calendar User Agent" (CUA) to deliver the alarm in the specified
 * fashion. An "ACTION" property value of AUDIO specifies an alarm that
 * causes a sound to be played to alert the user; DISPLAY specifies an
 * alarm that causes a text message to be displayed to the user; EMAIL
 * specifies an alarm that causes an electronic email message to be
 * delivered to one or more email addresses; and PROCEDURE specifies an
 * alarm that causes a procedure to be executed. The "ACTION" property
 * MUST specify one and only one of these values.
 * 
 * In an AUDIO alarm, if the optional "ATTACH" property is included, it
 * MUST specify an audio sound resource. The intention is that the sound
 * will be played as the alarm effect. If an "ATTACH" property is
 * specified that does not refer to a sound resource, or if the
 * specified sound resource cannot be rendered (because its format is
 * unsupported, or because it cannot be retrieved), then the CUA or
 * other entity responsible for playing the sound may choose a fallback
 * action, such as playing a built-in default sound, or playing no sound
 * at all.
 * 
 * In a DISPLAY alarm, the intended alarm effect is for the text value
 * of the "DESCRIPTION" property to be displayed to the user.
 * 
 * In an EMAIL alarm, the intended alarm effect is for an email message
 * to be composed and delivered to all the addresses specified by the
 * "ATTENDEE" properties in the "VALARM" calendar component. The
 * "DESCRIPTION" property of the "VALARM" calendar component MUST be
 * used as the body text of the message, and the "SUMMARY" property MUST
 * be used as the subject text. Any "ATTACH" properties in the "VALARM"
 * calendar component SHOULD be sent as attachments to the message.
 * 
 * In a PROCEDURE alarm, the "ATTACH" property in the "VALARM" calendar
 * component MUST specify a procedure or program that is intended to be
 * invoked as the alarm effect. If the procedure or program is in a
 * format that cannot be rendered, then no procedure alarm will be
 * invoked. If the "DESCRIPTION" property is present, its value
 * specifies the argument string to be passed to the procedure or
 * program. "Calendar User Agents" that receive an iCalendar object with
 * this category of alarm, can disable or allow the "Calendar User" to
 * disable, or otherwise ignore this type of alarm. While a very useful
 * alarm capability, the PROCEDURE type of alarm SHOULD be treated by
 * the "Calendar User Agent" as a potential security risk.
 * 
 * Example: The following example is for a "VALARM" calendar component
 * that specifies an audio alarm that will sound at a precise time and
 * repeat 4 more times at 15 minute intervals:
 * 
 *  BEGIN:VALARM
 *  TRIGGER;VALUE=DATE-TIME:19970317T133000Z
 *  REPEAT:4
 *  DURATION:PT15M
 *  ACTION:AUDIO
 *  ATTACH;FMTTYPE=audio/basic:ftp://host.com/pub/sounds/bell-01.aud
 *  END:VALARM
 * 
 * The following example is for a "VALARM" calendar component that
 * specifies a display alarm that will trigger 30 minutes before the
 * scheduled start of the event or the due date/time of the to-do it is
 * associated with and will repeat 2 more times at 15 minute intervals:
 * 
 *  BEGIN:VALARM
 *  TRIGGER:-PT30M
 *  REPEAT:2
 *  DURATION:PT15M
 *  ACTION:DISPLAY
 *  DESCRIPTION:Breakfast meeting with executive\n
 *   team at 8:30 AM EST.
 *  END:VALARM
 * 
 * The following example is for a "VALARM" calendar component that
 * specifies an email alarm that will trigger 2 days before the
 * scheduled due date/time of a to-do it is associated with. It does not
 * repeat. The email has a subject, body and attachment link.
 * 
 *  BEGIN:VALARM
 *  TRIGGER:-P2D
 *  ACTION:EMAIL
 *  ATTENDEE:MAILTO:john_doe@host.com
 *  SUMMARY:*** REMINDER: SEND AGENDA FOR WEEKLY STAFF MEETING ***
 *  DESCRIPTION:A draft agenda needs to be sent out to the attendees
 *    to the weekly managers meeting (MGR-LIST). Attached is a
 *    pointer the document template for the agenda file.
 *  ATTACH;FMTTYPE=application/binary:http://host.com/templates/agen
 *   da.doc
 *  END:VALARM
 * 
 * The following example is for a "VALARM" calendar component that
 * specifies a procedural alarm that will trigger at a precise date/time
 * and will repeat 23 more times at one hour intervals. The alarm will
 * invoke a procedure file.
 * 
 *  BEGIN:VALARM
 *  TRIGGER;VALUE=DATE-TIME:19980101T050000Z
 *  REPEAT:23
 *  DURATION:PT1H
 *  ACTION:PROCEDURE
 *  ATTACH;FMTTYPE=application/binary:ftp://host.com/novo-
 *   procs/felizano.exe
 *  END:VALARM
 */
class qCal_Component_Valarm extends qCal_Component {

	protected $name = "VALARM";
	protected $allowedComponents = array('VEVENT','VTODO');
	protected $requiredProperties = array('ACTION', 'TRIGGER');
	protected function doValidation() {
	
		$action = $this->getAction();
		switch(strtoupper($action->getValue())) {
			case "AUDIO":
				// action, trigger (already covered by parent constructor)
				// attach can only occur once
				$attach = $this->getProperty('ATTACH');
				if (count($attach) > 1) {
					throw new qCal_Exception_InvalidProperty('VALARM audio component can contain one and only one ATTACH property');
				}
				break;
			case "DISPLAY":
				// action, trigger, description 
				if (!$this->hasProperty('DESCRIPTION')) {
					throw new qCal_Exception_MissingProperty("DISPLAY VALARM component requires DESCRIPTION property");
				}
				break;
			case "EMAIL":
				// action, description, trigger, summary
				if (!$this->hasProperty('DESCRIPTION')) {
					throw new qCal_Exception_MissingProperty("EMAIL VALARM component requires DESCRIPTION property");
				}
				if (!$this->hasProperty('SUMMARY')) {
					throw new qCal_Exception_MissingProperty("EMAIL VALARM component requires SUMMARY property");
				}
				break;
			case "PROCEDURE":
				// action, attach, trigger
				$attach = $this->getProperty('ATTACH');
				if (count($attach) > 1) {
					throw new qCal_Exception_InvalidProperty('VALARM procedure component can contain one and only one ATTACH property');
				}
				if (count($attach) < 1) {
					throw new qCal_Exception_MissingProperty("PROCEDURE VALARM component requires ATTACH property");
				}
				break;
		}
		if ($this->hasProperty('DURATION')) {
			if (!$this->hasProperty('REPEAT')) {
				throw new qCal_Exception_MissingProperty("VALARM component with a DURATION property requires a REPEAT property");
			}
		}
		if ($this->hasProperty('REPEAT')) {
			if (!$this->hasProperty('DURATION')) {
				throw new qCal_Exception_MissingProperty("VALARM component with a REPEAT property requires a DURATION property");
			}
		}
	
	}

}