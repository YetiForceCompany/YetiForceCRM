{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Chat-Chat -->
	<div class="row">
		<input type="text" class="form-control message js-chat-message"{' '} autocomplete="off"{' '}
			   placeholder="{\App\Language::translate('LBL_SEARCH')}" data-js="keydown"/>
	</div>
	<div class="d-flex flex-column" style="min-height: calc(100vh - 260px);">
		<div class="row d-flex flex-grow-1">
			<div class="col-10 js-chat_content"
					{*data-current-room="{\App\Purifier::encodeHtml(\App\Json::encode($CURRENT_ROOM))}"*}
				 data-current-room-type="{$CURRENT_ROOM['roomType']}"
				 data-current-record-id="{$CURRENT_ROOM['recordId']}"
				 data-message-timer="{AppConfig::module('Chat', 'REFRESH_TIME')}"
				 data-room-timer="{AppConfig::module('Chat', 'REFRESH_TIME')}"
				 data-js="append">{include file=\App\Layout::getTemplatePath('Entries.tpl', 'Chat')}</div>
			<div class="col-2 bg-color-grey-50 h-100 js-users">USERS</div>
		</div>
	</div>
	<div class="row">
		{include file=\App\Layout::getTemplatePath('ChatInput.tpl', 'Chat')}
	</div>
	<!-- /tpl-Chat-Chat -->
{/strip}