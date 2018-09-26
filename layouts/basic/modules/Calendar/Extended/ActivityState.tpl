{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Calendar-Extended-ActivityState js-activity-state modalEditStatus"
		 data-js="container" tabindex="-1">
		{assign var=ID value=$RECORD->getId()}
		<div>
			<div class="clearfix">
				<h6 class="boxEventTitle text-muted text-center my-1">
					{\App\Language::translate('LBL_SET_RECORD_STATUS', $MODULE_NAME)}
				</h6>
			</div>
			<div class="d-flex justify-content-center flex-wrap mb-1">
				{if $RECORD->isEditable()}
					{assign var=ACTIVITY_STATE_LABEL value=Calendar_Module_Model::getComponentActivityStateLabel()}
					{assign var=ACTIVITY_STATE value=$RECORD->get('activitystatus')}
					{assign var=EMPTY value=!in_array($ACTIVITY_STATE, [$ACTIVITY_STATE_LABEL.cancelled,$ACTIVITY_STATE_LABEL.completed])}
					{assign var=SHOW_QUICK_CREATE value=AppConfig::module('Calendar','SHOW_QUICK_CREATE_BY_STATUS')}
					{if $EMPTY && \App\Privilege::isPermitted($MODULE_NAME, 'ActivityCancel', $ID)}
						<button type="button"
								class="mr-1 mt-1 btn btn-sm btn-warning {if in_array($ACTIVITY_STATE_LABEL.cancelled,$SHOW_QUICK_CREATE)}showQuickCreate{/if}"
								data-state="{$ACTIVITY_STATE_LABEL.cancelled}" data-id="{$ID}"
								data-type="1" data-js="click"
								title="{\App\Language::translate($ACTIVITY_STATE_LABEL.cancelled, $MODULE_NAME)}">
							<span class="fas fa-ban"></span>
							<span class="ml-1">{\App\Language::translate($ACTIVITY_STATE_LABEL.cancelled, $MODULE_NAME)}</span>
						</button>
					{/if}
					{if $EMPTY && \App\Privilege::isPermitted($MODULE_NAME, 'ActivityComplete', $ID)}
						<button type="button"
								class="mr-1 mt-1 btn btn-sm c-btn-done {if in_array($ACTIVITY_STATE_LABEL.completed,$SHOW_QUICK_CREATE)}showQuickCreate{/if}"
								data-state="{$ACTIVITY_STATE_LABEL.completed}" data-id="{$ID}"
								data-type="1" data-js="click"
								title="{\App\Language::translate($ACTIVITY_STATE_LABEL.completed, $MODULE_NAME)}">
							<span class="far fa-check-square fa-lg"></span>
							<span class="ml-1">{\App\Language::translate($ACTIVITY_STATE_LABEL.completed, $MODULE_NAME)}</span>
						</button>
					{/if}
					{if $EMPTY && \App\Privilege::isPermitted($MODULE_NAME, 'ActivityPostponed', $ID)}
						<button type="button" class="mr-1 mt-1 btn btn-sm btn-primary showQuickCreate"
								data-state="{$ACTIVITY_STATE_LABEL.postponed}" data-id="{$ID}"
								data-type="0"
								data-js="click"
								title="{\App\Language::translate($ACTIVITY_STATE_LABEL.postponed, $MODULE_NAME)}">
							<span class="fas fa-angle-double-right"></span>
							<span class="ml-1">{\App\Language::translate($ACTIVITY_STATE_LABEL.postponed, $MODULE_NAME)}</span>
						</button>
					{/if}
				{/if}
			</div>
			{include file=\App\Layout::getTemplatePath('ActivityStateContent.tpl', $MODULE_NAME)}
			<div class="formActionsPanel d-none d-md-block">
				<div class="float-left">
					{if $RECORD->get('link') neq '' && $PERMISSION_TO_SENDE_MAIL}
						{if $USER_MODEL->get('internal_mailer') == 1}
							{assign var=COMPOSE_URL value=OSSMail_Module_Model::getComposeUrl(\App\Record::getType($RECORD->get('link')), $RECORD->get('link'), 'Detail', 'new')}
							<a target="_blank" class="btn btn-sm mt-1 btn-default mr-1" role="button"
							   href="{$COMPOSE_URL}"
							   title="{\App\Language::translate('LBL_SEND_EMAIL')}">
								<span class="fas fa-envelope"></span>
							</a>
						{else}
							{assign var=URLDATA value=OSSMail_Module_Model::getExternalUrl(\App\Record::getType($RECORD->get('link')), $RECORD->get('link'), 'Detail', 'new')}
							{if $URLDATA && $URLDATA != 'mailto:?'}
								<a class="btn btn-sm mt-1 btn-default mr-1" role="button" href="{$URLDATA}"
								   title="{\App\Language::translate('LBL_CREATEMAIL', 'OSSMailView')}">
									<span class="fas fa-envelope"></span>
								</a>
							{/if}
						{/if}
					{/if}
					<a class="btn btn-sm mt-1 btn-default mr-1" role="button" href="{$URLDATA}"
					   title="{\App\Language::translate('LBL_CREATEMAIL', 'OSSMailView')}">
						<span class="fas fa-envelope"></span>
					</a>
					{if $RECORD->isEditable()}
						<a href="#" data-url="{$RECORD->getEditViewUrl()}" data-id="{$ID}"
						   class="editRecord btn btn-sm mt-1 btn-default mr-1"
						   title="{\App\Language::translate('LBL_EDIT',$MODULE_NAME)}">
							<span class="fas fa-edit summaryViewEdit"></span>
							<span class="ml-1">{\App\Language::translate('LBL_EDIT',$MODULE_NAME)}</span>
						</a>
					{/if}
					{if $RECORD->isViewable()}
						<a href="{$RECORD->getDetailViewUrl()}" class="btn btn-sm mt-1 btn-default mr-1"
						   title="{\App\Language::translate('LBL_SHOW_COMPLETE_DETAILS', $MODULE_NAME)}">
							<span class="fas fa-list summaryViewEdit"></span>
							<span class="ml-1">{\App\Language::translate('LBL_SHOW_COMPLETE_DETAILS', $MODULE_NAME)}</span>
						</a>
					{/if}
				</div>
				<a href="#" class="btn btn-sm mt-1 btn-danger summaryCloseEdit float-right"
				   title="{\App\Language::translate('LBL_CLOSE', $MODULE_NAME)}">
					<span class="fas fa-times" title="{\App\Language::translate('LBL_CLOSE', $MODULE_NAME)}"></span>
					<span class="ml-1 d-none d-xxl-inline">{\App\Language::translate('LBL_CLOSE', $MODULE_NAME)}</span>
				</a>
			</div>
		</div>
	</div>
	{foreach key=index item=jsModel from=$SCRIPTS}
		<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}?&v={$YETIFORCE_VERSION}"></script>
	{/foreach}
{/strip}
