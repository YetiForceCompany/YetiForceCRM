{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var=COUNT value=count($RECOLDLIST)}
	<div class="tpl-OSSMailView-MailsPreview modelContainer modal fade" tabindex="-1">
		<div class="modal-dialog modal-full">
			<div class="modal-content">
				<div class="modal-header">
					<div class="d-flex justify-content-between w-100">
						<div>
							<h5 class="modal-title">
								<span class="fas fa-search mr-1"></span>
								{\App\Language::translate('LBL_RECORDS_LIST','OSSMailView')}
							</h5>
						</div>
						<div>
							<button type="button" class="btn btn-outline-secondary expandAllMails mr-2">
								{\App\Language::translate('LBL_EXPAND_ALL','OSSMailView')}
							</button>
							<button type="button" class="btn btn-outline-secondary collapseAllMails">
								{\App\Language::translate('LBL_COLLAPSE_ALL','OSSMailView')}
							</button>
						</div>
						<div>
							<h5 class="modal-title">{\App\Language::translate('LBL_COUNT_ALL_MAILS','OSSMailView')}
								: {$COUNT}</h5>
						</div>
						<div>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
					</div>
				</div>
				<div class="modal-body modalViewBody">
					<div class="mailsList">
						<div class="container-fluid">
							{assign var=COUNT value=count($RECOLDLIST)}
							{foreach from=$RECOLDLIST item=ROW key=KEY}
								<div class="row{if $KEY%2 != 0} even{/if}">
									{if \App\Privilege::isPermitted('OSSMailView', 'DetailView', $ROW['id'])}
										<div class="col-12 mailActions d-flex justify-content-between mb-1">
											<div>
												<a class="js-toggle-icon__container showMailBody btn btn-sm btn-light mr-1" role="button" data-js="click">
													<span class="js-toggle-icon body-icon fas fa-caret-down" data-active="fa-caret-up" data-inactive="fa-caret-down" data-js="click"
														  aria-label="{\App\Language::translate('LBL_SHOW_PREVIEW_EMAIL',$MODULE_NAME)}">
													</span>
												</a>
												<button type="button" class="btn btn-sm btn-light showMailModal"
														data-url="{$ROW['url']}">
													<span class="body-icon fas fa-search"
														  title="{\App\Language::translate('LBL_SHOW_PREVIEW_EMAIL','OSSMailView')}"></span>
												</button>
											</div>
											<div>
												{if AppConfig::main('isActiveSendingMails') && \App\Privilege::isPermitted('OSSMail')}
													{if $USER_MODEL->get('internal_mailer') == 1}
														{assign var=COMPOSE_URL value=OSSMail_Module_Model::getComposeUrl($SMODULENAME, $SRECORD, 'Detail')}
														<button type="button"
																class="btn btn-sm btn-light sendMailBtn ml-1"
																data-url="{$COMPOSE_URL}&mid={$ROW['id']}&type=reply"
																data-popup="{$POPUP}">
										<span class="fas fa-reply"
											  title="{\App\Language::translate('LBL_REPLY','OSSMailView')}"></span>
														</button>
														<button type="button"
																class="btn btn-sm btn-light sendMailBtn ml-1"
																data-url="{$COMPOSE_URL}&mid={$ROW['id']}&type=replyAll"
																data-popup="{$POPUP}">
										<span class="fas fa-reply-all"
											  title="{\App\Language::translate('LBL_REPLYALLL', 'OSSMailView')}"></span>
														</button>
														<button type="button"
																class="btn btn-sm btn-light sendMailBtn ml-1"
																data-url="{$COMPOSE_URL}&mid={$ROW['id']}&type=forward"
																data-popup="{$POPUP}">
										<span class="fas fa-share"
											  title="{\App\Language::translate('LBL_FORWARD', 'OSSMailView')}"></span>
														</button>
													{else}
														<a class="btn btn-sm btn-light ml-1" role="button"
														   href="{OSSMail_Module_Model::getExternalUrlForWidget($ROW, 'reply',$SRECORD,$SMODULENAME)}">
										<span class="fas fa-reply"
											  title="{\App\Language::translate('LBL_REPLY','OSSMailView')}"></span>
														</a>
														<a class="btn btn-sm btn-light ml-1" role="button"
														   href="{OSSMail_Module_Model::getExternalUrlForWidget($ROW, 'replyAll',$SRECORD,$SMODULENAME)}">
										<span class="fas fa-reply-all"
											  title="{\App\Language::translate('LBL_REPLYALLL', 'OSSMailView')}"></span>
														</a>
														<a class="btn btn-sm btn-light ml-1" role="button"
														   href="{OSSMail_Module_Model::getExternalUrlForWidget($ROW, 'forward',$SRECORD,$SMODULENAME)}">
										<span class="fas fa-share"
											  title="{\App\Language::translate('LBL_FORWARD', 'OSSMailView')}"></span>
														</a>
													{/if}
												{/if}
											</div>
										</div>
										<div class="col-12">
											<hr class="mb-1">
										</div>
									{/if}
									<div class="col-12 d-flex justify-content-between">
										{if $ROW['type'] eq 0}
											{assign var=FIRST_LETTER_CLASS value='bgGreen'}
										{elseif $ROW['type'] eq 1}
											{assign var=FIRST_LETTER_CLASS value='bgDanger'}
										{elseif $ROW['type'] eq 2}
											{assign var=FIRST_LETTER_CLASS value='bgBlue'}
										{/if}
										<div class="d-inline-flex w-100">
											<div class="firstLetter {$FIRST_LETTER_CLASS}">
												{$ROW['firstLetter']}
											</div>
											<div>
												<h5 class="u-text-ellipsis h6 mb-0">
													{$ROW['from']}
												</h5>
												<h6 class="font-small font-weight-bold mb-0 u-text-ellipsis mb-0">
													{$ROW['subject']}
												</h6>
											</div>
										</div>
										<div class="d-inline-flex w-100 justify-content-end">
											{if $ROW['attachments'] eq 1}
												<span class="fas mt-1 fa-xs fa-paperclip mr-1"></span>
											{/if}
											{if $ROW['type'] eq 0}
												<span class="fas mt-1 fa-xs fa-arrow-up text-success"></span>
											{elseif $ROW['type'] eq 1}
												<span class="fas mt-1 fa-xs fa-arrow-down text-danger"></span>
											{elseif $ROW['type'] eq 2}
												<span class="fas mt-1 fa-xs fa-retweet text-primary"></span>
											{/if}
											<small class="text-muted ml-1">
												{\App\Fields\DateTime::formatToViewDate($ROW['date'])}
											</small>
										</div>
									</div>
									<div class="col-12">
										<hr/>
									</div>
									<div class="col-12">
										<div class="mailTeaser">
											{$ROW['teaser']}
										</div>
									</div>
									<div class="col-12 mailBody d-none">
										<div class="mailBodyContent">{$ROW['body']}</div>
									</div>
								</div>
							{/foreach}
							{if $COUNT == 0}
								<p class="textAlignCenter">{\App\Language::translate('LBL_NO_MAILS','OSSMailView')}</p>
							{/if}
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}
