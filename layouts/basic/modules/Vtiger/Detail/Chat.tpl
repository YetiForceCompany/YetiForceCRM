{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Base-Detail-Chat js-chat-container" data-js="container">
		{if !$CHAT->isCharRoomExists()}
			<button type="button" class="js-create-chatroom">
				LBL_CREATE_CHAT_ROOM
			</button>
		{else}
			<div class="js-chat-items" data-js="html">
				{include file=\App\Layout::getTemplatePath('Items.tpl', 'Chat') CHAT_ENTRIES=$CHAT_ENTRIES}
			</div>
			<div class="row mx-0 d-block">
				{*{\App\Language::translate('LBL_MESSAGE', 'Notification')}*}
				<input class="form-control message js-chat-message" type="text"{' '}
					   placeholder="{\App\Language::translate('LBL_MESSAGE', 'Notification')}" autocomplete="off"/>
				<button type="button" class="btn btn-primary js-add-msg float-right mt-2" data-js="click">
					{\App\Language::translate('LBL_SEND_MESSAGE')}
				</button>
			</div>
		{/if}
	</div>
{/strip}
