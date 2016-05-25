{*<!--
/*+***********************************************************************************************************************************
* The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
* in compliance with the License.
* Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
* See the License for the specific language governing rights and limitations under the License.
* The Original Code is YetiForce.
* The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
* All Rights Reserved.
*************************************************************************************************************************************/
-->*}
{strip}
	{if !empty($ALL_ACTIVEUSER_LIST)}
		<div>
			<ul  class="nav" >
				<li >
					<div class="marginRightZero ">
						<select class="select2 form-control" multiple="multiple" id="calendarUserList" title="{vtranslate('LBL_USERS',$MODULE)}">
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
						<select class="select2 form-control" multiple="multiple" id="calendarGroupList" title="{vtranslate('LBL_GROUPS',$MODULE)}">
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
					<select class="select2 form-control" multiple="multiple" id="calendarActivityTypeList" title="{vtranslate('Activity Type',$MODULE)}">
						{foreach item=ITEM from=$ACTIVITY_TYPE}
							<option selected value="{$ITEM}">{vtranslate($ITEM,$MODULE)}</option>
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
