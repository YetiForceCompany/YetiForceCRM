{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
{strip}
	<div class="recentActivitiesContainer row no-margin" >
		<input type="hidden" id="relatedHistoryCurrentPage" value="{$PAGING_MODEL->get('page')}" />
		<input type="hidden" id="relatedHistoryPageLimit" value="{$PAGING_MODEL->getPageLimit()}" />
		{if !empty($HISTORIES)}
			<ul class="timeline" id="relatedUpdates">
				{foreach item=HISTORY from=$HISTORIES}
					<li>
						<span class="glyphicon {$HISTORY['class']} userIcon-{$HISTORY['type']}" aria-hidden="true"></span>
						<div class="timeline-item">
							<div class="pull-left paddingRight15 imageContainer">
								{if !$HISTORY['isGroup']}
									{assign var=IMAGE value=$HISTORY['userModel']->getImagePath()}
									{if $IMAGE}
										<img class="userImage" src="data:image/jpg;base64,{base64_encode(file_get_contents($IMAGE))}" >
									{else}	
										<span class="glyphicon glyphicon-user userImage" aria-hidden="true"></span>
									{/if}
								{else}
									<span class="glyphicon glyphicon-user userImage" aria-hidden="true"></span>
								{/if}
							</div>
							<div class="timeline-body row no-margin">
								<div class="pull-right">
									<span class="time">
										<span title="{$HISTORY['time']}">{Vtiger_Util_Helper::formatDateDiffInStrings($HISTORY['time'])}</span>
									</span>
								</div>
								<strong>{$HISTORY['userModel']->getName()}&nbsp;</strong>
								<a href="{$HISTORY['url']}" target="_blank">{$HISTORY['content']}</a>
								{if $HISTORY['attachments_exist'] eq 1}
									&nbsp;<span class="body-icon glyphicon glyphicon-paperclip"></span>
								{/if}
								{if !$IS_READ_ONLY && $HISTORY['type'] eq 'OSSMailView'}
									<div class="pull-right marginRight10 btn-group" role="group">
										<button data-url="{$HISTORY['url']|cat:'&noloadlibs=1'}" type="button" title="{\App\Language::translate('LBL_SHOW_PREVIEW_EMAIL','OSSMailView')}" class="showModal btn btn-xs btn-default" data-cb="Vtiger_Index_Js.registerMailButtons">
											<span class="body-icon glyphicon glyphicon-search"></span>
										</button>
										{if AppConfig::main('isActiveSendingMails') && \App\Privilege::isPermitted('OSSMail') && $USER_MODEL->internal_mailer == 1}
											{assign var=COMPOSE_URL value=OSSMail_Module_Model::getComposeUrl($MODULE_NAME, $RECORD_ID, 'Detail')}
											<button type="button" class="btn btn-xs btn-default sendMailBtn" data-url="{$COMPOSE_URL}&mid={$HISTORY['id']}&type=reply" data-popup="{$POPUP}" title="{\App\Language::translate('LBL_REPLY','OSSMailView')}">
												<img width="14px" src="{\App\Layout::getLayoutFile('modules/OSSMailView/previewReply.png')}" alt="{\App\Language::translate('LBL_REPLY','OSSMailView')}">
											</button>
											<button type="button" class="btn btn-xs btn-default sendMailBtn" data-url="{$COMPOSE_URL}&mid={$HISTORY['id']}&type=replyAll" data-popup="{$POPUP}" title="{\App\Language::translate('LBL_REPLYALLL','OSSMailView')}">
												<img width="14px" src="{\App\Layout::getLayoutFile('modules/OSSMailView/previewReplyAll.png')}" alt="{\App\Language::translate('LBL_REPLYALLL','OSSMailView')}">
											</button>
											<button type="button" class="btn btn-xs btn-default sendMailBtn" data-url="{$COMPOSE_URL}&mid={$HISTORY['id']}&type=forward" data-popup="{$POPUP}" title="{\App\Language::translate('LBL_FORWARD','OSSMailView')}">
												<span class="glyphicon glyphicon-share-alt"></span>
											</button>
										{/if}
									</div>
								{/if}<br />
								{$HISTORY['body']}
							</div>
						</div>
					</li>
				{/foreach}
			</ul>
			{if !$IS_READ_ONLY && count($HISTORIES) eq $PAGING_MODEL->getPageLimit() && !$NO_MORE}
				<div id="moreRelatedUpdates">
					<div class="pull-right">
						<button type="button" class="btn btn-primary btn-xs moreRelatedUpdates cursorPointer">{\App\Language::translate('LBL_MORE',$MODULE_NAME)}..</button>
					</div>
				</div>
			{/if}
		{else}
			{if $PAGING_MODEL->get('page') eq 1}
				<div class="summaryWidgetContainer">
					<p class="textAlignCenter">{\App\Language::translate('LBL_NO_RECENT_UPDATES')}</p>
				</div>
			{/if}
		{/if}
	</div>
{/strip}
