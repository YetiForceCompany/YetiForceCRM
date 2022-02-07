{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Calendar-ActivityStateContent -->
	{assign var=START_DATE value=$RECORD->get('date_start')}
	{assign var=START_TIME value=$RECORD->get('time_start')}
	{assign var=END_DATE value=$RECORD->get('due_date')}
	{assign var=END_TIME value=$RECORD->get('time_end')}
	<div class="o-calendar__form__wrapper js-calendar__form__wrapper form-horizontal modalSummaryValues" data-js="perfectscrollbar">
		<div class="form-group row">
			<label class="col-4 mt-2 u-font-weight-500">
				{\App\Language::translate('Subject',$MODULE_NAME)}:
			</label>
			<div class="col-8 mt-2 u-text-ellipsis fieldVal"
				data-subject="{$RECORD->getDisplayValue('subject',false,false,true)}">
				{$RECORD->getDisplayValue('subject',false,false,100)}
			</div>
		</div>
		<div class="">
			<div class="form-group row">
				<label class="col-4 mt-2 u-font-weight-500">
					{\App\Language::translate('Start Date & Time',$MODULE_NAME)}:
				</label>
				<div class="col-8 mt-2">
					{\App\Fields\DateTime::formatToDay("$START_DATE $START_TIME",$RECORD->get('allday'))}
				</div>
			</div>
			<div class="form-group row">
				<label class="col-4 mt-2 u-font-weight-500">
					{\App\Language::translate('Due Date',$MODULE_NAME)}:
				</label>
				<div class="col-8 mt-2">
					{\App\Fields\DateTime::formatToDay("$END_DATE $END_TIME",$RECORD->get('allday'))}
				</div>
			</div>
			{if $RECORD->get('activitystatus') neq '' }
				<div class="form-group row">
					<label class="col-4 mt-2 u-font-weight-500">
						{\App\Language::translate('Status',$MODULE_NAME)}:
					</label>
					<div class="col-8 mt-2">
						{$RECORD->getDisplayValue('activitystatus',false,false,true)}
					</div>
				</div>
			{/if}
			{if $RECORD->get('linkextend') neq '' }
				<div class="form-group row">
					<label class="col-4 mt-2 u-font-weight-500">
						{\App\Language::translate('FL_RELATION_EXTEND',$MODULE_NAME)}:
					</label>
					<div class="col-8 mt-2 u-text-ellipsis">
						{$RECORD->getDisplayValue('linkextend',false,false,true)}
					</div>
				</div>
			{/if}
			{if $RECORD->get('link') neq '' }
				<div class="form-group row">
					<label class="col-4 mt-2 col-form-label">
						{\App\Language::translate('FL_RELATION',$MODULE_NAME)}:
					</label>
					<div class="col-8 mt-2 u-text-ellipsis">
						{$RECORD->getDisplayValue('link',false,false,true)}
					</div>
				</div>
			{/if}
			{if $RECORD->get('process') neq '' }
				<div class="form-group row">
					<label class="col-4 mt-2 u-font-weight-500">
						{\App\Language::translate('Process',$MODULE_NAME)}:
					</label>
					<div class="col-8 mt-2 u-text-ellipsis">
						{$RECORD->getDisplayValue('process',false,false,true)}
					</div>
				</div>
			{/if}
			{if $RECORD->get('meeting_url') neq '' }
				<div class="form-group row">
					<label class="col-4 mt-2 u-font-weight-500">
						{\App\Language::translate($RECORD->getField('meeting_url')->getFieldLabel(),$MODULE_NAME)}:
					</label>
					<div class="col-8 mt-2 u-text-ellipsis">
						{$RECORD->getDisplayValue('meeting_url',false,false,true)}
					</div>
				</div>
			{/if}
			<hr />
			<div class="form-group row">
				<label class="col-4 mt-2 u-font-weight-500">
					{\App\Language::translate('Description',$MODULE_NAME)}:
				</label>
				<div class="col-8 mt-2">
					{if $RECORD->get('description') neq ''}
						{$RECORD->getDisplayValue('description',false,false,200)}
					{else}
						<span class="muted">{\App\Language::translate('LBL_NO_DESCRIPTION',$MODULE_NAME)}</span>
					{/if}
				</div>
			</div>
			<hr />
			<div class="form-group row">
				<label class="col-4 mt-2 u-font-weight-500">
					{\App\Language::translate('Created By',$MODULE_NAME)}:
				</label>
				<div class="col-8 mt-2 u-text-ellipsis">
					{$RECORD->getDisplayValue('created_user_id',false,false,true)}
				</div>
			</div>
			<div class="form-group row">
				<label class="col-4 mt-2 u-font-weight-500">
					{\App\Language::translate('Assigned To',$MODULE_NAME)}:
				</label>
				<div class="col-8 mt-2 u-text-ellipsis">{$RECORD->getDisplayValue('assigned_user_id',false,false,true)}</div>
			</div>
			{if $RECORD->get('shownerid')}
				<div class="form-group row">
					<label class="col-4 mt-2 u-font-weight-500">
						{\App\Language::translate('Share with users',$MODULE_NAME)}:
					</label>
					<div class="col-8 mt-2">{$RECORD->getDisplayValue('shownerid',false,false,true)}</div>
				</div>
			{/if}
		</div>
	</div>
	<!-- /tpl-Calendar-ActivityStateContent -->
{/strip}
