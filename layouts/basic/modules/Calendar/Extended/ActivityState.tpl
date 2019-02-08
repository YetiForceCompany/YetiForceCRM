{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Calendar-Extended-ActivityState js-activity-state modalEditStatus"
		 data-js="container" tabindex="-1">
		{assign var=ID value=$RECORD->getId()}
		<div class="o-calendar__form w-100 d-flex flex-column">
			<h6 class="boxEventTitle text-muted text-center my-1">
				{\App\Language::translate('LBL_SET_RECORD_STATUS', $MODULE_NAME)}
			</h6>
			{include file=\App\Layout::getTemplatePath('Extended/ActivityButtons.tpl', $MODULE_NAME)}
			{include file=\App\Layout::getTemplatePath('ActivityStateContent.tpl', $MODULE_NAME)}
			<div class="o-calendar__form__actions">
				<div class="d-flex flex-wrap">
					{if $RECORD->get('link') neq '' && $PERMISSION_TO_SENDE_MAIL}
						{if $USER_MODEL->get('internal_mailer') == 1}
							{assign var=COMPOSE_URL value=OSSMail_Module_Model::getComposeUrl(\App\Record::getType($RECORD->get('link')), $RECORD->get('link'), 'Detail', 'new')}
							<a target="_blank" class="btn mt-1 btn-default mr-1" role="button"
							   href="{$COMPOSE_URL}" rel="noreferrer noopener"
							   title="{\App\Language::translate('LBL_SEND_EMAIL')}">
								<span class="fas fa-envelope"></span>
							</a>
						{else}
							{assign var=URLDATA value=OSSMail_Module_Model::getExternalUrl(\App\Record::getType($RECORD->get('link')), $RECORD->get('link'), 'Detail', 'new')}
							{if $URLDATA && $URLDATA != 'mailto:?'}
								<a class="btn mt-1 btn-default mr-1" role="button" href="{$URLDATA}"
								   title="{\App\Language::translate('LBL_CREATEMAIL', 'OSSMailView')}">
									<span class="fas fa-envelope"></span>
								</a>
							{/if}
						{/if}
					{/if}
					{if $RECORD->isEditable()}
						<a href="#" data-url="{$RECORD->getEditViewUrl()}" data-id="{$ID}"
						   class="editRecord btn mt-1 btn-default mr-1"
						   title="{\App\Language::translate('LBL_EDIT',$MODULE_NAME)}">
							<span class="fas fa-edit summaryViewEdit"></span>
							<span class="ml-1">{\App\Language::translate('LBL_EDIT',$MODULE_NAME)}</span>
						</a>
					{/if}
					{if $RECORD->isViewable()}
						<a href="{$RECORD->getDetailViewUrl()}" class="btn mt-1 btn-default mr-1"
						   title="{\App\Language::translate('LBL_SHOW_COMPLETE_DETAILS', $MODULE_NAME)}">
							<span class="fas fa-list summaryViewEdit"></span>
							<span class="ml-1">{\App\Language::translate('LBL_SHOW_COMPLETE_DETAILS', $MODULE_NAME)}</span>
						</a>
					{/if}
					<a href="#" class="btn mt-1 btn-danger js-summary-close-edit ml-auto"
					   title="{\App\Language::translate('LBL_CLOSE', $MODULE_NAME)}">
						<span class="fas fa-times" title="{\App\Language::translate('LBL_CLOSE', $MODULE_NAME)}"></span>
						<span class="ml-1 d-none{if $RECORD->get('link') neq '' && $PERMISSION_TO_SENDE_MAIL} d-xl-inline{else} d-xxl-inline{/if}">{\App\Language::translate('LBL_CLOSE', $MODULE_NAME)}</span>
					</a>
				</div>
			</div>
		</div>
	</div>
	{foreach key=index item=jsModel from=$SCRIPTS}
		<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}?&v={$YETIFORCE_VERSION}"></script>
	{/foreach}
{/strip}
