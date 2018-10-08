{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Base-Detail-Chat js-chat-container" data-js="container" data-chat-room-id="{$RECORD->getId()}">
		<div class="{if $CHAT->isCharRoomExists()}hide{/if} js-container-button">
			<button type="button" class="js-create-chatroom">
				LBL_CREATE_CHAT_ROOM
			</button>
		</div>
		<div class="{if !$CHAT->isCharRoomExists()}hide{/if} js-container-items">
			<div class="js-chat-items js-chat-room-{$RECORD->getId()}" data-js="html">
				{include file=\App\Layout::getTemplatePath('Items.tpl', 'Chat') CHAT_ENTRIES=$CHAT->getEntries($CHAT_ID)}
			</div>
			{include file=\App\Layout::getTemplatePath('Detail/ChatFooter.tpl')}
		</div>
	</div>
{/strip}
