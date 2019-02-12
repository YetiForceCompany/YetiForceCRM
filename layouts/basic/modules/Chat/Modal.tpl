{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Chat-Modal -->
	{function ROOM_ITEM CLASS_NAME='' ROOM_TYPE='' FAVORITE_ADD_BTN=false FAVORITE_REMOVE_BTN=false}
		{assign var=SELECTED value=$CURRENT_ROOM['recordId'] == $ROOM['recordid'] && $CURRENT_ROOM['roomType'] == $ROOM_TYPE }
		<li class="text-truncate col-12 js-room o-chat__room-hover u-cursor-pointer{if empty($CLASS_NAME)} d-flex{/if} {if $SELECTED} active o-chat__room {/if} {$CLASS_NAME} py-1 pr-1 pl-3"
			title="{\App\Purifier::encodeHtml($ROOM['name'], 'Chat')}"
			data-record-id="{$ROOM['recordid']}"
			data-js="click">
			<div class="col-7 p-0">
				<span class="js-room-name" data-js="append|replace">{$ROOM['name']}</span>
			</div>
			<div class="col-3 p-0 text-right">
				<span class="js-room-cnt badge badge-info ml-1 inline" data-js="append|replace">
					{if $ROOM['cnt_new_message'] > 0}{$ROOM['cnt_new_message']}{/if}
				</span>
			</div>
			<div class="col-1 text-right px-2 d-flex align-items-center o-chat__pin-favorites">
				<a href="{if isset($ROOM['moduleName'])}index.php?module={$ROOM['moduleName']}&view=Detail&record={$ROOM['recordid']}{else}#{/if}"
				   class="{if $ROOM_TYPE!=='crm'}hide js-link{/if}" data-js="hide">
					<span class="fas fa-link"
						  title="{\App\Language::translate('LBL_DETAIL_VIEW', $MODULE_NAME)}"></span>
				</a>
			</div>
			<div class="col-1 text-right px-2 d-flex align-items-center o-chat__pin-favorites">
				<a href="#" class="{if !$FAVORITE_REMOVE_BTN} hide{/if} js-remove-favorites" data-js="click">
					<span class="fas fa-thumbtack text-danger"
						  title="{\App\Language::translate('LBL_REMOVE_FROM_FAVORITES', $MODULE_NAME)}"></span>
				</a>
				<a href="#" class="{if !$FAVORITE_ADD_BTN} hide{/if} js-add-favorites" data-js="click">
					<span class="fas fa-thumbtack text-light"
						  title="{\App\Language::translate('LBL_ADD_FROM_FAVORITES', $MODULE_NAME)}"></span>
				</a>
			</div>
		</li>
	{/function}
	<div class="modal-body pt-0 pb-0">
		<div class="row p-0">
			<div class="col-2 bg-color-grey-50 m-0 p-0 js-room-list" data-js="container">
				<div class="w-100 text-right p-2 o-chat__icon-container">
					<a class="ml-auto mr-1 js-btn-enter" data-icon-on="fa-keyboard" data-icon-off="fa-ban"
					   data-js="click" href="#">
						<span class="js-icon fas {if $SEND_BY_ENTER}fa-keyboard{else}fa-ban{/if}"
							  title="{\App\Language::translate('LBL_ENTER', $MODULE_NAME)}" data-js="replace">
						</span>
					</a>
					<a class="ml-auto mr-1 js-btn-unread" data-js="click" href="#">
						<span class="fas fa-comments"
							  title="{\App\Language::translate('LBL_UNREAD', $MODULE_NAME)}">
						</span>
					</a>
					<a class="ml-auto mr-1 js-btn-history" data-js="click" href="#">
						<span class="fas fa-history"
							  title="{\App\Language::translate('LBL_HISTORY_CHAT', $MODULE_NAME)}">
						</span>
					</a>
					<a class="js-btn-desktop-notification mr-1" data-icon-on="fa-bell"
					   data-icon-off="fa-bell-slash" data-js="click" href="#">
						<span class="js-icon fas {if $IS_DESKTOP_NOTIFICATION}fa-bell{else}fa-bell-slash{/if}"
							  title="{\App\Language::translate('LBL_NOTIFICATION', $MODULE_NAME)}" data-js="replace">
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
				{foreach item=GROUP_ROOM key=KEY from=\App\Chat::getRoomsByUser()}
					{assign var=LBL_GROUP_ROOM value="LBL_ROOM_$KEY"|upper}
					<div class="text-uppercase bg-color-grey-200 p-2 font-weight-bold js-group-name" data-js="data"
						 data-group="{$KEY}">
						{if $KEY === 'crm'}
							<span class="fas fa-star mr-2"></span>
						{elseif $KEY === 'group'}
							<span class="fas fa-users mr-2"></span>
						{elseif $KEY === 'global'}
							<span class="fas fa-globe mr-2"></span>
						{/if}
						{\App\Language::translate($LBL_GROUP_ROOM, $MODULE_NAME)}
						<div class="js-popover-tooltip ml-2 float-right" data-js="popover"
							 data-content="{\App\Language::translate(strtoupper("LBL_ROOM_DESCRIPTION_"|cat:$KEY), $MODULE_NAME)}">
							<span class="fas fa-info-circle"></span>
						</div>
					</div>
					{assign var=FAVORITE_REMOVE_BTN value=$KEY==='crm' || $KEY==='group'}
					<ul class="js-room-type u-font-size-13px p-0 mb-0" data-room-type="{$KEY}"
						data-favorite-remove-btn="{if $FAVORITE_REMOVE_BTN}true{else}false{/if}" data-js="data">
						{foreach item=ROOM from=$GROUP_ROOM}
							{ROOM_ITEM ROOM=$ROOM ROOM_TYPE=$KEY FAVORITE_REMOVE_BTN=$FAVORITE_REMOVE_BTN }
						{/foreach}
					</ul>
					{if $KEY==='group'}
						{assign var=USER_GROUP value=[]}
						{foreach item=ROOM from=$GROUP_ROOM}
							{$USER_GROUP[]=$ROOM['recordid']}
						{/foreach}
						<ul class="js-room-type js-hide-group hide u-font-size-13px p-0 mb-0" data-room-type="{$KEY}"
							data-js="data">
							{assign var=CNT_GROUP value=0}
							{foreach item=GROUP_NAME key=GROUP_ID from=\App\User::getCurrentUserModel()->getGroupNames()}
								{if in_array($GROUP_ID, $USER_GROUP)}
									{continue}
								{/if}
								{assign var=CNT_GROUP value=$CNT_GROUP+1}
								{assign var=TRANSLATE_GROUP value=\App\Language::translate($GROUP_NAME)}
								{assign var=SELECTED value=false}
								{ROOM_ITEM ROOM=['name'=>$TRANSLATE_GROUP, 'recordid'=>$GROUP_ID, 'cnt_new_message'=>0] ROOM_TYPE=$KEY FAVORITE_ADD_BTN=true}
							{/foreach}
						</ul>
						<div class="col-12 px-2 text-right mb-1">
							<a href="#" class="text-success js-btn-more{if $CNT_GROUP===0} hide{/if}" data-js="click">
								{\App\Language::translate('LBL_MORE', $MODULE_NAME)}
							</a>
							<a href="#" class="text-danger hide js-btn-more-remove" data-js="click">
								{\App\Language::translate('LBL_HIDE', $MODULE_NAME)}
							</a>
						</div>
					{/if}
				{/foreach}
			</div>
			<div class="col-10 m-0">
				{include file=\App\Layout::getTemplatePath('Chat.tpl', 'Chat')}
			</div>
		</div>
	</div>
	<!-- /tpl-Chat-Modal -->
{/strip}
