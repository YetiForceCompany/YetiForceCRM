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
			{assign var=ACTIVITYPOSTPONED value=\App\Privilege::isPermitted('Calendar', 'ActivityPostponed', $ID)}
			{assign var=ACTIVITYCANCEL value=\App\Privilege::isPermitted('Calendar', 'ActivityCancel', $ID)}
			{assign var=ACTIVITYCOMPLETE value=\App\Privilege::isPermitted('Calendar', 'ActivityComplete', $ID)}
			{assign var=ACTIVITY_STATE_LABEL value=Calendar_Module_Model::getComponentActivityStateLabel()}
			{assign var=ACTIVITY_STATE value=$RECORD->get('activitystatus')}
			{assign var=EMPTY value=!in_array($ACTIVITY_STATE, [$ACTIVITY_STATE_LABEL.cancelled,$ACTIVITY_STATE_LABEL.completed])}
			<div class="marginTop10">
				{include file=\App\Layout::getTemplatePath('ActivityStateContent.tpl', $MODULE_NAME)}
			</div>
			<div class="formActionsPanel d-none d-md-block">
				{if $RECORD->isEditable()}
					{assign var=SHOW_QUICK_CREATE value=AppConfig::module('Calendar','SHOW_QUICK_CREATE_BY_STATUS')}
					{if $ACTIVITYCANCEL eq 'yes' && $EMPTY}
						<button type="button"
								class="btn btn-warning mr-1 {if in_array($ACTIVITY_STATE_LABEL.cancelled,$SHOW_QUICK_CREATE)}showQuickCreate{/if}"
								data-state="{$ACTIVITY_STATE_LABEL.cancelled}" data-id="{$ID}"
								data-type="1" data-js="click"
								title="{\App\Language::translate($ACTIVITY_STATE_LABEL.cancelled, $MODULE_NAME)}">
							<span class="fas fa-ban"></span>
						</button>
					{/if}
					{if $ACTIVITYCOMPLETE eq 'yes' && $EMPTY}
						<button type="button"
								class="btn btn-success mr-1 {if in_array($ACTIVITY_STATE_LABEL.completed,$SHOW_QUICK_CREATE)}showQuickCreate{/if}"
								data-state="{$ACTIVITY_STATE_LABEL.completed}" data-id="{$ID}"
								data-type="1" data-js="click"
								title="{\App\Language::translate($ACTIVITY_STATE_LABEL.completed, $MODULE_NAME)}">
							<span class="far fa-check-square fa-lg"></span>
						</button>
					{/if}
					{if $ACTIVITYPOSTPONED eq 'yes' && $EMPTY}
						<button type="button" class="btn btn-primary showQuickCreate mr-1"
								data-state="{$ACTIVITY_STATE_LABEL.postponed}" data-id="{$ID}"
								data-type="0"
								data-js="click"
								title="{\App\Language::translate($ACTIVITY_STATE_LABEL.postponed, $MODULE_NAME)}">
							<span class="fas fa-angle-double-right"></span>
						</button>
					{/if}
					{if !$EMPTY}
						{\App\Language::translate('LBL_NO_AVAILABLE_ACTIONS', $MODULE_NAME)}
					{/if}
				{/if}
				{if $RECORD->get('link') neq '' && $PERMISSION_TO_SENDE_MAIL}
					{if $USER_MODEL->get('internal_mailer') == 1}
						{assign var=COMPOSE_URL value=OSSMail_Module_Model::getComposeUrl(\App\Record::getType($RECORD->get('link')), $RECORD->get('link'), 'Detail', 'new')}
						<a target="_blank" class="btn btn-default mr-1" role="button"
						   href="{$COMPOSE_URL}"
						   title="{\App\Language::translate('LBL_SEND_EMAIL')}">
							<span class="fas fa-envelope"></span>
						</a>
					{else}
						{assign var=URLDATA value=OSSMail_Module_Model::getExternalUrl(\App\Record::getType($RECORD->get('link')), $RECORD->get('link'), 'Detail', 'new')}
						{if $URLDATA && $URLDATA != 'mailto:?'}
							<a class="btn btn-default mr-1" role="button" href="{$URLDATA}"
							   title="{\App\Language::translate('LBL_CREATEMAIL', 'OSSMailView')}">
								<span class="fas fa-envelope"></span>
							</a>
						{/if}
					{/if}
				{/if}
				{if $RECORD->isEditable()}
					<a href="#" data-url="{$RECORD->getEditViewUrl()}" data-id="{$ID}"
					   class="editRecord btn btn-default mr-1"
					   title="{\App\Language::translate('LBL_EDIT',$MODULE_NAME)}">
						<span class="fas fa-edit summaryViewEdit"></span>
					</a>
				{/if}
				{if $RECORD->isViewable()}
					<a href="{$RECORD->getDetailViewUrl()}" class="btn btn-default mr-1"
					   title="{\App\Language::translate('LBL_SHOW_COMPLETE_DETAILS', $MODULE_NAME)}">
						<span class="fas fa-list summaryViewEdit"></span>
					</a>
				{/if}
				<a href="#" class="btn btn-danger summaryCloseEdit float-right"
				   title="{\App\Language::translate('LBL_CLOSE', $MODULE_NAME)}">
					<span class="fas fa-times"></span>
				</a>
			</div>
		</div>
	</div>
	{foreach key=index item=jsModel from=$SCRIPTS}
		<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}?&v={$YETIFORCE_VERSION}"></script>
	{/foreach}
{/strip}
