{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Calendar-Reminders remindersContent">
		{foreach item=RECORD from=$RECORDS}
			{assign var=START_DATE value=$RECORD->get('date_start')}
			{assign var=START_TIME value=$RECORD->get('time_start')}
			{assign var=END_DATE value=$RECORD->get('due_date')}
			{assign var=END_TIME value=$RECORD->get('time_end')}
			{assign var=RECORD_ID value=$RECORD->getId()}
			<div class="js-toggle-panel c-panel picklistCBr_Calendar_activitytype_{\App\Purifier::encodeHtml($RECORD->get('activitytype'))}" data-js="click" data-record="{$RECORD_ID}">
				<div class="card-header p-2 d-flex justify-content-between picklistCBg_Calendar_activitytype_{\App\Purifier::encodeHtml($RECORD->get('activitytype'))}">
					{assign var=ACTIVITY_TYPE value=$RECORD->get('activitytype')}
					<div class="float-left">
						<a target="_blank" href="index.php?module=Calendar&view=Detail&record={$RECORD_ID}">
							{if $ACTIVITY_TYPE eq 'Task'}
								<span class="far fa-check-square fa-lg"></span>
							{elseif $ACTIVITY_TYPE eq 'Call'}
								<span class="fas fa-phone fa-lg fa-flip-horizontal"></span>
							{else}
								<span class="fas fa-user fa-lg"></span>
							{/if}
							<span class="ml-2">{$RECORD->getDisplayValue('subject')}</span>
						</a>
					</div>
					<div class="float-right ml-1">
						<button class="btn btn-success btn-sm  showModal" data-url="index.php?module=Calendar&view=ActivityStateModal&trigger=Reminders&record={$RECORD->getId()}" data-modalid="calendar-reminder-modal">
							<span class="fas fa-check"></span>
						</button>
					</div>
				</div>
				<div class="card-body small p-2">
					<div>
						{\App\Language::translate('Start Date & Time',$MODULE_NAME)}:&nbsp;
						<strong>{\App\Fields\DateTime::formatToDay("$START_DATE $START_TIME",$RECORD->get('allday'))}</strong>
					</div>
					<div>
						{\App\Language::translate('Due Date',$MODULE_NAME)}:&nbsp;
						<strong>{\App\Fields\DateTime::formatToDay("$END_DATE $END_TIME",$RECORD->get('allday'))}</strong>
					</div>
					{if $RECORD->get('activitystatus') neq '' }
						<div>
							{\App\Language::translate('Status',$MODULE_NAME)}:&nbsp;
							<strong>{$RECORD->getDisplayValue('activitystatus')}</strong>
						</div>
					{/if}
					{if $RECORD->get('link') neq ''}
						<div>
							{\App\Language::translate('FL_RELATION',$MODULE_NAME)}:&nbsp;
							<strong>{$RECORD->getDisplayValue('link')}</strong>
							{if $PERMISSION_TO_SENDE_MAIL}
								{if \App\Mail::checkInternalMailClient()}
									{assign var=COMPOSE_URL value=OSSMail_Module_Model::getComposeUrl(\App\Record::getType($RECORD->get('link')), $RECORD->get('link'), 'Detail', 'new')}
									<a target="_blank" class="float-right" href="{$COMPOSE_URL}" title="{\App\Language::translate('LBL_SEND_EMAIL')}">
										<span class="fas fa-envelope fa-fw"></span>
									</a>
								{else}
									{assign var=URLDATA value=OSSMail_Module_Model::getExternalUrl(\App\Record::getType($RECORD->get('link')), $RECORD->get('link'), 'Detail', 'new')}
									{if $URLDATA && $URLDATA != 'mailto:?'}
										<a target="_blank" class="float-right" href="{$URLDATA}" title="{\App\Language::translate('LBL_CREATEMAIL', 'OSSMailView')}">
											<span class="fas fa-envelope fa-fw" title="{\App\Language::translate('LBL_CREATEMAIL', 'OSSMailView')}"></span>
										</a>
									{/if}
								{/if}
							{/if}
						</div>
					{/if}
					{if $RECORD->get('process') neq '' }
						<div>
							{\App\Language::translate('FL_PROCESS',$MODULE_NAME)}:&nbsp;
							<strong>{$RECORD->getDisplayValue('process')}</strong>
						</div>
					{/if}
					{if $RECORD->get('linkextend') neq ''}
						<div>
							{\App\Language::translate('FL_RELATION_EXTEND',$MODULE_NAME)}:&nbsp;
							<strong>{$RECORD->getDisplayValue('linkextend')}</strong>
							{if $PERMISSION_TO_SENDE_MAIL}
								{if \App\Mail::checkInternalMailClient()}
									{assign var=COMPOSE_URL value=OSSMail_Module_Model::getComposeUrl(\App\Record::getType($RECORD->get('linkextend')), $RECORD->get('linkextend'), 'Detail', 'new')}
									<a target="_blank" class="float-right" href="{$COMPOSE_URL}"
										rel="noreferrer noopener">
										<span class="fas fa-envelope fa-fw" title="{\App\Language::translate('LBL_SEND_EMAIL')}"></span>
									</a>
								{else}
									{assign var=URLDATA value=OSSMail_Module_Model::getExternalUrl(\App\Record::getType($RECORD->get('linkextend')), $RECORD->get('linkextend'), 'Detail', 'new')}
									{if $URLDATA && $URLDATA != 'mailto:?'}
										<a target="_blank" class="float-right" href="{$URLDATA}" title="{\App\Language::translate('LBL_CREATEMAIL', 'OSSMailView')}">
											<span class="fas fa-envelope fa-fw" title="{\App\Language::translate('LBL_CREATEMAIL', 'OSSMailView')}"></span>
										</a>
									{/if}
								{/if}
							{/if}
						</div>
					{/if}
					{if $RECORD->get('subprocess') neq '' }
						<div>
							{\App\Language::translate('FL_SUB_PROCESS',$MODULE_NAME)}:&nbsp;
							<strong>{$RECORD->getDisplayValue('subprocess')}</strong>
						</div>
					{/if}
					{if $RECORD->get('location') neq '' }
						<div>
							{\App\Language::translate('Location',$MODULE_NAME)}:&nbsp;
							<strong class="ml-1">
								{$RECORD->getDisplayValue('location')}
							</strong>
							{if App\Privilege::isPermitted('OpenStreetMap')}
								<a class="float-right" href="#" data-location="{$RECORD->getDisplayValue('location')}" onclick="Vtiger_Index_Js.showLocation(this)">
									<span class="fas fa-map-marker-alt fa-fw" title="{\App\Language::translate('LBL_MAP')}"></span>
								</a>
							{/if}
						</div>
					{/if}
					<hr />
					<div class="actionRow text-center" role="toolbar">
						<a class="btn btn-success btn-sm showModal" role="button" href="#" data-url="index.php?module=Calendar&view=ActivityStateModal&trigger=Reminders&record={$RECORD->getId()}" data-modalid="calendar-reminder-modal">
							<span class="fas fa-check" title="{\App\Language::translate('LBL_SET_RECORD_STATUS')}"></span>
						</a>
						<a class="btn btn-dark btn-sm reminderPostpone" role="button" href="#" data-time="15m" title="{\App\Language::translate('LBL_REMAIND_MINS', $MODULE_NAME)}">
							15{\App\Language::translate('LBL_M',$MODULE_NAME)}
						</a>
						<a class="btn btn-dark btn-sm reminderPostpone" role="button" href="#" data-time="30m" title="{\App\Language::translate('LBL_REMAIND_MINS', $MODULE_NAME)}">
							30{\App\Language::translate('LBL_M',$MODULE_NAME)}
						</a>
						<a class="btn btn-dark btn-sm reminderPostpone" role="button" href="#" data-time="1h" title="{\App\Language::translate('LBL_REMAIND_HOURS', $MODULE_NAME)}">
							1{\App\Language::translate('LBL_H',$MODULE_NAME)}
						</a>
						<a class="btn btn-dark btn-sm reminderPostpone" role="button" href="#" data-time="2h" title="{\App\Language::translate('LBL_REMAIND_HOURS', $MODULE_NAME)}">
							2{\App\Language::translate('LBL_H',$MODULE_NAME)}
						</a>
						<a class="btn btn-dark btn-sm reminderPostpone" role="button" href="#" data-time="6h" title="{\App\Language::translate('LBL_REMAIND_HOURS', $MODULE_NAME)}">
							6{\App\Language::translate('LBL_H',$MODULE_NAME)}
						</a>
						<a class="btn btn-dark btn-sm reminderPostpone" role="button" href="#" data-time="1d" title="{\App\Language::translate('LBL_REMAIND_DAYS', $MODULE_NAME)}">
							1{\App\Language::translate('LBL_D',$MODULE_NAME)}
						</a>
					</div>
				</div>
			</div>
		{foreachelse}
			<div class="alert alert-info">
				{\App\Language::translate('LBL_NO_CURRENT_ACTIVITIES',$MODULE_NAME)}
			</div>
		{/foreach}
	</div>
{/strip}
