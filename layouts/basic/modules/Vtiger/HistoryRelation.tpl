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
						<div class="u-font-size-13px text-muted text-right mb-1">
							<span>{\App\Fields\DateTime::formatToViewDate($HISTORY['time'])}</span>
							<a href="{$HISTORY['url']}" target="_blank" rel="noreferrer noopener">
								<span class="fas fa-link mx-1"
							  title="{\App\Language::translate('LBL_DETAILS', $MODULE_NAME)}"></span>
							</a>
						</div>
						<div>
							<span class="position-absolute c-badge__icon {$HISTORY['class']} userIcon-{$HISTORY['type']}"
								  aria-hidden="true"></span>
							<div class="w-auto ml-5 timeline-item d-flex flex-row border">
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
								<div class="timeline-body small d-flex align-items-start mr-auto p-1">
									<strong>{$HISTORY['userModel']->getName()}&nbsp;</strong>
									<div>{\App\Utils\Completions::decode(Vtiger_Util_Helper::toVtiger6SafeHTML($HISTORY['content']))}</div>
								</div>
									<div class="d-flex justify-content-end {$HISTORY['class']}">
											&nbsp;
											{if $HISTORY['attachments_exist'] eq 1}
												<span class="body-icon fas fa-paperclip"></span>
											{/if}
											{if !$IS_READ_ONLY && $HISTORY['type'] eq 'OSSMailView'}
												<div class="btn-group-vertical" role="group">
													<button data-url="{$HISTORY['url']|cat:'&noloadlibs=1'}" type="button"
															class="showModal btn btn-xs btn-primary-outline mt-1"
															data-cb="Vtiger_Index_Js.registerMailButtons">
													<span class="body-icon fas fa-search"
															title="{\App\Language::translate('LBL_SHOW_PREVIEW_EMAIL', 'OSSMailView')}"></span>
														<span class="sr-only">{\App\Language::translate('LBL_SHOW_PREVIEW_EMAIL', 'OSSMailView')}</span>
													</button>
													{if App\Config::main('isActiveSendingMails') && \App\Privilege::isPermitted('OSSMail') && $USER_MODEL->internal_mailer == 1}
														{assign var=COMPOSE_URL value=OSSMail_Module_Model::getComposeUrl($HISTORY['type'], $RECORD_ID, 'Detail')}
														<button type="button" class="btn btn-xs btn-primary-outline sendMailBtn mt-1"
																data-url="{$COMPOSE_URL}&mid={$HISTORY['id']}&type=reply"
																data-popup="{$POPUP}">
														<span class="fas fa-reply"
																title="{\App\Language::translate('LBL_REPLY','OSSMailView')}"></span>
															<span class="sr-only">{\App\Language::translate('LBL_REPLY', 'OSSMailView')}</span>
														</button>
														<button type="button" class="btn btn-xs btn-primary-outline sendMailBtn mt-1"
																data-url="{$COMPOSE_URL}&mid={$HISTORY['id']}&type=replyAll"
																data-popup="{$POPUP}">
														<span class="fas fa-reply-all"
																title="{\App\Language::translate('LBL_REPLYALLL', 'OSSMailView')}"></span>
															<span class="sr-only">{\App\Language::translate('LBL_REPLYALLL', 'OSSMailView')}</span>
														</button>
														<button type="button" class="btn btn-xs btn-primary-outline sendMailBtn mt-1"
																data-url="{$COMPOSE_URL}&mid={$HISTORY['id']}&type=forward"
																data-popup="{$POPUP}">
														<span class="fas fa-share"
																title="{\App\Language::translate('LBL_FORWARD', 'OSSMailView')}"></span>
															<span class="sr-only">{\App\Language::translate('LBL_FORWARD', 'OSSMailView')}</span>
														</button>
													{/if}
												</div>
											{/if}
											{if !$IS_READ_ONLY && $HISTORY['type'] eq 'Calendar'}
												<div class="btn-group-vertical" role="group">
													{if \App\Privilege::isPermitted($HISTORY['type'], 'EditView', $HISTORY['id'])}
														<span class="editDefaultStatus btn-xs u-cursor-pointer float-right js-popover-tooltip showEdit mt-1" data-url="index.php?module=Calendar&view=QuickEditAjax&record={$HISTORY['id']}"
															data-content="{\App\Language::translate('LBL_EDIT',$HISTORY['type'])}" data-js="popover">
															<span class="fas fa-pencil-alt fa-fw"></span>
														</span>
													{/if}
													{if \App\Privilege::isPermitted($HISTORY['type'], 'EditView', $HISTORY['id'])}
														<span class="editDefaultStatus btn-xs text-success u-cursor-pointer js-popover-tooltip delay0 mt-1"
															data-js="popover" data-url="index.php?module=Calendar&view=QuickEditAjax&record={$HISTORY['id']}"
															data-content="{\App\Language::translate('LBL_SET_RECORD_STATUS',$HISTORY['type'])}">
															<span class="fas fa-check fa-fw"></span>
														</span>
													{/if}
													{if \App\Privilege::isPermitted($HISTORY['type'], 'Delete', $HISTORY['id'])}
														<span class="editDefaultStatus btn-xs text-danger u-cursor-pointer js-popover-tooltip delay0 mt-1"
															data-js="popover" data-url="index.php?module=Calendar&view=QuickEditAjax&record={$HISTORY['id']}"
															data-content="{\App\Language::translate('LBL_DELETE_RECORD_COMPLETELY',$HISTORY['type'])}">
															<span class="fas fa-trash fa-fw"></span>
														</span>
													{/if}
												</div>
											{/if}
											{if !$IS_READ_ONLY && $HISTORY['type'] eq 'Documents'}
												<div class="btn-group-vertical" role="group">
													{if \App\Privilege::isPermitted($HISTORY['type'], 'EditView', $HISTORY['id'])}
														<span class="editDefaultStatus btn-xs u-cursor-pointer js-popover-tooltip showEdit mt-1" data-url="index.php?module=Calendar&view=Delete&record={$HISTORY['id']}"
															data-content="{\App\Language::translate('LBL_EDIT',$HISTORY['type'])}" data-js="popover">
															<span class="fas fa-pencil-alt fa-fw"></span>
														</span>
													{/if}
													{if \App\Privilege::isPermitted($HISTORY['type'], 'DetailView', $HISTORY['id'])}
														<span class="editDefaultStatus btn-xs u-cursor-pointer js-popover-tooltip showEdit mt-1" data-url="index.php?module=Documents&view=Detail&record={$HISTORY['id']}"
															data-content="{\App\Language::translate('LBL_SHOW_FULL_DETAILS',$HISTORY['type'])}" data-js="popover">
															<span class="fas fa-search fa-fw"></span>
														</span>
													{/if}
													{if \App\Privilege::isPermitted($HISTORY['type'], 'DetailView', $HISTORY['id'])}
														<span class="editDefaultStatus btn-xs u-cursor-pointer text-primary js-popover-tooltip showEdit mt-1" data-url="index.php?module=Documents&view=Detail&record={$HISTORY['id']}"
															data-content="{\App\Language::translate('LBL_SHOW_FULL_DETAILS',$HISTORY['type'])}" data-js="popover">
															<span class="fas fa-download fa-fw"></span>
														</span>
													{/if}


												</div>
											{/if}

										{\App\Purifier::encodeHtml($HISTORY['body'])}
									</div>
							</div>
						</div>
					</li>
				{/foreach}
			</ul>
			{if !$IS_READ_ONLY && count($HISTORIES) eq $PAGING_MODEL->getPageLimit() && !$NO_MORE}
				<div id="moreRelatedUpdates mt-2">
					<div class="d-flex justify-content-end">
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
		<hr class="widgetHr" />
		{if !$IS_READ_ONLY && $COMMENTS_MODULE_MODEL->isPermitted('CreateView')}
			<div class="commentTitle mt-4 mb-2">
				<div class="js-add-comment-block addCommentBlock" data-js="container|remove">
					<div class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text">
								<span class="fas fa-comments"></span>
							</span>
						</div>
						<div name="commentcontent" contenteditable="true"
							 class="js-comment-content js-completions commentcontent form-control u-min-h-56px"
							 title="{\App\Language::translate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}"
							 placeholder="{\App\Language::translate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}"
							 data-js="html | tribute.js"></div>
						<div class="input-group-append">
							<button class="btn btn-success js-historyrelation-view-save-comment" type="button" data-mode="add">
								<span class="fa fa-plus"></span>
							</button>
						</div>
					</div>
				</div>
			</div>
		{/if}
	</div>
{/strip}
