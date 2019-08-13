{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Base-HistoryRelation recentActivitiesContainer pt-2">
		<input class="js-relatedHistoryCurrentPage" type="hidden" id="relatedHistoryCurrentPage"
			   value="{$PAGING_MODEL->get('page')}"
			   data-js="value"/>
		<input class="js-relatedHistoryPageLimit" type="hidden" id="relatedHistoryPageLimit"
			   value="{$PAGING_MODEL->getPageLimit()}" data-js="value"/>
		<nav>
 			<div class="nav mb-2 nav-under u-border-bottom-timeline-nav" id="nav-tab" role="tablist">
			 	{foreach from=Vtiger_HistoryRelation_Widget::getActions() item=ACTIONS}
					<a class="nav-item nav-link relatedHistoryTypes {if $ACTIONS eq $SELECTED_TAB }active text-primary {else}  text-dark {/if}" data-js="click" data-tab="{$ACTIONS}" data-toggle="tab" href="#nav-{$ACTIONS}" role="tab" aria-controls="nav-{$MODULE_TAB}">{\App\Language::translate($ACTIONS, $ACTIONS)}</a>
				{/foreach}
  		</div>
		</nav>
		{if !empty($HISTORIES)}
			<ul class="timeline" id="relatedUpdates">
				{foreach item=HISTORY from=$HISTORIES}
					<li>
						<div class="u-font-size-13px text-muted text-right">
							<span>{\App\Fields\DateTime::formatToViewDate($HISTORY['time'])}</span>
							<a href="{$HISTORY['url']}" target="_blank" rel="noreferrer noopener">
								<span class="fas fa-link mx-1"
							  title="{\App\Language::translate('LBL_DETAILS', $MODULE_NAME)}"></span>
							</a>
						</div>
						<div>
							<span class="position-absolute c-badge__icon {$HISTORY['class']} userIcon-{$HISTORY['type']}"
								  aria-hidden="true"></span>
							<div class="w-auto ml-5 p-1 timeline-item d-flex flex-row border">
								<div class="imageContainer d-flex align-items-center ml-2">
									{if !$HISTORY['isGroup']}
										{assign var=IMAGE value=$HISTORY['userModel']->getImage()}
										{if $IMAGE}
											<img class="userImage" src="{$IMAGE['url']}">
										{else}
											<span class="fas fa-user userImage"></span>
										{/if}
									{else}
										<span class="fas fa-user userImage"></span>
									{/if}
								</div>
								<div class="timeline-body small d-flex align-items-start mr-auto">
									<strong>{$HISTORY['userModel']->getName()}&nbsp;</strong>
									<div>{\App\Utils\Completions::decode(Vtiger_Util_Helper::toVtiger6SafeHTML($HISTORY['content']))}</div>

								</div>
									<div class="d-flex justify-content-end {$HISTORY['class']}">
											&nbsp;
											<span class="body-icon fas fa-paperclip"></span>
											<div class="btn-group-vertical" role="group">
												<button data-url="{$HISTORY['url']|cat:'&noloadlibs=1'}" type="button"
														class="showModal btn btn-xs btn-primary-outline"
														data-cb="Vtiger_Index_Js.registerMailButtons">
												<span class="body-icon fas fa-search"
													  title="{\App\Language::translate('LBL_SHOW_PREVIEW_EMAIL', 'OSSMailView')}"></span>
													<span class="sr-only">{\App\Language::translate('LBL_SHOW_PREVIEW_EMAIL', 'OSSMailView')}</span>
												</button>
												{if App\Config::main('isActiveSendingMails') && \App\Privilege::isPermitted('OSSMail') && $USER_MODEL->internal_mailer == 1}
													{assign var=COMPOSE_URL value=OSSMail_Module_Model::getComposeUrl($MODULE_NAME, $RECORD_ID, 'Detail')}
													<button type="button" class="btn btn-xs btn-primary-outline sendMailBtn"
															data-url="{$COMPOSE_URL}&mid={$HISTORY['id']}&type=reply"
															data-popup="{$POPUP}">
													<span class="fas fa-reply"
														  title="{\App\Language::translate('LBL_REPLY','OSSMailView')}"></span>
														<span class="sr-only">{\App\Language::translate('LBL_REPLY', 'OSSMailView')}</span>
													</button>
													<button type="button" class="btn btn-xs btn-primary-outline sendMailBtn"
															data-url="{$COMPOSE_URL}&mid={$HISTORY['id']}&type=replyAll"
															data-popup="{$POPUP}">
													<span class="fas fa-reply-all"
														  title="{\App\Language::translate('LBL_REPLYALLL', 'OSSMailView')}"></span>
														<span class="sr-only">{\App\Language::translate('LBL_REPLYALLL', 'OSSMailView')}</span>
													</button>
													<button type="button" class="btn btn-xs btn-primary-outline sendMailBtn"
															data-url="{$COMPOSE_URL}&mid={$HISTORY['id']}&type=forward"
															data-popup="{$POPUP}">
													<span class="fas fa-share"
														  title="{\App\Language::translate('LBL_FORWARD', 'OSSMailView')}"></span>
														<span class="sr-only">{\App\Language::translate('LBL_FORWARD', 'OSSMailView')}</span>
													</button>
												{/if}
											</div>
										{\App\Purifier::encodeHtml($HISTORY['body'])}
									</div>
							</div>
						</div>
					</li>
				{/foreach}
			</ul>
			{if !$IS_READ_ONLY && count($HISTORIES) eq $PAGING_MODEL->getPageLimit() && !$NO_MORE}
				<div id="moreRelatedUpdates">
					<div class="float-right">
						<button type="button"
								class="btn btn-primary btn-sm moreRelatedUpdates u-cursor-pointer">{\App\Language::translate('LBL_MORE',$MODULE_NAME)}
							..
						</button>
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
