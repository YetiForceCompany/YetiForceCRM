{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="container-fluid">
		{assign var=COUNT value=count($RECOLDLIST)}
		{foreach from=$RECOLDLIST item=ROW key=KEY}
			<div class="row{if $KEY%2 != 0} even{/if}">
				{if \App\Privilege::isPermitted('OSSMailView', 'DetailView', $ROW['id'])}
					<div class="col-md-12 mailActions">
						<div class="float-left">
							<a class="showMailBody btn btn-sm btn-light">
								<span class="body-icon fas fa-caret-down" role="button" title="{\App\Language::translate('LBL_SHOW_PREVIEW_EMAIL','OSSMailView')}"></span>
							</a>&nbsp;
							<button type="button" class="btn btn-sm btn-light showMailModal" data-url="{$ROW['url']}">
								<span class="body-icon fas fa-search" title="{\App\Language::translate('LBL_SHOW_PREVIEW_EMAIL', 'OSSMailView')}"></span>
							</button>
						</div>
						<div class="float-right">
							{if AppConfig::main('isActiveSendingMails') && \App\Privilege::isPermitted('OSSMail')}
								{if $PRIVILEGESMODEL->internal_mailer == 1}
									{assign var=COMPOSE_URL value=OSSMail_Module_Model::getComposeUrl($SMODULENAME, $SRECORD, 'Detail')}
									<button type="button" class="btn btn-sm btn-light sendMailBtn" data-url="{$COMPOSE_URL}&mid={$ROW['id']}&type=reply" data-popup="{$POPUP}">
										<span class="fas fa-reply" title="{\App\Language::translate('LBL_REPLY','OSSMailView')}"></span>
									</button>
									<button type="button" class="btn btn-sm btn-light sendMailBtn" data-url="{$COMPOSE_URL}&mid={$ROW['id']}&type=replyAll" data-popup="{$POPUP}">
										<span class="fas fa-reply-all"  title="{\App\Language::translate('LBL_REPLYALLL', 'OSSMailView')}"></span>
									</button>
									<button type="button" class="btn btn-sm btn-light sendMailBtn" data-url="{$COMPOSE_URL}&mid={$ROW['id']}&type=forward" data-popup="{$POPUP}">
										<span class="fas fa-share" title="{\App\Language::translate('LBL_FORWARD', 'OSSMailView')}"></span>
									</button>
								{else}
									<a class="btn btn-sm btn-light" role="button" href="{OSSMail_Module_Model::getExternalUrlForWidget($ROW, 'reply',$SRECORD,$SMODULENAME)}">
										<span class="fas fa-reply" title="{\App\Language::translate('LBL_REPLY','OSSMailView')}"></span>
									</a>
									<a class="btn btn-sm btn-light" role="button" href="{OSSMail_Module_Model::getExternalUrlForWidget($ROW, 'replyAll',$SRECORD,$SMODULENAME)}">
										<span class="fas fa-reply-all" title="{\App\Language::translate('LBL_REPLYALLL', 'OSSMailView')}"></span>
									</a>
									<a class="btn btn-sm btn-light" role="button" href="{OSSMail_Module_Model::getExternalUrlForWidget($ROW, 'forward',$SRECORD,$SMODULENAME)}">
										<span class="fas fa-share" title="{\App\Language::translate('LBL_FORWARD', 'OSSMailView')}"></span>
									</a>
								{/if}
							{/if}
						</div>
						<div class="clearfix"></div>
						<hr/>
					</div>
				{/if}
				<div class="col-md-12">
					<div class="float-left">
						{if $ROW['type'] eq 0}
							{assign var=FIRST_LETTER_CLASS value='bgGreen'}
						{elseif $ROW['type'] eq 1}
							{assign var=FIRST_LETTER_CLASS value='bgDanger'}
						{elseif $ROW['type'] eq 2}
							{assign var=FIRST_LETTER_CLASS value='bgBlue'}
						{/if}
						<span class="firstLetter {$FIRST_LETTER_CLASS}">
							{$ROW['firstLetter']}
						</span>
					</div>
					<div class="float-right muted">
						<small>
							{\App\Fields\DateTime::formatToViewDate($ROW['date'])}
						</small>   
					</div>
					<h5 class="u-text-ellipsis mailTitle mainFrom">
						{$ROW['from']}
					</h5>
					<div class="float-right">
						{if $ROW['attachments'] eq 1}
							<img class="float-right" src="{\App\Layout::getLayoutFile('modules/OSSMailView/attachment.png')}" />
						{/if}
						<span class="float-right">
							{if $ROW['type'] eq 0}
								<span class="fas fa-arrow-up text-success"></span>
							{elseif $ROW['type'] eq 1}
								<span class="fas fa-arrow-down text-danger"></span>
							{elseif $ROW['type'] eq 2}
								<span class="fas fa-retweet text-primary"></span>
							{/if}
						</span>
						<span class="float-right smalSeparator"></span>
					</div>
					<h5 class="u-text-ellipsis mailTitle mainSubject">
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
				<div class="col-md-12 mailBody d-none">
					<div class="mailBodyContent">{$ROW['body']}</div>
				</div>
				<div class="clearfix"></div>
			</div>
		{/foreach}
		{if $COUNT == 0}
			<p class="textAlignCenter">{\App\Language::translate('LBL_NO_MAILS','OSSMailView')}</p>
		{/if}
	</div>
{/strip}
