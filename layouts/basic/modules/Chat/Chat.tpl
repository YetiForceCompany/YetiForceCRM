{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Chat-Chat -->
	{function ITEM_USER CLASS=''}
		<li class="js-item-user c-chat__user-item {$CLASS} border-bottom pb-2 mb-2" data-user-id="{$USER['user_id']}"
			data-js="data">
			<div class="row px-2">
				<div class="p-1 chat_img-container c-chat__author text-center">
					{assign var=IMAGE value=$USER['image']}
					{assign var=IS_IMAGE value=isset($IMAGE['url'])}
					<img src="{if $IS_IMAGE}{$IMAGE['url']}{/if}" class="{if !$IS_IMAGE} hide{/if}"
						 alt="{$USER['user_name']}"
						 title="{$USER['user_name']}"/>
					<span class="fas fa-user u-font-size-50px userImage {if $IS_IMAGE} hide{/if}"></span>
				</div>
				<div class="col-9 px-4">
					<div class="js-user-name u-font-size-13px">{$USER['user_name']}</div>
					<div class="js-role c-chat__user-role font-weight-bold color-blue-600 mb-2">{$USER['role_name']}</div>
					<div class="js-message c-chat__user-message text-truncate">{$USER['message']}</div>
				</div>
			</div>
		</li>
	{/function}
	<div class="row o-chat__view-container">
		<div class="col-9">
			<div class="row px-2">
				<input type="text"
					   class="form-control u-font-size-13px js-search-message border-bottom rounded-0 c-chat__form-control"{' '}
					   autocomplete="off"{' '}
					   placeholder="{\App\Language::translate('LBL_SEARCH_MESSAGE', $MODULE_NAME)}" data-js="keydown"/>
				<span class="fas fa-search c-chat__icon-search"></span>
				<button type="button" class="btn btn-danger hide js-search-cancel" data-js="click">X</button>
			</div>
			<div class="d-flex flex-column js-chat-main-content c-chat__scrollbar js-scrollbar border-bottom"
				 data-js=”container|perfectscrollbar”>
				<div class="d-flex flex-grow-1">
					<div class="col-12 js-chat_content h-100 w-100 mb-4"
						 data-current-room-type="{$CURRENT_ROOM['roomType']}"
						 data-current-record-id="{$CURRENT_ROOM['recordId']}"
						 data-message-timer="{AppConfig::module('Chat', 'REFRESH_TIME')}"
						 data-room-timer="{AppConfig::module('Chat', 'REFRESH_TIME')}"
						 data-max-length-message="{AppConfig::module('Chat', 'MAX_LENGTH_MESSAGE')}"
						 data-js="append">
						{include file=\App\Layout::getTemplatePath('Entries.tpl', 'Chat')}
					</div>
				</div>
			</div>
			<div class="row">
				<div class="input-group">
					<textarea
							class="form-control noresize u-font-size-13px js-chat-message rounded-0 c-chat__form-control"
							rows="2"
							autocomplete="off"
							placeholder="{\App\Language::translate('LBL_MESSAGE', $MODULE_NAME)}"
							data-js="keydown">
					</textarea>
				</div>
				<button type="button" class="btn btn-primary js-btn-send c-chat__btn-send" data-js="click">
					<span class="fas fa-paper-plane"></span>
				</button>
			</div>
		</div>
		<div class="col-3 px-0 bg-color-grey-50">
			<div class="px-2">
				<input type="text"
					   class="form-control u-font-size-13px js-search-participants border-bottom bg-color-grey-50 rounded-0 c-chat__form-control"
					   autocomplete="off"
					   placeholder="{\App\Language::translate('LBL_SEARCH_PARTICIPANTS', $MODULE_NAME)}"
					   data-js="keydown"/>
			</div>
			<span class="fas fa-search c-chat__icon-search"></span>
			<div class="text-uppercase bg-color-grey-200 p-2 my-2 font-weight-bold u-font-size-14px">
				{\App\Language::translate('LBL_PARTICIPANTS', $MODULE_NAME)}
			</div>
			<div class="js-participants-list px-3 c-chat__scrollbar js-scrollbar" data-js="container|perfectscrollbar">
				{ITEM_USER USER=['user_id'=>'', 'user_name'=>'', 'image'=>null] CLASS='js-temp-item-user hide'}
				<ul class="js-users pl-0 m-0" data-js="container">
					{foreach item=USER from=$PARTICIPANTS}
						{ITEM_USER USER=$USER}
					{/foreach}
				</ul>
			</div>
		</div>
	</div>
	<!-- /tpl-Chat-Chat -->
{/strip}