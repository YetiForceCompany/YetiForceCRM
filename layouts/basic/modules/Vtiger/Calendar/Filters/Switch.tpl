{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Calendar-Filters-Switch -->
	{if \App\Config::module('Calendar', 'HIDDEN_DAYS_IN_CALENDAR_VIEW')}
		{assign var=HIDDEN_DAYS value=$HISTORY_PARAMS eq '' || !isset($HISTORY_PARAMS['hiddenDays']) || !empty($HISTORY_PARAMS['hiddenDays'])}
		<div class="btn-group btn-group-toggle mt-0 js-switch js-switch--switchingDays c-calendar-switch" data-toggle="buttons">
			<label class="btn btn-outline-primary c-calendar-switch__button js-switch--label-on{if $HIDDEN_DAYS} active{/if}">
				<input type="radio" name="options" data-on-text="{\App\Language::translate('LBL_WORK_DAYS', $MODULE_NAME)}" id="option1" autocomplete="off" data-val="workDays" {if $HIDDEN_DAYS} checked{/if}>
				{\App\Language::translate('LBL_WORK_DAYS', $MODULE_NAME)}
			</label>
			<label class="btn btn-outline-primary c-calendar-switch__button js-switch--label-off{if !$HIDDEN_DAYS} active{/if}">
				<input type="radio" name="options" id="option2" data-off-text="{\App\Language::translate('LBL_ALL', $MODULE_NAME)}" autocomplete="off" data-val="all" {if !$HIDDEN_DAYS} checked{/if}>
				{\App\Language::translate('LBL_ALL', $MODULE_NAME)}
			</label>
		</div>
	{/if}
	{if !empty($SHOW_TYPE)}
		{assign var=IS_TIME_CURRENT value=empty($HISTORY_PARAMS['time']) || $HISTORY_PARAMS['time'] eq 'current'}
		<div class="btn-group btn-group-toggle mt-0 ml-3 js-switch js-switch--showType c-calendar-switch" data-toggle="buttons">
			<label class="btn btn-outline-primary c-calendar-switch__button js-switch--label-on{if $IS_TIME_CURRENT} active{/if}">
				<input type="radio" name="options1" data-on-text="{\App\Language::translate('LBL_FILTER', $MODULE_NAME)}" autocomplete="off" data-val="current" {if $IS_TIME_CURRENT} checked{/if}>
				{\App\Language::translate('LBL_TO_REALIZE', $MODULE_NAME)}
			</label>
			<label class="btn btn-outline-primary c-calendar-switch__button js-switch--label-off{if !$IS_TIME_CURRENT} active{/if}">
				<input type="radio" name="options1" data-off-text="{\App\Language::translate('LBL_HISTORY', $MODULE_NAME)}" autocomplete="off" data-val="history" {if !$IS_TIME_CURRENT} checked{/if}>
				{\App\Language::translate('LBL_HISTORY', $MODULE_NAME)}
			</label>
		</div>
	{/if}
	<!-- tpl-Base-Calendar-Filters-Switch -->
{/strip}
