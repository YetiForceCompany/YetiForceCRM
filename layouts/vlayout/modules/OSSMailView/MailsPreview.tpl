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
											{if vglobal('isActiveSendingMails')}
												<a title="{vtranslate('LBL_FORWARD','OSSMailView')}" onclick="window.open('index.php?module=OSSMail&view=compose&id={$ROW['id']}&type=forward{if $POPUP}&popup=1{/if}',{if !$POPUP}'_self'{else}'_blank', 'resizable=yes,location=no,scrollbars=yes,toolbar=no,menubar=no,status=no'{/if})" class="btn btn-sm btn-default">
													<span title="{vtranslate('LBL_FORWARD','OSSMailView')}" class="glyphicon glyphicon-share-alt"></span>
												</a>
												<a title="{vtranslate('LBL_REPLYALLL','OSSMailView')}" onclick="window.open('index.php?module=OSSMail&view=compose&id={$ROW['id']}&type=replyAll{if $POPUP}&popup=1{/if}',{if !$POPUP}'_self'{else}'_blank', 'resizable=yes,location=no,scrollbars=yes,toolbar=no,menubar=no,status=no'{/if})" class="btn btn-sm btn-default">
													<img width="14px" src="layouts/vlayout/modules/OSSMailView/previewReplyAll.png" alt="{vtranslate('LBL_REPLYALLL','OSSMailView')}" title="{vtranslate('LBL_REPLYALLL','OSSMailView')}">
												</a>
												<a title="{vtranslate('LBL_REPLY','OSSMailView')}" onclick="window.open('index.php?module=OSSMail&view=compose&id={$ROW['id']}&type=reply{if $POPUP}&popup=1{/if}',{if !$POPUP}'_self'{else}'_blank', 'resizable=yes,location=no,scrollbars=yes,toolbar=no,menubar=no,status=no'{/if})" class="btn btn-sm btn-default">
													<img width="14px" src="layouts/vlayout/modules/OSSMailView/previewReply.png" alt="{vtranslate('LBL_REPLY','OSSMailView')}" title="{vtranslate('LBL_REPLY','OSSMailView')}">
												</a>
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
												<img class="pull-right" src="layouts/vlayout/modules/OSSMailView/attachment.png" />
											{/if}
											<span class="pull-right">
												{if $ROW['type'] eq 0}
													<img src="layouts/vlayout/modules/OSSMailView/outgoing.png" />
												{elseif $ROW['type'] eq 1}
													<img src="layouts/vlayout/modules/OSSMailView/incoming.png" />
												{elseif $ROW['type'] eq 2}
													<img src="layouts/vlayout/modules/OSSMailView/internal.png" />
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
