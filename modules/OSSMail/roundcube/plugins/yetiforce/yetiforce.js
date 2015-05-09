window.rcmail && rcmail.addEventListener('init', function (evt) {
	rcmail.env.compose_commands.push('yetiforce.addFilesToMail')
	rcmail.register_command('yetiforce.addFilesToMail', function (data) {
		var ts = new Date().getTime(),
				frame_name = 'rcmupload' + ts,
				frame = rcmail.async_upload_form_frame(frame_name);
		data._uploadid = ts;
		jQuery.ajax({
			url: "?_task=mail&_action=plugin.yetiforce.addFilesToMail&_id=" + rcmail.env.compose_id,
			type: "POST",
			data: data,
			success: function (data) {
				var doc = frame[0].contentWindow.document;
				var body = $('html', doc);
				body.html(data);
			}
		});
	}, true);
});
