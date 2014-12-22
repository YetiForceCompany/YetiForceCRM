jQuery(function() {
	var params = {
		'view' : 'Index',
		'module' : 'AJAXChat'
	}
	AppConnector.request(params).then(
		function(data){
			jQuery('#page').append(data);
			$('.headerLinksAJAXChat .ChatIcon').toggle(function() {
				$('#AJAXChatBlock').animate({'right':'0px'});
			}, function() {
				$('#AJAXChatBlock').animate({'right':'-603px'});
			});
		}
	);
});