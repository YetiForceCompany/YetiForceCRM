{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Chat-Chat -->
	<div class="row">
		<div class="col-9">
			<div class="row">
				<input type="text" class="form-control message js-search-message"{' '} autocomplete="off"{' '}
					   placeholder="{\App\Language::translate('LBL_SEARCH_MESSAGE', $MODULE_NAME)}" data-js="keydown"/>
			</div>
			<div class="d-flex flex-column" style="min-height: calc(100vh - 260px);">
				<div class="row d-flex flex-grow-1">
					<div class="col-10 js-chat_content"
						 data-current-room-type="{$CURRENT_ROOM['roomType']}"
						 data-current-record-id="{$CURRENT_ROOM['recordId']}"
						 data-message-timer="{AppConfig::module('Chat', 'REFRESH_TIME')}"
						 data-room-timer="{AppConfig::module('Chat', 'REFRESH_TIME')}"
						 data-current-room="{\App\Purifier::encodeHtml(\App\Json::encode($CURRENT_ROOM))}"
						 data-js="append">
						{include file=\App\Layout::getTemplatePath('Entries.tpl', 'Chat')}
					</div>
				</div>
			</div>
			<div class="row">
				<textarea class="form-control message js-chat-message" autocomplete="off"
						  placeholder="{\App\Language::translate('LBL_MESSAGE', $MODULE_NAME)}" data-js="keydown">
				</textarea>
				<button type="button" class="js-btn-send" data-js="click">SEND</button>
			</div>
		</div>
		<div class="col-3">
			<div>
				<input type="text" class="form-control message js-search-participants" autocomplete="off"
					   placeholder="{\App\Language::translate('LBL_SEARCH_PARTICIPANTS', $MODULE_NAME)}"
					   data-js="keydown"/>
			</div>
			<h5>{\App\Language::translate('LBL_PARTICIPANTS', $MODULE_NAME)}</h5>
			<div class="js-participants-list" data-js="container">

			</div>
		</div>
	</div>
	<!-- /tpl-Chat-Chat -->
{/strip}