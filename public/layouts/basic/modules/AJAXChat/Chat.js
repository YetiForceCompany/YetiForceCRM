/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

var AJAXChat_Chat_Js = {
	interval: false,
	blinkingIcon: function () {
		clearInterval(this.interval);
		this.interval = setInterval(function () {
			$('.headerLinkChat').effect("pulsate", 1500);
		}, 1500);
	},
	stopBlinkingIcon: function(){
		clearInterval(this.interval);
		app.cacheClear('unreadCharMessage' + app.getMainParams('current_user_id'));
	},
	registerUnreadMessage: function(){
		this.blinkingIcon();
		app.cacheSet('unreadCharMessage' + app.getMainParams('current_user_id'), true);
	},
	checkUnreadMessage: function(){
		var unreadMessage = app.cacheGet('unreadCharMessage' + app.getMainParams('current_user_id'));
		if(unreadMessage) {
			this.blinkingIcon();
		}
	}
};

jQuery(function () {
	var params = {
		'view': 'Index',
		'module': 'AJAXChat'
	};
	AppConnector.request(params).then(
			function (data) {
				jQuery('#page').append(data);
				var container = $("#AJAXChatBlock");
				var icon = $('.headerLinkChat');
				AJAXChat_Chat_Js.checkUnreadMessage();
				icon.click(function () {
					AJAXChat_Chat_Js.stopBlinkingIcon();
					$('.actionMenu').removeClass('actionMenuOn');// hide action menu
					if (container.hasClass('chat-closed')) {
						container.addClass('chat-opened').removeClass('chat-closed');
					} else {
						container.addClass('chat-closed').removeClass('chat-opened');
					}
				});
				$(document).mouseup(function (e)
				{
					if (container.hasClass('chat-opened') &&
						!container.is(e.target) && !icon.is(e.target) &&// if the target of the click isn't the container...
						container.has(e.target).length === 0 && icon.has(e.target).length === 0) // ... nor a descendant of the container
					{
						container.addClass('chat-closed').removeClass('chat-opened');
					}
				});
			}
	);
});
