{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
{strip}
	{if !empty($ALL_ACTIVEUSER_LIST)}
		<div>
			<ul  class="nav" >
				<li >
					<div class="marginRightZero ">
						<select class="select2 form-control" multiple="multiple" id="calendarUserList" title="{\App\Language::translate('LBL_USERS',$MODULE)}">
							{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEUSER_LIST}
								<option {if $USER_MODEL->getId() eq $OWNER_ID}selected {/if}value="{$OWNER_ID}">{$OWNER_NAME}</option>
							{/foreach}
						</select>
					</div>
				</li>
			</ul>
		</div>
	{/if}
	{if !empty($ALL_ACTIVEGROUP_LIST)}
		<div>
			<ul  class="nav">
				<li>
					<div class="marginRightZero">
						<select class="select2 form-control" multiple="multiple" id="calendarGroupList" title="{\App\Language::translate('LBL_GROUPS',$MODULE)}">
							{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEGROUP_LIST}
								<option class="" value="{$OWNER_ID}">{$OWNER_NAME}</option>
							{/foreach}
						</select>
					</div>
				</li>
			</ul>
		</div>
	{/if}
	{if !empty($ACTIVITY_TYPE)}
		<div>
			<ul  class="nav" >
				<li class="">
					<select class="select2 form-control" multiple="multiple" id="calendarActivityTypeList" title="{\App\Language::translate('Activity Type',$MODULE)}">
						{foreach item=ITEM from=$ACTIVITY_TYPE}
							<option selected value="{$ITEM}">{\App\Language::translate($ITEM,$MODULE)}</option>
						{/foreach}
					</select>
				</li>
			</ul>
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
