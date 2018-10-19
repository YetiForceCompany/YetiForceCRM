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
		<div class="row">
			<input type="text" class="form-control message js-chat-message"{' '} autocomplete="off"{' '}
				   placeholder="{\App\Language::translate('LBL_SEARCH')}" data-js="keydown"/>
		</div>
		<div class="d-flex flex-column" style="min-height: calc(100vh - 260px);">
			<div class="row d-flex flex-grow-1">
				<div class="col-10 js-chat_content"
					 data-current-room="{\App\Purifier::encodeHtml(\App\Json::encode($CURRENT_ROOM))}"
					 data-js="append">{include file=\App\Layout::getTemplatePath('Entries.tpl', 'Chat')}</div>
				<div class="col-2 bg-color-grey-50 h-100 js-users">USERS</div>
			</div>
		</div>
		<div class="row">
			{include file=\App\Layout::getTemplatePath('ChatInput.tpl', 'Chat')}
		</div>
	</div>
	<!-- /tpl-Chat-Detail-Chat -->
{/strip}