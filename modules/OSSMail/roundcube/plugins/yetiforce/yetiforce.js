window.rcmail && rcmail.addEventListener('init', function (evt) {
	rcmail.env.compose_commands.push('yetiforce.addFilesToMail');
	rcmail.env.compose_commands.push('yetiforce.addFilesFromCRM');
	
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
	rcmail.register_command('yetiforce.addFilesFromCRM', function (data) {
		if(typeof parent.app != 'undefined'){
			var params = {
				module: 'Documents',
				src_module: 'Documents',
				src_field: '',
				src_record: '',
				multi_select: true,
				url: rcmail.env.site_URL + 'index.php?'
			};
			var sourceFieldElement = $(this);
			var prePopupOpenEvent = jQuery.Event(parent.Vtiger_Edit_Js.preReferencePopUpOpenEvent);
			sourceFieldElement.trigger(prePopupOpenEvent);
			var data = {};
			var popupInstance = parent.Vtiger_Popup_Js.getInstance();
			parent.show(params, function(data) {
				var responseData = JSON.parse(data);
				var ids = [];
				for (var id in responseData) {
					ids.push(id);
				}	
				rcmail.command('yetiforce.addFilesToMail', {ids: ids, _uploadid: new Date().getTime()});
			});
		}
	}, true);
});
