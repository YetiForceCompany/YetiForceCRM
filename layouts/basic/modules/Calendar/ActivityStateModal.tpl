{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div id="activityStateModal" class="modal fade modalEditStatus" tabindex="-1">
		{assign var=ID value=$RECORD->getId()}
		<div class="modal-dialog modal-md">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title"><span class="fas fa-question-circle mr-1"></span>
						{\App\Language::translate('LBL_SET_RECORD_STATUS', $MODULE_NAME)}
					</h5>
					<div class="ml-auto">
						{if $RECORD->get('link') neq '' && $PERMISSION_TO_SENDE_MAIL}
							{if $USER_MODEL->get('internal_mailer') == 1}
								{assign var=COMPOSE_URL value=OSSMail_Module_Model::getComposeUrl(\App\Record::getType($RECORD->get('link')), $RECORD->get('link'), 'Detail', 'new')}
								<a target="_blank" class="btn btn-sm btn-light mr-1" role="button"
								   href="{$COMPOSE_URL}">
								<span class="fas fa-envelope"
									  title="{\App\Language::translate('LBL_SEND_EMAIL')}"></span>
								</a>
							{else}
								{assign var=URLDATA value=OSSMail_Module_Model::getExternalUrl(\App\Record::getType($RECORD->get('link')), $RECORD->get('link'), 'Detail', 'new')}
								{if $URLDATA && $URLDATA != 'mailto:?'}
									<a class="btn btn-sm btn-light mr-1" role="button" href="{$URLDATA}">
									<span class="fas fa-envelope"
										  title="{\App\Language::translate('LBL_CREATEMAIL', 'OSSMailView')}"></span>
									</a>
								{/if}
							{/if}
						{/if}
						{if $RECORD->isEditable()}
							<a href="{$RECORD->getEditViewUrl()}" class="btn btn-sm btn-light mr-1" role="button">
							<span class="fas fa-edit js-detail-quick-edit" data-js="click"
								  title="{\App\Language::translate('LBL_EDIT',$MODULE_NAME)}"></span>
							</a>
						{/if}
						{if $RECORD->isViewable()}
							<a href="{$RECORD->getDetailViewUrl()}" class="btn btn-sm btn-light" role="button">
							<span class="fas fa-th-list js-detail-quick-edit"
								  title="{\App\Language::translate('LBL_SHOW_COMPLETE_DETAILS', $MODULE_NAME)}"></span>
							</a>
						{/if}
					</div>
					<button type="button" class="close ml-0" data-dismiss="modal"
							title="{\App\Language::translate('LBL_CLOSE')}">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				{assign var=ACTIVITYPOSTPONED value=\App\Privilege::isPermitted('Calendar', 'ActivityPostponed', $ID)}
				{assign var=ACTIVITYCANCEL value=\App\Privilege::isPermitted('Calendar', 'ActivityCancel', $ID)}
				{assign var=ACTIVITYCOMPLETE value=\App\Privilege::isPermitted('Calendar', 'ActivityComplete', $ID)}
				{assign var=ACTIVITY_STATE_LABEL value=Calendar_Module_Model::getComponentActivityStateLabel()}
				{assign var=ACTIVITY_STATE value=$RECORD->get('activitystatus')}
				{assign var=EMPTY value=!in_array($ACTIVITY_STATE, [$ACTIVITY_STATE_LABEL.cancelled,$ACTIVITY_STATE_LABEL.completed])}
				<div class="modal-body">
					{assign var=START_DATE value=$RECORD->get('date_start')}
					{assign var=START_TIME value=$RECORD->get('time_start')}
					{assign var=END_DATE value=$RECORD->get('due_date')}
					{assign var=END_TIME value=$RECORD->get('time_end')}
					<div class="form-horizontal modalSummaryValues">
						<div class="form-group row">
							<label class="col-4 col-form-label col-form-label-sm">
								{\App\Language::translate('Subject',$MODULE_NAME)}:
							</label>
							<div class="col-8 u-text-ellipsis fieldVal"
								 data-subject="{$RECORD->getDisplayValue('subject',false,false,true)}">
								{$RECORD->getDisplayValue('subject',false,false,100)}
							</div>
						</div>
						<div class="">
							<div class="form-group row">
								<label class="col-4 col-form-label col-form-label-sm">
									{\App\Language::translate('Start Date & Time',$MODULE_NAME)}:
								</label>
								<div class="col-8">
									{\App\Fields\DateTime::formatToDay("$START_DATE $START_TIME",$RECORD->get('allday'))}
								</div>
							</div>
							<div class="form-group row">
								<label class="col-4 col-form-label col-form-label-sm">
									{\App\Language::translate('Due Date',$MODULE_NAME)}:
								</label>
								<div class="col-8">
									{\App\Fields\DateTime::formatToDay("$END_DATE $END_TIME",$RECORD->get('allday'))}
								</div>
							</div>
							{if $RECORD->get('activitystatus') neq '' }
								<div class="form-group row">
									<label class="col-4 col-form-label col-form-label-sm">
										{\App\Language::translate('Status',$MODULE_NAME)}:
									</label>
									<div class="col-8">
										{$RECORD->getDisplayValue('activitystatus',false,false,true)}
									</div>
								</div>
							{/if}
							{if $RECORD->get('linkextend') neq '' }
								<div class="form-group row">
									<label class="col-4 col-form-label col-form-label-sm">
										{\App\Language::translate('FL_RELATION_EXTEND',$MODULE_NAME)}:
									</label>
									<div class="col-8 u-text-ellipsis">
										{$RECORD->getDisplayValue('linkextend',false,false,true)}
									</div>
								</div>
							{/if}
							{if $RECORD->get('link') neq '' }
								<div class="form-group row">
									<label class="col-4 col-form-label">
										{\App\Language::translate('FL_RELATION',$MODULE_NAME)}:
									</label>
									<div class="col-8 u-text-ellipsis">
										{$RECORD->getDisplayValue('link',false,false,true)}
									</div>
								</div>
							{/if}
							{if $RECORD->get('process') neq '' }
								<div class="form-group row">
									<label class="col-4 col-form-label col-form-label-sm">
										{\App\Language::translate('Process',$MODULE_NAME)}:
									</label>
									<div class="col-8 u-text-ellipsis">
										{$RECORD->getDisplayValue('process',false,false,true)}
									</div>
								</div>
							{/if}
							<hr/>
							<div class="form-group row">
								<label class="col-4 col-form-label col-form-label-sm">
									{\App\Language::translate('Description',$MODULE_NAME)}:
								</label>
								<div class="col-8">
									{if $RECORD->get('description') neq ''}
										{$RECORD->getDisplayValue('description',false,false,200)}
									{else}
										<span class="muted">{\App\Language::translate('LBL_NO_DESCRIPTION',$MODULE_NAME)}</span>
									{/if}
								</div>
							</div>
							<hr/>
							<div class="form-group row">
								<label class="col-4 col-form-label col-form-label-sm">
									{\App\Language::translate('Created By',$MODULE_NAME)}:
								</label>
								<div class="col-8 u-text-ellipsis">
									{$RECORD->getDisplayValue('created_user_id',false,false,true)}
								</div>
							</div>
							<div class="form-group row">
								<label class="col-4 col-form-label col-form-label-sm">
									{\App\Language::translate('Assigned To',$MODULE_NAME)}:
								</label>
								<div class="col-8 u-text-ellipsis">{$RECORD->getDisplayValue('assigned_user_id',false,false,true)}</div>
							</div>
							{if $RECORD->get('shownerid')}
								<div class="form-group row">
									<label class="col-4 col-form-label col-form-label-sm">
										{\App\Language::translate('Share with users',$MODULE_NAME)}:
									</label>
									<div class="col-8">{$RECORD->getDisplayValue('shownerid',false,false,true)}</div>
								</div>
							{/if}
						</div>
					</div>
				</div>
				<div class="modal-footer">
					{if $RECORD->isEditable()}
					<div class="col-12 p-0">
						<div class="float-left">
							{assign var=SHOW_QUICK_CREATE value=AppConfig::module('Calendar','SHOW_QUICK_CREATE_BY_STATUS')}
							{if $ACTIVITYCANCEL eq 'yes' && $EMPTY}
								<button type="button"

										class="mr-1 btn btn-warning {if in_array($ACTIVITY_STATE_LABEL.cancelled,$SHOW_QUICK_CREATE)}showQuickCreate{/if}"
										data-state="{$ACTIVITY_STATE_LABEL.cancelled}" data-id="{$ID}" data-type="1">
									<span class="fas fa-times mr-1"></span>
									{\App\Language::translate($ACTIVITY_STATE_LABEL.cancelled, $MODULE_NAME)}</button>
							{/if}
							{if $ACTIVITYCOMPLETE eq 'yes' && $EMPTY}
								<button type="button"

										class="mr-1 btn c-btn-done {if in_array($ACTIVITY_STATE_LABEL.completed,$SHOW_QUICK_CREATE)}showQuickCreate{/if}"
										data-state="{$ACTIVITY_STATE_LABEL.completed}" data-id="{$ID}" data-type="1">
									<span class="far fa-check-square fa-lg mr-1"></span>
									{\App\Language::translate($ACTIVITY_STATE_LABEL.completed, $MODULE_NAME)}</button>
							{/if}

							{if $ACTIVITYPOSTPONED eq 'yes' && $EMPTY}
								<button type="button" class="mr-1 btn btn-primary showQuickCreate"
										data-state="{$ACTIVITY_STATE_LABEL.postponed}" data-id="{$ID}" data-type="0">
									<span class="fas fa-angle-double-right mr-1"></span>
									{\App\Language::translate($ACTIVITY_STATE_LABEL.postponed, $MODULE_NAME)}

								</button>
							{/if}
							{if !$EMPTY}
								{\App\Language::translate('LBL_NO_AVAILABLE_ACTIONS', $MODULE_NAME)}
							{/if}
						</div>
						<div class="float-right">
							{/if}
							<a href="#" class="btn btn-danger" role="button" data-dismiss="modal">
								<span class="fas fa-times mr-1"></span>
								{\App\Language::translate('LBL_CLOSE', $MODULE_NAME)}</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	{foreach key=index item=jsModel from=$SCRIPTS}
		<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}?&v={$YETIFORCE_VERSION}"></script>
	{/foreach}
{/strip}
