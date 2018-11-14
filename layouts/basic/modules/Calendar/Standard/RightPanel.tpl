{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{if !empty($ALL_ACTIVEUSER_LIST)}
		<div class="rounded m-1">
			<select class="select2 form-control js-calendar__filter__select" data-cache="calendar-users" multiple="multiple"
					title="{\App\Language::translate('LBL_USERS',$MODULE)}" data-js="data | value">
				{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEUSER_LIST}
					<option class="ownerCBg_{$OWNER_ID}" value="{$OWNER_ID}"
							{if \App\User::getCurrentUserId() eq $OWNER_ID}selected {/if}>
						{$OWNER_NAME}
					</option>
				{/foreach}
			</select>
		</div>
	{/if}
	{if !empty($ALL_ACTIVEGROUP_LIST)}
		<div class="rounded m-1">
			<select class="select2 form-control js-calendar__filter__select" data-cache="calendar-groups" multiple="multiple"
					title="{\App\Language::translate('LBL_GROUPS',$MODULE)}" data-js="data | value">
				{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEGROUP_LIST}
					<option class="ownerCBg_{$OWNER_ID}" value="{$OWNER_ID}">
						{$OWNER_NAME}
					</option>
				{/foreach}
			</select>
		</div>
	{/if}
	{if !empty($ACTIVITY_TYPE)}
		<div class="rounded m-1">
			<select class="select2 form-control js-calendar__filter__select" data-cache="calendar-types" multiple="multiple"
					title="{\App\Language::translate('Activity Type',$MODULE)}" data-js="data | value">
				{foreach item=ITEM from=$ACTIVITY_TYPE}
					<option class="picklistCBr_Calendar_activitytype_{$ITEM}" value="{$ITEM}">
						{\App\Language::translate($ITEM,$MODULE)}
					</option>
				{/foreach}
			</select>
		</div>
	{/if}
{/strip}
