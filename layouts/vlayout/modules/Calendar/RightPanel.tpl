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
<style>
	/*ul {
		list-style-type: none;
	}
	label{
		display: inline;
	}
	.my-checkbox{
		margin-right: 20px;
	}
	ul li{
		margin-bottom: 2px;
	}*/
</style>
	{if $ALL_ACTIVEUSER_LIST}
		<div class="" style="margin-left:5px; ">
			<ul  class="nav" id="calendarUserList">
				{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEUSER_LIST}
					<li value="{$OWNER_ID}" style="margin-bottom: 2px;">
						<div class="row" style="margin-right: 0px;">
							<div class="col-md-4">
								<input id="{$OWNER_ID}" data-value="{$OWNER_ID}"  class="switchBtn label" type="checkbox" {if $USER_MODEL->id eq $OWNER_ID} checked {/if} data-size="mini" data-label-width="5" data-handle-width="20"/>
							</div>
							<div class="col-md-8 marginLeftZero paddingLRZero" style=" background: White">
								<div class="col-md-11 paddingLRZero">
									<label for="{$OWNER_ID}" class="muted">{$OWNER_NAME}
								</label>
								</div>
								<div class="col-md-1 paddingLRZero">
									<span class="userCol_{$OWNER_ID} pull-right" style="width: 9px; height: 9px; margin: 4px"></span>
								</div>
								
							</div>
						</div>
					</li>
				{/foreach}
			</ul>					
		</div>
	{/if}
	{if $ALL_ACTIVEGROUP_LIST}
		<div class="" style="margin-left:5px;">
			<ul  class="nav" id="calendarGroupList">
				{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEGROUP_LIST}
					<li value="{$OWNER_ID}" style="margin-bottom: 2px;">
						<div class="row" style="margin-right: 0px;">
							<div class="col-md-4">
								{$SHIFT_USER_TITLE="LBL_SHITF_{$ITEM|upper}_SHOW"}
								<input id="{$OWNER_ID}" data-value="{$OWNER_ID}"  title="{vtranslate('LBL_SHIFT_USER_SHOW')}" class="switchBtn label" type="checkbox" data-size="mini" data-label-width="5" data-handle-width="20"/>
							</div>
							<div class="col-md-8 marginLeftZero paddingLRZero" style=" background: White">
								<div class="col-md-11 paddingLRZero">
									<label for="{$OWNER_ID}" class="muted" >{$OWNER_NAME}</label>
								</div>
								<div class="col-md-1 paddingLRZero">
									<span class="userCol_{$OWNER_ID} pull-right" style="width: 9px; height: 9px; margin: 5px"></span>
								</div>
							</div>
						</div>
					</li>
				{/foreach}
			</ul>					
		</div>
	{/if}
	{if $ACTIVITY_TYPE}
		<div class="" style="margin-left:5px;">
			<ul  class="nav" id="calendarActivityTypeList">
				{foreach item=ITEM from=$ACTIVITY_TYPE}
					<li value="{$ITEM}" style="margin-bottom: 2px;">
						<div class="row" style="margin-right: 0px;">
							<div class="col-md-4">
								{$SHIFT_ACTIVITY_TITLE="LBL_SHITF_{$ITEM|upper}_SHOW"}
								<input id="{$ITEM}" data-value="{$ITEM}" title="{vtranslate($SHIFT_ACTIVITY_TITLE)}" class="switchBtn label" type="checkbox" data-size="mini" data-label-width="5" data-handle-width="20" checked="true"/>
							</div>
							<div class="col-md-8 marginLeftZero paddingLRZero" >
								<div class="col-md-11 paddingLRZero">
									<label for="{$ITEM}" class="muted" >{vtranslate($ITEM,$MODULE)}</label>
								</div>
								<div class="col-md-1 paddingLRZero">
									<span class="listCol_{$ITEM} pull-right" style="width: 9px; height: 9px; margin: 4px"></span>
								</div>
							</div>
						</div>
					</li>
				{/foreach}
			</ul>					
		</div>
	{/if}
<script type="text/javascript">
jQuery(document).ready(function() {
	Calendar_CalendarView_Js.registerSwitches();
});
</script>
{/strip}
