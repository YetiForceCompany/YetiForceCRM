/**
 *
 * Per identity smtp settings
 *
 * Description
 *
 * @version 0.1
 * @author elm@skweez.net, ritze@skweez.net, mks@skweez.net
 * @url skweez.net
 *
 * MIT License
 *
 **/

function identity_smtp_toggle_standard_server() {
	var checkbox = $('input[name=_smtp_standard]');
	if (checkbox.is(':checked')) {
		$('.identity_smtp_form').prop('disabled', true);
	} else {
		$('.identity_smtp_form').removeProp('disabled');
	}
}
