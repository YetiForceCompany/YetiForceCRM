{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
{strip}
<div id="activityStateModal" class="modal fade modalEditStatus" tabindex="-1">
	{assign var=ID value=$RECORD->get('id')}
	<div class="modal-dialog">
        <div class="modal-content">
			<div class="modal-header">
				<div class="pull-left">
					<h3 class="modal-title">{\App\Language::translate('LBL_SET_RECORD_STATUS', $MODULE_NAME)}</h3>
				</div>
				<div class="pull-right">
					{if $RECORD->get('link') neq '' && $PERMISSION_TO_SENDE_MAIL}
						{if $USER_MODEL->get('internal_mailer') == 1}
							{assign var=COMPOSE_URL value=OSSMail_Module_Model::getComposeUrl(vtlib\Functions::getCRMRecordType($RECORD->get('link')), $RECORD->get('link'), 'Detail', 'new')}
							<a target="_blank" class="btn btn-default" href="{$COMPOSE_URL}" title="{\App\Language::translate('LBL_SEND_EMAIL')}">
								<span class="glyphicon glyphicon-envelope" aria-hidden="true"></span>
							</a>
						{else}
							{assign var=URLDATA value=OSSMail_Module_Model::getExternalUrl(vtlib\Functions::getCRMRecordType($RECORD->get('link')), $RECORD->get('link'), 'Detail', 'new')}
							{if $URLDATA && $URLDATA != 'mailto:?'}
								<a class="btn btn-default" href="{$URLDATA}" title="{\App\Language::translate('LBL_CREATEMAIL', 'OSSMailView')}">
									<span class="glyphicon glyphicon-envelope" title="{\App\Language::translate('LBL_CREATEMAIL', 'OSSMailView')}"></span>
								</a>
							{/if}
						{/if}
					{/if}
					{if $RECORD->isEditable()}
						<a href="{$RECORD->getEditViewUrl()}" class="btn btn-default"><span class="glyphicon glyphicon-pencil summaryViewEdit" title="{\App\Language::translate('LBL_EDIT',$MODULE_NAME)}"></span></a>
					{/if}
					{if $RECORD->isViewable()}
						<a href="{$RECORD->getDetailViewUrl()}" class="btn btn-default"><span title="{\App\Language::translate('LBL_SHOW_COMPLETE_DETAILS', $MODULE_NAME)}" class="glyphicon glyphicon-th-list summaryViewEdit"></span></a>
					{/if}
						{*<a target="_blank" href="index.php?module=Calendar&view=Detail&record={$RECORD->getId()}"></a>*}
				</div>
				<div class="clearfix"></div>
			</div>
			{assign var=ACTIVITYPOSTPONED value=isPermitted('Calendar', 'ActivityPostponed', $ID)}
			{assign var=ACTIVITYCANCEL value=isPermitted('Calendar', 'ActivityCancel', $ID)}
			{assign var=ACTIVITYCOMPLETE value=isPermitted('Calendar', 'ActivityComplete', $ID)}
			{assign var=ACTIVITY_STATE_LABEL value=Calendar_Module_Model::getComponentActivityStateLabel()}
			{assign var=ACTIVITY_STATE value=$RECORD->get('activitystatus')}
			{assign var=EMPTY value=!in_array($ACTIVITY_STATE, [$ACTIVITY_STATE_LABEL.cancelled,$ACTIVITY_STATE_LABEL.completed])}
			<div class="modal-body">
			{assign var=START_DATE value=$RECORD->get('date_start')}
			{assign var=START_TIME value=$RECORD->get('time_start')}
			{assign var=END_DATE value=$RECORD->get('due_date')}
			{assign var=END_TIME value=$RECORD->get('time_end')}
			<div class="form-horizontal modalSummaryValues">
				<div class="form-group">
					<label class="col-sm-4 control-label">{\App\Language::translate('Subject',$MODULE_NAME)}:</label>
					<div class="col-sm-8 textOverflowEllipsis fieldVal" data-subject="{Vtiger_Util_Helper::toSafeHTML($RECORD->get('subject'))}">
						{$RECORD->get('subject')}
					</div>
				</div>
				<div class="">
					<div class="form-group">
						<label class="col-sm-4 control-label">{\App\Language::translate('Start Date & Time',$MODULE_NAME)}:</label>
						<div class="col-sm-8">
							{Vtiger_Util_Helper::formatDateTimeIntoDayString("$START_DATE $START_TIME",$RECORD->get('allday'))}
						</div>
						
					</div>
					<div class="form-group">
						<label class="col-sm-4 control-label">{\App\Language::translate('Due Date',$MODULE_NAME)}:</label>
						<div class="col-sm-8">
							{Vtiger_Util_Helper::formatDateTimeIntoDayString("$END_DATE $END_TIME",$RECORD->get('allday'))}
						</div>	
					</div>
					{if $RECORD->get('activitystatus') neq '' }
						<div class="form-group">
							<label class="col-sm-4 control-label">{\App\Language::translate('Status',$MODULE_NAME)}: </label>
							<div class="col-sm-8">
								{$RECORD->getDisplayValue('activitystatus')}
							</div>
						</div>
					{/if}
					{if $RECORD->get('link') neq '' }
						<div class="form-group">
							<label class="col-sm-4 control-label">{\App\Language::translate('FL_RELATION',$MODULE_NAME)}: </label>
							<div class="col-sm-8 textOverflowEllipsis">
								{$RECORD->getDisplayValue('link')}
							</div>
						</div>
					{/if}
					{if $RECORD->get('process') neq '' }
						<div class="form-group">
							<label class="col-sm-4 control-label">{\App\Language::translate('Process',$MODULE_NAME)}: </label>
							<div class="col-sm-8 textOverflowEllipsis">
								{$RECORD->getDisplayValue('process')}
							</div>
						</div>
					{/if}
					<hr />
					<div class="form-group">
						<label class="col-sm-4 control-label">{\App\Language::translate('Description',$MODULE_NAME)}: </label>
						<div class="col-sm-8">
						{if $RECORD->get('description') neq ''}
							{\App\Language::translate($RECORD->get('description'),$MODULE_NAME)|truncate:120:'...'}
						{else}
							<span class="muted">{\App\Language::translate('LBL_NO_DESCRIPTION',$MODULE_NAME)}</span>
						{/if}
						</div>
					</div>
					<hr />
					<div class="form-group">
						<label class="col-sm-4 control-label">{\App\Language::translate('Created By',$MODULE_NAME)}: </label>
						<div class="col-sm-8 textOverflowEllipsis">
							{vtlib\Functions::getOwnerRecordLabel( $RECORD->get('created_user_id') )}
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-4 control-label">{\App\Language::translate('Assigned To',$MODULE_NAME)}: </label>
						<div class="col-sm-8 textOverflowEllipsis">{vtlib\Functions::getOwnerRecordLabel( $RECORD->get('assigned_user_id') )}</div>
					</div>
					{if $RECORD->get('shownerid')}
						<div class="form-group">
							{assign var=FIELD_MODEL value=$RECORD->getModule()->getField('shownerid')}
							<label class="col-sm-4 control-label">{\App\Language::translate($FIELD_MODEL->get('label'),$MODULE_NAME)}: </label>
							<div class="col-sm-8">{$FIELD_MODEL->getDisplayValue($RECORD->get('shownerid'))}</div>
						</div>
					{/if}
				</div>
			</div>
			</div>
			<div class="modal-footer">
				{if $RECORD->isEditable()}
					<div class="pull-left">
						{assign var=SHOW_QUICK_CREATE value=AppConfig::module('Calendar','SHOW_QUICK_CREATE_BY_STATUS')}
						{if $ACTIVITYCANCEL eq 'yes' && $EMPTY}
							<button type="button" class="btn btn-danger {if in_array($ACTIVITY_STATE_LABEL.cancelled,$SHOW_QUICK_CREATE)}showQuickCreate{/if}" data-state="{$ACTIVITY_STATE_LABEL.cancelled}" data-id="{$ID}" data-type="1">{\App\Language::translate($ACTIVITY_STATE_LABEL.cancelled, $MODULE_NAME)}</button>
						{/if}
						{if $ACTIVITYCOMPLETE eq 'yes' && $EMPTY}
							<button type="button" class="btn btn-success {if in_array($ACTIVITY_STATE_LABEL.completed,$SHOW_QUICK_CREATE)}showQuickCreate{/if}" data-state="{$ACTIVITY_STATE_LABEL.completed}" data-id="{$ID}" data-type="1">{\App\Language::translate($ACTIVITY_STATE_LABEL.completed, $MODULE_NAME)}</button>
						{/if}
						{if $ACTIVITYPOSTPONED eq 'yes' && $EMPTY}
							<button type="button" class="btn btn-primary showQuickCreate" data-state="{$ACTIVITY_STATE_LABEL.postponed}" data-id="{$ID}" data-type="0">{\App\Language::translate($ACTIVITY_STATE_LABEL.postponed, $MODULE_NAME)}</button>
						{/if}
						{if !$EMPTY}
							{\App\Language::translate('LBL_NO_AVAILABLE_ACTIONS', $MODULE_NAME)}
						{/if}
					</div>
				{/if}
				<a href="#" class="btn btn-warning" data-dismiss="modal">{\App\Language::translate('LBL_CLOSE', $MODULE_NAME)}</a>
			</div>      
		</div>
	</div>
</div>
{foreach key=index item=jsModel from=$SCRIPTS}
	<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}?&v={$YETIFORCE_VERSION}"></script>
{/foreach}	
{/strip}
