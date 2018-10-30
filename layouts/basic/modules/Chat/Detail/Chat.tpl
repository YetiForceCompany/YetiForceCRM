{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{*<<<<<<< HEAD*}
	<div class="tpl-Chat-Detail-Chat {if !$CHAT->isRoomExists()}hide {/if} js-container-chat">
		{*=======
			<!-- tpl-Chat-Detail-Chat -->
			<div class="w-100 text-center{if $CHAT->isRoomExists()} hide{/if} js-container-button">
				<button type="button" class="btn btn-success js-create-chatroom m-auto" data-js="click">
					<span class="fa fa-plus mr-2"
						  title="{\App\Language::translate('LBL_CREATE_CHAT_ROOM', $MODULE_NAME)}"></span>
					{\App\Language::translate('LBL_CREATE_CHAT_ROOM', $MODULE_NAME)}
				</button>
			</div>*}
		{*<div class="{if !$CHAT->isRoomExists()}hide {/if} js-container-chat o-chat__detail">
			<div class="w-100 text-center mb-2">
				<button type="button"
						class="btn btn-danger{if !$CHAT->isAssigned()} hide{/if} js-remove-from-favorites m-auto"
						data-js="click">
					<span class="fa fa-minus mr-2"
						  title="{\App\Language::translate('LBL_REMOVE_FROM_FAVORITES', $MODULE_NAME)}"></span>
					{\App\Language::translate('LBL_REMOVE_FROM_FAVORITES', $MODULE_NAME)}
				</button>
				<button type="button"
						class="btn btn-success{if $CHAT->isAssigned()} hide{/if} js-add-from-favorites m-auto"
						data-js="click">
				<span class="fa fa-plus mr-2"
					  title="{\App\Language::translate('LBL_ADD_FROM_FAVORITES', $MODULE_NAME)}"></span>
					{\App\Language::translate('LBL_ADD_FROM_FAVORITES', $MODULE_NAME)}
				</button>
			</div>*}
		{*>>>>>>> developer*}
		{include file=\App\Layout::getTemplatePath('Chat.tpl', 'Chat')}
	</div>
{/strip}
