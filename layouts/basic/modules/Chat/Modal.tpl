{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Chat-Modal -->
	{function ROOM_ITEM CLASS_NAME='' FAVORITE_BTN=false}
		{assign var=SELECTED value=$CURRENT_ROOM['recordId'] == $ROOM['recordid'] && $CURRENT_ROOM['roomType'] == $ROOM_TYPE }
		<li class="text-truncate js-room o-chat__room-hover u-cursor-pointer {if $SELECTED} active o-chat__room {/if} {$CLASS_NAME} py-1 pr-1 pl-3"
			title="{\App\Purifier::encodeHtml($ROOM['name'], 'Chat')}"
			data-record-id="{$ROOM['recordid']}"
			data-js="click">
			<span class="js-room-name" data-js="append|replace">{$ROOM['name']}</span>
			<span class="js-room-cnt badge badge-info ml-1 inline" data-js="append|replace">
				{if $ROOM['cnt_new_message'] > 0}{$ROOM['cnt_new_message']}{/if}
			</span>
			<a href="#" class="inline mr-2 js-remove-favorites{if !$FAVORITE_BTN} hide{/if}"
			   data-record-id="{$ROOM['recordid']}" data-js="click">
				<span class="fas fa-thumbtack text-danger"
					  title="{\App\Language::translate('LBL_REMOVE_FROM_FAVORITES', $MODULE_NAME)}"></span>
			</a>
		</li>
	{/function}
	<div class="modal-body pt-0 pb-0">
		<div class="row p-0">
			<div class="col-2 bg-color-grey-50 m-0 p-0 js-room-list" data-js="container">
				<div class="w-100 text-right p-2 o-chat__icon-container">
					<a class="ml-auto mr-1 js-btn-unread" data-js="click" href="#">
						<span class="fas fa-comments"
							  title="{\App\Language::translate('LBL_HISTORY_CHAT', $MODULE_NAME)}">
						</span>
					</a>
					<a class="ml-auto mr-1 js-btn-history" data-js="click" href="#">
						<span class="fas fa-history"
							  title="{\App\Language::translate('LBL_HISTORY_CHAT', $MODULE_NAME)}">
						</span>
					</a>
					<a class="js-btn-desktop-notification mr-1" data-icon-on="fa-bell"
					   data-icon-off="fa-bell-slash" data-js="click" href="#">
						<span class="js-icon fas fa-bell"
							  title="{\App\Language::translate('LBL_NOTIFICATION', $MODULE_NAME)}">
						</span>
					</a>
					<a class="js-btn-bell mr-1" data-icon-on="fa-volume-up"
					   data-icon-off="fa-volume-mute" data-js="click" href="#">
						<span class="fas {if $IS_SOUND_NOTIFICATION}fa-volume-up{else}fa-volume-mute{/if} js-icon"
							  data-js="replace"
							  title="{\App\Language::translate('LBL_SOUND_ON', $MODULE_NAME)}">
						</span>
					</a>
					<button type="button" class="close float-left" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				{ROOM_ITEM ROOM=['recordid'=>'', 'name'=>'', 'cnt_new_message'=>''] CLASS_NAME='hide js-temp-item-room'}
				{assign var=ROOMS_BY_USER value=\App\Chat::getRoomsByUser()}
				<!-- CRM -->
				<div class="text-uppercase bg-color-grey-200 p-2 font-weight-bold js-group-name" data-js="data"
					 data-group="crm">
					<span class="fas fa-star mr-2"></span>
					{\App\Language::translate('LBL_ROOM_CRM', $MODULE_NAME)}
				</div>
				<ul class="js-room-type u-font-size-13px p-0" data-room-type="crm" data-favorite="true" data-js="data">
					{foreach item=ROOM from=$ROOMS_BY_USER['crm']}
						{ROOM_ITEM ROOM=$ROOM CLASS_NAME='' ROOM_TYPE='crm' FAVORITE_BTN=true}
					{/foreach}
				</ul>
				<!-- GROUP -->
				<div class="text-uppercase bg-color-grey-200 p-2 font-weight-bold js-group-name" data-js="data"
					 data-group="group">
					<span class="fas fa-users mr-2"></span>
					{\App\Language::translate('LBL_ROOM_GROUP', $MODULE_NAME)}
				</div>
				<ul class="js-room-type u-font-size-13px p-0" data-room-type="group" data-favorite="false"
					data-js="data">
					{foreach item=GROUP_NAME key=GROUP_ID from=\App\Fields\Owner::getInstance('CustomView')->getGroups(false)}
						{assign var=TRANSLATE_GROUP value=\App\Language::translate($GROUP_NAME)}
						{assign var=SELECTED value=$CURRENT_ROOM['recordId'] == $GROUP && $CURRENT_ROOM['roomType'] === 'group'}
						<div class="w-100 row m-0 hide js-group js-hide">
							<li class="text-truncate col-11 js-room js-group-room o-chat__room-hover {if $CHAT->isAssigned()} hide {/if} u-cursor-pointer d-flex  {if $SELECTED}active o-chat__room{/if} py-1 pr-1 pl-3"
								title="{\App\Purifier::encodeHtml($TRANSLATE_GROUP)}"
								data-record-id="{$GROUP_ID}"
								data-js="click">
								<div class="col-9 p-0">
									<span class="js-room-name" data-js="append|replace">{$TRANSLATE_GROUP}</span>
								</div>
								<div class="col-3 p-0 text-right">
									<span class="js-room-cnt badge badge-info ml-1 inline" data-js="append|replace">
										{if $ROOM['cnt_new_message'] > 0}{$ROOM['cnt_new_message']}{/if}
									</span>
								</div>
							</li>
							<div class="col-1  text-right px-2 d-flex align-items-center">
								<a class="{if $CHAT->isAssigned()} hide{/if} js-remove-favorites" data-js="click"
								   href="#" data-record-id="{$GROUP_ID}">
									<span class="fas fa-thumbtack text-danger"
										  title="{\App\Language::translate('LBL_REMOVE_FROM_FAVORITES', $MODULE_NAME)}"></span>
								</a>
								<a class="{if !$CHAT->isAssigned()} hide{/if} js-add-favorites" data-js="click"
								   href="#" data-record-id="{$GROUP_ID}">
									<span class="fas fa-thumbtack text-success"
										  title="{\App\Language::translate('LBL_ADD_FROM_FAVORITES', $MODULE_NAME)}"></span>
								</a>
							</div>
						</div>
					{/foreach}
				</ul>
				<div class="col-12 px-2 text-right mb-1">
					<button type="button"
							class="btn btn-sm btn-success js-btn-more"
							data-js="click">
						{\App\Language::translate('LBL_MORE', $MODULE_NAME)}
					</button>
				</div>
				<!-- GLOBAL -->
				<div class="text-uppercase bg-color-grey-200 p-2 font-weight-bold js-group-name" data-js="data"
					 data-group="global">
					<span class="fas fa-globe mr-2"></span>
					{\App\Language::translate('LBL_ROOM_GLOBAL', $MODULE_NAME)}
				</div>
				<ul class="js-room-type u-font-size-13px p-0" data-room-type="global" data-favorite="false"
					data-js="data">
					{foreach item=ROOM from=$ROOMS_BY_USER['global']}
						{ROOM_ITEM ROOM=$ROOM CLASS_NAME='' ROOM_TYPE='global'}
					{/foreach}
				</ul>
				<!-- - -->
			</div>
			<div class="col-10 m-0">
				{include file=\App\Layout::getTemplatePath('Chat.tpl', 'Chat')}
			</div>
		</div>
	</div>
	<!-- /tpl-Chat-Modal -->
{/strip}
