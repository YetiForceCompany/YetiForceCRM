{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-OSSMailView-widgets container-fluid pl-1 pr-1">
		{assign var=COUNT value=count($RECOLDLIST)}
		{foreach from=$RECOLDLIST item=ROW key=KEY}
			<div class="row{if $KEY%2 != 0} even{/if} mb-1 px-0">
				{if \App\Privilege::isPermitted($MODULE_NAME, 'DetailView', $ROW['id'])}
					<div class="col-12 mailActions d-flex justify-content-between mb-1 px-sm-3 px-2">
						<div>
							<a class="js-toggle-icon__container showMailBody btn btn-sm btn-outline-secondary mr-1" role="button" data-js="click">
									<span class="js-toggle-icon body-icon fas fa-caret-down" data-active="fa-caret-up" data-inactive="fa-caret-down" data-js="click"
										  aria-label="{\App\Language::translate('LBL_SHOW_PREVIEW_EMAIL',$MODULE_NAME)}">
									</span>
							</a>
							<div class="btn-group" role="group">
								{if \App\Privilege::isPermitted($SMODULENAME, 'RemoveRelation')}
									{if  \App\Privilege::isPermitted($MODULE_NAME, 'MoveToTrash', $ROW['id'])}
										{assign var=LINK value=Vtiger_Link_Model::getInstanceFromValues([
										'linklabel' => 'LBL_REMOVE_RELATION',
										'linkicon' => 'fas fa-unlink',
										'linkclass' => 'btn-sm btn-secondary relationDelete entityStateBtn',
										'linkdata' => ['content' => \App\Language::translate('LBL_REMOVE_RELATION'),
									'confirm' => \App\Language::translate('LBL_REMOVE_RELATION_CONFIRMATION'), 'id' => $ROW['id']
									]
									])}
										{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE_NAME) MODULE=$MODULE_NAME}
									{/if}
									{if  \App\Privilege::isPermitted($MODULE_NAME, 'Delete', $ROW['id'])}
										{assign var=LINK value=Vtiger_Link_Model::getInstanceFromValues([
										'linktype' => 'LIST_VIEW_ACTIONS_RECORD_LEFT_SIDE',
										'linklabel' => 'LBL_DELETE_RECORD_COMPLETELY',
										'linkicon' => 'fas fa-eraser',
										'dataUrl' => "index.php?module={$MODULE_NAME}&action=Delete&record={$ROW['id']}",
										'linkdata' => ['confirm' => \App\Language::translate('LBL_DELETE_RECORD_COMPLETELY_DESC')],
									'linkclass' => 'btn-sm btn-dark relationDelete entityStateBtn'
									])}
										{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE_NAME) MODULE=$MODULE_NAME}
									{/if}
								{/if}
							</div>
						</div>
						<div>
							{if App\Config::main('isActiveSendingMails') && \App\Privilege::isPermitted('OSSMail')}
								{if $PRIVILEGESMODEL->internal_mailer == 1}
									{assign var=COMPOSE_URL value=OSSMail_Module_Model::getComposeUrl($SMODULENAME, $SRECORD, 'Detail')}
									<button type="button" class="btn btn-sm btn-light sendMailBtn ml-1"
											data-url="{$COMPOSE_URL}&mid={$ROW['id']}&type=reply" data-popup="{$POPUP}">
										<span class="fas fa-reply"
											  title="{\App\Language::translate('LBL_REPLY',$MODULE_NAME)}"></span>
									</button>
									<button type="button" class="btn btn-sm btn-light sendMailBtn ml-1"
											data-url="{$COMPOSE_URL}&mid={$ROW['id']}&type=replyAll"
											data-popup="{$POPUP}">
										<span class="fas fa-reply-all"
											  title="{\App\Language::translate('LBL_REPLYALLL', $MODULE_NAME)}"></span>
									</button>
									<button type="button" class="btn btn-sm btn-light sendMailBtn ml-1"
											data-url="{$COMPOSE_URL}&mid={$ROW['id']}&type=forward"
											data-popup="{$POPUP}">
										<span class="fas fa-share"
											  title="{\App\Language::translate('LBL_FORWARD', $MODULE_NAME)}"></span>
									</button>
								{else}
									<a class="btn btn-sm btn-light ml-1" role="button"
									   href="{OSSMail_Module_Model::getExternalUrlForWidget($ROW, 'reply',$SRECORD,$SMODULENAME)}"
									   rel="noreferrer noopener">
										<span class="fas fa-reply"
											  title="{\App\Language::translate('LBL_REPLY',$MODULE_NAME)}"></span>
									</a>
									<a class="btn btn-sm btn-light ml-1" role="button"
									   href="{OSSMail_Module_Model::getExternalUrlForWidget($ROW, 'replyAll',$SRECORD,$SMODULENAME)}"
									   rel="noreferrer noopener">
										<span class="fas fa-reply-all"
											  title="{\App\Language::translate('LBL_REPLYALLL', $MODULE_NAME)}"></span>
									</a>
									<a class="btn btn-sm btn-light ml-1" role="button"
									   href="{OSSMail_Module_Model::getExternalUrlForWidget($ROW, 'forward',$SRECORD,$SMODULENAME)}"
									   rel="noreferrer noopener">
										<span class="fas fa-share"
											  title="{\App\Language::translate('LBL_FORWARD', $MODULE_NAME)}"></span>
									</a>
								{/if}
							{/if}
						</div>
					</div>
					<div class="col-12 px-sm-3 px-2">
						<hr class="mb-sm-1 mb-0">
					</div>
				{/if}
				<div class="col-12 d-flex justify-content-between px-sm-3 px-2">
					{if $ROW['type'] eq 0}
						{assign var=FIRST_LETTER_CLASS value='bgGreen'}
					{elseif $ROW['type'] eq 1}
						{assign var=FIRST_LETTER_CLASS value='bgDanger'}
					{elseif $ROW['type'] eq 2}
						{assign var=FIRST_LETTER_CLASS value='bgBlue'}
					{/if}
					<div class="d-inline-flex w-100 col-9 pr-0 pl-0 align-items-center mb-1">
						<div class="firstLetter {$FIRST_LETTER_CLASS} d-sm-block d-none mr-2">
							{$ROW['firstLetter']}
						</div>
						<div class="w-100">
							<p class="u-text-ellipsis mb-0 u-fs-13px">
								{\App\Language::translate('LBL_FROM', 'Settings:Mail')}: {$ROW['from']}
							</p>
							<p class="u-text-ellipsis mb-0 u-fs-13px">
								{\App\Language::translate('LBL_TO', 'Settings:Mail')}: {$ROW['to']}
							</p>
							<p class="font-small mb-0 text-truncate mb-0 u-fs-13px">
								{if \App\Privilege::isPermitted('OSSMailView', 'DetailView', $ROW['id'])}
									<a type="button" href="#" class="showMailModal" data-url="{$ROW['url']}">
										{\App\Language::translate('LBL_SUBJECT')}: {$ROW['subjectRaw']}
									</a>
								{elseif $ROW['type'] eq 2}
									{\App\Language::translate('LBL_SUBJECT')}: {$ROW['subjectRaw']}
								{/if}
							</p>
						</div>
					</div>
					<div class="d-inline-flex w-100 justify-content-end col-3 pr-0 pl-0">
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
						<small class="text-muted ml-1 text-truncate">
							{\App\Fields\DateTime::formatToViewDate($ROW['date'])}
						</small>
					</div>
				</div>
				<div class="col-12 px-sm-3 px-2">
					<hr class="mb-sm-1 mb-0"/>
				</div>
				<div class="col-12 px-sm-3 px-2">
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
			<p class="textAlignCenter">{\App\Language::translate('LBL_NO_MAILS',$MODULE_NAME)}</p>
		{/if}
	</div>
{/strip}
