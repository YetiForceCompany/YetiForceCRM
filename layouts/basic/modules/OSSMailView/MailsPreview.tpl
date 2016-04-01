{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{assign var=COUNT value=count($RECOLDLIST)}
	<div class="modelContainer modal fade" tabindex="-1">
		<div class="modal-dialog modal-blg">
			<div class="modal-content">
				<div class="modal-header">
					<div class="row">
						<div class="col-md-6">
							<h4 class="modal-title">{vtranslate('LBL_RECORDS_LIST','OSSMailView')}</h4>
						</div>
						<div class="col-md-3">
							<button type="button" class="btn btn-default expandAllMails">
								{vtranslate('LBL_EXPAND_ALL','OSSMailView')}
							</button>
							&nbsp;&nbsp;
							<button type="button" class="btn btn-default collapseAllMails">
								{vtranslate('LBL_COLLAPSE_ALL','OSSMailView')}
							</button>
						</div>
						<div class="col-md-3">
							<h4 class="modal-title pull-left">{vtranslate('LBL_COUNT_ALL_MAILS','OSSMailView')}: {$COUNT}</h4>
							<button type="button" class="btn btn-warning pull-right" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
					</div>
				</div>
				<div class="modal-body modalViewBody">
					<div class="mailsList">
						<div class="container-fluid">
							{foreach from=$RECOLDLIST item=ROW key=KEY}
								<div class="row{if $KEY%2 != 0} even{/if}">
									<div class="col-md-12 mailActions">
										<div class="pull-left">
											<a title="{vtranslate('LBL_SHOW_PREVIEW_EMAIL','OSSMailView')}" class="showMailBody btn btn-sm btn-default" >
												<span class="body-icon glyphicon glyphicon-triangle-bottom"></span>
											</a>&nbsp;
											<button type="button" class="btn btn-sm btn-default showMailModal" data-url="{$ROW['url']}" title="{vtranslate('LBL_SHOW_PREVIEW_EMAIL','OSSMailView')}">
												<span class="body-icon glyphicon glyphicon-search"></span>
											</button>
										</div>
										<div class="pull-right">
											{if AppConfig::main('isActiveSendingMails') && Users_Privileges_Model::isPermitted('OSSMail')}
												{if $USER_MODEL->get('internal_mailer') == 1}
													{assign var=COMPOSE_URL value=OSSMail_Module_Model::getComposeUrl($SMODULENAME, $SRECORD, 'Detail')}
													<button type="button" class="btn btn-sm btn-default sendMailBtn" data-url="{$COMPOSE_URL}&mid={$ROW['id']}&type=reply" data-popup="{$POPUP}" title="{vtranslate('LBL_REPLY','OSSMailView')}">
														<img width="14px" src="{Yeti_Layout::getLayoutFile('modules/OSSMailView/previewReply.png')}" alt="{vtranslate('LBL_REPLY','OSSMailView')}">
													</button>
													<button type="button" class="btn btn-sm btn-default sendMailBtn" data-url="{$COMPOSE_URL}&mid={$ROW['id']}&type=replyAll" data-popup="{$POPUP}" title="{vtranslate('LBL_REPLYALLL','OSSMailView')}">
														<img width="14px" src="{Yeti_Layout::getLayoutFile('modules/OSSMailView/previewReplyAll.png')}" alt="{vtranslate('LBL_REPLYALLL','OSSMailView')}">
													</button>
													<button type="button" class="btn btn-sm btn-default sendMailBtn" data-url="{$COMPOSE_URL}&mid={$ROW['id']}&type=forward" data-popup="{$POPUP}" title="{vtranslate('LBL_FORWARD','OSSMailView')}">
														<span class="glyphicon glyphicon-share-alt"></span>
													</button>
												{else}
													<a class="btn btn-sm btn-default" href="{OSSMail_Module_Model::getExternalUrlForWidget($ROW, 'reply')}" title="{vtranslate('LBL_CREATEMAIL', 'OSSMailView')}">
														<img width="14px" src="{Yeti_Layout::getLayoutFile('modules/OSSMailView/previewReply.png')}" alt="{vtranslate('LBL_REPLY','OSSMailView')}">
													</a>
													<a class="btn btn-sm btn-default" href="{OSSMail_Module_Model::getExternalUrlForWidget($ROW, 'replyAll')}" title="{vtranslate('LBL_REPLYALLL', 'OSSMailView')}">
														<img width="14px" src="{Yeti_Layout::getLayoutFile('modules/OSSMailView/previewReplyAll.png')}" alt="{vtranslate('LBL_REPLYALLL','OSSMailView')}">
													</a>
													<a class="btn btn-sm btn-default" href="{OSSMail_Module_Model::getExternalUrlForWidget($ROW, 'forward')}" title="{vtranslate('LBL_FORWARD', 'OSSMailView')}">
														<span class="glyphicon glyphicon-share-alt"></span>
													</a>
												{/if}

											{/if}
										</div>
										<div class="clearfix"></div>
										<hr/>
									</div>
									<div class="col-md-12">
										<div class="pull-left">
											<span class="firstLetter">
												{$ROW['firstLetter']}
											</span>
										</div>
										<div class="pull-right muted">
											<small title="{$ROW['date']}">
												{Vtiger_Util_Helper::formatDateDiffInStrings($ROW['date'])}
											</small>   
										</div>
										<h5 class="textOverflowEllipsis mailTitle mainFrom">
											{$ROW['from']}
										</h5>
										<div class="pull-right">
											{if $ROW['attachments'] eq 1}
												<img class="pull-right" src="{Yeti_Layout::getLayoutFile('modules/OSSMailView/attachment.png')}" />
											{/if}
											<span class="pull-right">
												{if $ROW['type'] eq 0}
													<img src="{Yeti_Layout::getLayoutFile('modules/OSSMailView/outgoing.png')}" />
												{elseif $ROW['type'] eq 1}
													<img src="{Yeti_Layout::getLayoutFile('modules/OSSMailView/incoming.png')}" />
												{elseif $ROW['type'] eq 2}
													<img src="{Yeti_Layout::getLayoutFile('modules/OSSMailView/internal.png')}" />
												{/if}
											</span>
											<span class="pull-right smalSeparator"></span>
										</div>
										<h5 class="textOverflowEllipsis mailTitle mainSubject">
											{$ROW['subject']}
										</h5>
									</div>
									<div class="col-md-12">
										<hr/>
									</div>
									<div class="col-md-12">
										<div class="mailTeaser">
											{$ROW['teaser']}
										</div>	
									</div>
									<div class="col-md-12 mailBody hide">
										<div class="mailBodyContent">{$ROW['body']}</div>
									</div>
									<div class="clearfix"></div>
								</div>
							{/foreach}
							{if $COUNT == 0}
								<p class="textAlignCenter">{vtranslate('LBL_NO_MAILS','OSSMailView')}</p>
							{/if}
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}
