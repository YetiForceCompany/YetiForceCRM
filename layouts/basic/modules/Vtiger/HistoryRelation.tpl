{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Base-HistoryRelation recentActivitiesContainer pt-0">
		<input class="js-relatedHistoryCurrentPage" type="hidden" id="relatedHistoryCurrentPage"
			value="{$PAGING_MODEL->get('page')}"
			data-js="value" />
		<input class="js-relatedHistoryPageLimit" type="hidden" id="relatedHistoryPageLimit"
			value="{$PAGING_MODEL->getPageLimit()}" data-js="value" />
		{if !empty($HISTORIES)}
			<ul class="timeline" id="relatedUpdates">
				{foreach item=HISTORY from=$HISTORIES}
					<li>
						<div class="d-flex">
							<span class="c-circle-icon mt-2 text-light {$HISTORY['class']} yfm-{$HISTORY['type']}"></span>
							<div class="flex-grow-1 ml-1 p-1 timeline-item">
								<div class="float-left imageContainer d-sm-block d-none">
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
								<div class="timeline-body small">
									<div class="d-flex align-items-center">
										<strong>{$HISTORY['userModel']->getName()}</strong>
										<div class="btn-group ml-auto mr-1 d-md-block d-none" role="group">
											{if !$IS_READ_ONLY && $HISTORY['type'] eq 'OSSMailView'}
												<button data-url="{$HISTORY['url']|cat:'&noloadlibs=1'}" type="button" class="showModal btn btn-sm btn-light" data-cb="Vtiger_Index_Js.registerMailButtons">
													<span class="body-icon fas fa-search"
														title="{\App\Language::translate('LBL_SHOW_PREVIEW_EMAIL', 'OSSMailView')}"></span>
													<span class="sr-only">{\App\Language::translate('LBL_SHOW_PREVIEW_EMAIL', 'OSSMailView')}</span>
												</button>
												{if \App\Mail::checkInternalMailClient()}
													{assign var=COMPOSE_URL value=OSSMail_Module_Model::getComposeUrl($MODULE_NAME, $RECORD_ID, 'Detail')}
													<button type="button" class="btn btn-sm btn-light sendMailBtn"
														data-url="{$COMPOSE_URL}&mid={$HISTORY['id']}&type=reply"
														data-popup="{$POPUP}">
														<span class="fas fa-reply"
															title="{\App\Language::translate('LBL_REPLY','OSSMailView')}"></span>
														<span class="sr-only">{\App\Language::translate('LBL_REPLY', 'OSSMailView')}</span>
													</button>
													<button type="button" class="btn btn-sm btn-light sendMailBtn"
														data-url="{$COMPOSE_URL}&mid={$HISTORY['id']}&type=replyAll"
														data-popup="{$POPUP}">
														<span class="fas fa-reply-all"
															title="{\App\Language::translate('LBL_REPLYALLL', 'OSSMailView')}"></span>
														<span class="sr-only">{\App\Language::translate('LBL_REPLYALLL', 'OSSMailView')}</span>
													</button>
													<button type="button" class="btn btn-sm btn-light sendMailBtn"
														data-url="{$COMPOSE_URL}&mid={$HISTORY['id']}&type=forward"
														data-popup="{$POPUP}">
														<span class="fas fa-share"
															title="{\App\Language::translate('LBL_FORWARD', 'OSSMailView')}"></span>
														<span class="sr-only">{\App\Language::translate('LBL_FORWARD', 'OSSMailView')}</span>
													</button>
												{/if}
											{/if}
											<a class="btn btn-sm btn-light" href="{$HISTORY['url']}" target="_blank" rel="noreferrer noopener">
												<span class="fas fa-th-list mx-1" title="{\App\Language::translate('LBL_DETAILS', $MODULE_NAME)}"></span>
											</a>
										</div>
										<div class="time text-muted ml-md-0 ml-auto">
											<span>{\App\Fields\DateTime::formatToViewDate($HISTORY['time'])}</span>
										</div>
									</div>
									<div class="js-hb__container ml-auto float-right d-md-none d-sm-block">
										<button type="button" tabindex="0" class="btn js-hb__btn u-hidden-block-btn text-grey-6 py-0 px-1">
											<div class="text-center col items-center justify-center row">
												<i aria-hidden="true" class="mdi mdi-wrench q-icon"></i>
											</div>
										</button>
										<div class="u-hidden-block items-center js-comment-actions">
											{if !$IS_READ_ONLY && $HISTORY['type'] eq 'OSSMailView'}
												<button data-url="{$HISTORY['url']|cat:'&noloadlibs=1'}" type="button"
													class="showModal btn btn-sm btn-light"
													data-cb="Vtiger_Index_Js.registerMailButtons">
													<span class="body-icon fas fa-search"
														title="{\App\Language::translate('LBL_SHOW_PREVIEW_EMAIL', 'OSSMailView')}"></span>
													<span class="sr-only">{\App\Language::translate('LBL_SHOW_PREVIEW_EMAIL', 'OSSMailView')}</span>
												</button>
												{if \App\Mail::checkInternalMailClient()}
													{assign var=COMPOSE_URL value=OSSMail_Module_Model::getComposeUrl($MODULE_NAME, $RECORD_ID, 'Detail')}
													<button type="button" class="btn btn-sm btn-light sendMailBtn"
														data-url="{$COMPOSE_URL}&mid={$HISTORY['id']}&type=reply"
														data-popup="{$POPUP}">
														<span class="fas fa-reply"
															title="{\App\Language::translate('LBL_REPLY','OSSMailView')}"></span>
														<span class="sr-only">{\App\Language::translate('LBL_REPLY', 'OSSMailView')}</span>
													</button>
													<button type="button" class="btn btn-sm btn-light sendMailBtn"
														data-url="{$COMPOSE_URL}&mid={$HISTORY['id']}&type=replyAll"
														data-popup="{$POPUP}">
														<span class="fas fa-reply-all"
															title="{\App\Language::translate('LBL_REPLYALLL', 'OSSMailView')}"></span>
														<span class="sr-only">{\App\Language::translate('LBL_REPLYALLL', 'OSSMailView')}</span>
													</button>
													<button type="button" class="btn btn-sm btn-light sendMailBtn"
														data-url="{$COMPOSE_URL}&mid={$HISTORY['id']}&type=forward"
														data-popup="{$POPUP}">
														<span class="fas fa-share"
															title="{\App\Language::translate('LBL_FORWARD', 'OSSMailView')}"></span>
														<span class="sr-only">{\App\Language::translate('LBL_FORWARD', 'OSSMailView')}</span>
													</button>
												{/if}
											{/if}
											<a class="btn btn-sm btn-light" href="{$HISTORY['url']}" target="_blank" rel="noreferrer noopener">
												<span class="fas fa-th-list mx-1" title="{\App\Language::translate('LBL_DETAILS', $MODULE_NAME)}"></span>
											</a>
										</div>
									</div>
									<div class="u-word-break">{\App\Utils\Completions::decode(Vtiger_Util_Helper::toVtiger6SafeHTML($HISTORY['content']))}</div>
									<div class="u-word-break">
										{if $HISTORY['attachments_exist'] eq 1}
											&nbsp;
											<span class="body-icon fas fa-paperclip"></span>
										{/if}
										{\App\Purifier::encodeHtml($HISTORY['body'])}
									</div>
								</div>
							</div>
						</div>
					</li>
				{/foreach}
			</ul>
			{if !$IS_READ_ONLY && count($HISTORIES) eq $PAGING_MODEL->getPageLimit() && !$NO_MORE}
				<div id="moreRelatedUpdates">
					<div class="float-right mb-1">
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
