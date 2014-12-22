$(document).ready(function(){
	var vars = {};
	var parts = document.body.baseURI.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
		vars[key] = value;
	});
	$( "#message-oss-parameters-uid" ).text(vars['_uid']);
	$( "#message-oss-parameters-folder" ).text(vars['_mbox']);
})