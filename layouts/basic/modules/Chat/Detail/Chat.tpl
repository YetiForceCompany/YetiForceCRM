{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Chat-Detail-Chat -->
	<div class="w-100 text-center {if $CHAT->isRoomExists()}hide {/if} js-container-button">
		<button type="button" class="btn btn-success js-create-chatroom m-auto" data-js="click">
			<span class="fa fa-plus mr-2" title="{\App\Language::translate('LBL_CREATE', $MODULE_NAME)}"></span>
			{\App\Language::translate('LBL_CREATE_CHAT_ROOM')}
		</button>
	</div>
	<div class="{if !$CHAT->isRoomExists()}hide {/if} js-container-chat">
		{include file=\App\Layout::getTemplatePath('Chat.tpl', 'Chat')}
	</div>
	<!-- /tpl-Chat-Detail-Chat -->
{/strip}