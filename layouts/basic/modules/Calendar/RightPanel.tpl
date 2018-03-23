{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{if !empty($ALL_ACTIVEUSER_LIST)}
		<div>
			<p class="rounded m-1">
				<select class="select2 form-control" multiple="multiple" id="calendarUserList" title="{\App\Language::translate('LBL_USERS',$MODULE)}">
					{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEUSER_LIST}
						<option class="ownerCBg_{$OWNER_ID}" {if $USER_MODEL->getId() eq $OWNER_ID}selected {/if}value="{$OWNER_ID}">{$OWNER_NAME}</option>
					{/foreach}
				</select>
			</p>
		</div>
	{/if}
	{if !empty($ALL_ACTIVEGROUP_LIST)}
		<div>
			<p class="rounded m-1">
				<select class="select2 form-control" multiple="multiple" id="calendarGroupList" title="{\App\Language::translate('LBL_GROUPS',$MODULE)}">
					{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEGROUP_LIST}
						<option class="ownerCBg_{$OWNER_ID}" value="{$OWNER_ID}">{$OWNER_NAME}</option>
					{/foreach}
				</select>
			</p>
		</div>
	{/if}
	{if !empty($ACTIVITY_TYPE)}
		<div>
			<p class="rounded m-1">
				<select class="select2 form-control" multiple="multiple" id="calendarActivityTypeList" title="{\App\Language::translate('Activity Type',$MODULE)}">
					{foreach item=ITEM from=$ACTIVITY_TYPE}
						<option class="picklistCBr_Calendar_activitytype_{$ITEM}" selected value="{$ITEM}">{\App\Language::translate($ITEM,$MODULE)}</option>
					{/foreach}
				</select>
			</p>
		</div>
	{/if}
	{if !empty($ACTIVITY_TYPE) || !empty($ALL_ACTIVEGROUP_LIST) || !empty($ALL_ACTIVEUSER_LIST)}
		<script type="text/javascript">
			jQuery(document).ready(function () {
				Calendar_CalendarView_Js.currentInstance.registerSelect2Event();
			});
		</script>
	{/if}
{/strip}
