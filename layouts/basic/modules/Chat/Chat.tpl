{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Chat-Chat -->
	{function ITEM_USER CLASS=''}
		<li class="js-item-user o-chat__user-item {$CLASS} border-bottom pb-1 mb-2" data-user-id="{$USER['user_id']}"
			data-js="data">
			<div class="row px-2">
				{assign var=IS_IMAGE value=!empty($USER['image'])}
				<div class="js-image o-chat__img-container {if !$IS_IMAGE} p-1 {/if} text-center" data-js="append">
					<img src="{$USER['image']}" class="js-chat-image_src {if !$IS_IMAGE} hide{/if} o-chat__author-img"
						 alt="{$USER['user_name']}"
						 title="{$USER['user_name']}" data-js="hide"/>
					<span class="js-chat-image_icon fas fa-user u-font-size-38px userImage {if $IS_IMAGE} hide{/if} o-chat__author-name"
						  data-js="hide"></span>
				</div>
				<div class="col-9 px-4">
					<div class="js-user-name u-font-size-13px">{$USER['user_name']}</div>
					<div class="js-role u-font-size-10px font-weight-bold color-blue-600">{$USER['role_name']}</div>
					<div class="js-message o-chat__user-message text-truncate">
						{\App\Utils\Completions::decode(\App\Purifier::purifyHtml(\App\Purifier::decodeHtml($USER['message'])))}
					</div>
				</div>
			</div>
		</li>
	{/function}
	<div class="row o-chat">
		<div class="col-9 js-message-container js-completions__container" data-js="class: .js-message-container">
			<div class="row px-2">
				<div class="input-group js-input-group-search" data-js="class: .js-input-group-search">
					<div class="input-group-prepend">
						<span class="input-group-text bg-white hide js-search-cancel border-bottom o-chat__form-control rounded-0">
							<span class="u-cursor-pointer" data-js="click" aria-hidden="true">&times;</span>
						</span>
					</div>
					<input type="text"
						   class="form-control u-font-size-13px js-search-message border-bottom rounded-0 o-chat__form-control"{' '}
						   autocomplete="off"{' '}
						   placeholder="{\App\Language::translate('LBL_SEARCH_MESSAGE', $MODULE_NAME)}"
						   data-js="keydown"/>
					<div class="input-group-append">
						<span class="input-group-text bg-white border-bottom o-chat__form-control u-cursor-pointer js-icon-search-message">
							<span class="fas fa-search"></span>
						</span>
					</div>
				</div>
				<div class="js-chat-nav-history w-100 hide" data-js="class:hide">
					<ul class="nav nav-tabs">
						<li class="nav-item js-chat-link" data-group-name="crm">
							<a class="nav-link active" href="#" role="tab" data-toggle="tab">
								{\App\Language::translate('LBL_ROOM_CRM', $MODULE_NAME)}
							</a>
						</li>
						<li class="nav-item js-chat-link" data-group-name="group">
							<a class="nav-link" href="#" role="tab" data-toggle="tab">
								{\App\Language::translate('LBL_ROOM_GROUP', $MODULE_NAME)}
							</a>
						</li>
						<li class="nav-item js-chat-link" data-group-name="global">
							<a class="nav-link" href="#" role="tab" data-toggle="tab">
								{\App\Language::translate('LBL_ROOM_GLOBAL', $MODULE_NAME)}
							</a>
						</li>
					</ul>
				</div>
			</div>
			<div class="d-flex flex-column js-chat-main-content o-chat__scrollbar js-scrollbar"
				 data-js="container|perfectscrollbar">
				<div class="d-flex flex-grow-1">
					<div class="js-chat_content js-completions__messages col-12 px-2 h-100 w-100 mb-4"
						 data-current-room-type="{$CURRENT_ROOM['roomType']}"
						 data-current-record-id="{$CURRENT_ROOM['recordId']}"
						 data-message-timer="{AppConfig::module('Chat', 'REFRESH_MESSAGE_TIME')}"
						 data-room-timer="{AppConfig::module('Chat', 'REFRESH_ROOM_TIME')}"
						 data-max-length-message="{AppConfig::module('Chat', 'MAX_LENGTH_MESSAGE')}"
						 data-view-for-record="{if isset($VIEW_FOR_RECORD) && $VIEW_FOR_RECORD}true{else}false{/if}"
						 data-js="append">
						{include file=\App\Layout::getTemplatePath('Entries.tpl', 'Chat')}
					</div>
				</div>
			</div>
			<div class="c-completions js-completions__actions">
				<span class="c-completions__item js-completions__emojis far fa-smile"></span>
				<span class="c-completions__item js-completions__users fas fa-user-plus"></span>
				<span class="c-completions__item js-completions__records fas fa-hashtag"></span>
			</div>
			<div class="d-flex flex-nowrap js-chat-message-block border-top" data-js="hide" data-js="perfectscrollbar">
				<div class="js-scrollbar o-chat__form-control o-chat__message-block">
					<div class="u-font-size-13px js-chat-message js-completions o-chat__form-control"
						 contenteditable="true"
						 data-completions-buttons="true"
						 placeholder="{\App\Language::translate('LBL_MESSAGE', $MODULE_NAME)}"
						 data-js="keydown | tribute.js">
					</div>
				</div>
				<button type="button" class="btn btn-primary js-btn-send o-chat__btn-send" data-js="click">
					<span class="fas fa-paper-plane"></span>
				</button>
			</div>
		</div>
		<div class="col-3 px-0 bg-color-grey-50 js-users" data-js="class: .js-users">
			{if !(isset($IS_MODAL_VIEW) && $IS_MODAL_VIEW) }
				<div class="mb-3 mt-0">
					<button type="button"
							class="btn btn-danger ml-2{if !$CHAT->isAssigned()} hide{/if} js-remove-from-favorites"
							data-js="click">
						<span class="fa fa-minus mr-2"
							  title="{\App\Language::translate('LBL_REMOVE_FROM_FAVORITES', $MODULE_NAME)}"></span>
						{\App\Language::translate('LBL_REMOVE_FROM_FAVORITES', $MODULE_NAME)}
					</button>
					<button type="button"
							class="btn btn-success ml-2{if $CHAT->isAssigned()} hide{/if} js-add-from-favorites"
							data-js="click">
						<span class="fa fa-plus mr-2"
							  title="{\App\Language::translate('LBL_ADD_FROM_FAVORITES', $MODULE_NAME)}"></span>
						{\App\Language::translate('LBL_ADD_FROM_FAVORITES', $MODULE_NAME)}
					</button>
				</div>
			{/if}
			<div class="px-2 input-group">
				<div class="input-group-prepend">
						<span class="input-group-text bg-color-grey-50 hide js-search-participants-cancel border-bottom o-chat__form-control rounded-0">
							<span class="u-cursor-pointer" data-js="click"
								  aria-hidden="true">&times;</span>
						</span>
				</div>
				<input type="text"
					   class="form-control u-font-size-13px js-search-participants border-bottom bg-color-grey-50 rounded-0 o-chat__form-control"
					   autocomplete="off"
					   placeholder="{\App\Language::translate('LBL_SEARCH_PARTICIPANTS', $MODULE_NAME)}"
					   data-js="keydown"/>
				<div class="input-group-append">
						<span class="input-group-text bg-color-grey-50 border-bottom o-chat__form-control"><span
									class="fas fa-search"></span></span>
				</div>
			</div>
			<div class="text-uppercase bg-color-grey-200 p-2 my-2 font-weight-bold u-font-size-14px">
				{\App\Language::translate('LBL_PARTICIPANTS', $MODULE_NAME)}

			</div>
			<div class="js-participants-list px-3 o-chat__scrollbar o-chat__entries-scrollbar js-scrollbar"
				 data-js="container|perfectscrollbar">
				{ITEM_USER USER=['user_id'=>'', 'user_name'=>'', 'role_name'=>'', 'message'=>'', 'image'=>null] CLASS='js-temp-item-user hide'}
				<ul class="js-users pl-0 m-0" data-js="container">
					{foreach item=USER from=$CHAT->getParticipants()}
						{ITEM_USER USER=$USER}
					{/foreach}
				</ul>
			</div>
		</div>
	</div>
	<!-- /tpl-Chat-Chat -->
{/strip}
