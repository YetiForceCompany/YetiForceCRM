{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Base-Detail-Chat js-chat-detail" data-chat-room-id="{$RECORD_MODEL->getId()}"
		 data-timer="{AppConfig::module('Chat', 'REFRESH_TIME')}"
		 data-js="container">
		<div class="{if $CHAT->isRoomExists()}hide {/if}js-container-button">
			<button type="button" class="btn btn-success js-create-chatroom" data-js="click">
				<span class="fas fa-plus mr-2" title="{\App\Language::translate('LBL_CREATE', $MODULE)}"></span>
				{\App\Language::translate('LBL_CREATE_CHAT_ROOM')}
			</button>
		</div>
		<div class="{if !$CHAT->isRoomExists()}hide {/if}js-container-items">
			<div class="js-chat-items js-chat-room-{$RECORD_MODEL->getId()} pr-2" data-js="html">
				{include file=\App\Layout::getTemplatePath('Items.tpl', 'Chat') CHAT_ENTRIES=$CHAT->getEntries($CHAT_ID)}
			</div>
			{include file=\App\Layout::getTemplatePath('Detail/ChatFooter.tpl')}
		</div>
	</div>
{/strip}
