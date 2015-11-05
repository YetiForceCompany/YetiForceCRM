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
	{if $ALL_ACTIVEUSER_LIST}
		<div>
			<ul  class="nav" id="calendarUserList">
				{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEUSER_LIST}
					<li value="{$OWNER_ID}">
						<div class="row marginRightZero">
							<div class="col-xs-3">
								<input id="{$OWNER_ID}" data-value="{$OWNER_ID}"  class="switchBtn label" type="checkbox" {if $USER_MODEL->id eq $OWNER_ID} checked {/if} data-size="mini" data-label-width="5" data-on-text="{vtranslate('LBL_ON_SWITCH',$MODULE)}" data-off-text="{vtranslate('LBL_OFF_SWITCH',$MODULE)}" data-handle-width="30"/>
							</div>
							<div class="col-xs-9 paddingRightZero">
								<div class="col-xs-10 paddingLRZero">
									<label for="{$OWNER_ID}" class="muted no-margin cursorPointer">{$OWNER_NAME}
									</label>
								</div>
								<div class="col-xs-2 paddingLRZero">
									<span class="userCol_{$OWNER_ID} pull-right square9"></span>
								</div>
							</div>
						</div>
					</li>
				{/foreach}
			</ul>					
		</div>
	{/if}
	{if $ALL_ACTIVEGROUP_LIST}
		<div>
			<ul  class="nav" id="calendarGroupList">
				{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEGROUP_LIST}
					<li value="{$OWNER_ID}" >
						<div class="row marginRightZero">
							<div class="col-xs-3">
								{$SHIFT_USER_TITLE="LBL_SHITF_{$ITEM|upper}_SHOW"}
								<input id="{$OWNER_ID}" data-value="{$OWNER_ID}"  title="{vtranslate('LBL_SHIFT_USER_SHOW')}" class="switchBtn label" type="checkbox" data-size="mini" data-label-width="5" data-on-text="{vtranslate('LBL_ON_SWITCH',$MODULE)}" data-off-text="{vtranslate('LBL_OFF_SWITCH',$MODULE)}" data-handle-width="30"/>
							</div>
							<div class="col-xs-9 paddingRightZero">
								<div class="col-xs-10 paddingLRZero">
									<label for="{$OWNER_ID}" class="muted no-margin cursorPointer" >{$OWNER_NAME}</label>
								</div>
								<div class="col-xs-2 paddingLRZero">
									<span class="userCol_{$OWNER_ID} pull-right square9"></span>
								</div>
							</div>
						</div>
					</li>
				{/foreach}
			</ul>					
		</div>
	{/if}
	{if $ACTIVITY_TYPE}
		<div>
			<ul  class="nav" id="calendarActivityTypeList">
				{foreach item=ITEM from=$ACTIVITY_TYPE}
					<li value="{$ITEM}" >
						<div class="row marginRightZero">
							<div class="col-xs-3">
								{$SHIFT_ACTIVITY_TITLE="LBL_SHITF_{$ITEM|upper}_SHOW"}
								<input id="{$ITEM}" data-value="{$ITEM}" title="{vtranslate($SHIFT_ACTIVITY_TITLE)}" class="switchBtn label" type="checkbox" data-size="mini" data-label-width="5" 
									   checked="true" data-on-text="{vtranslate('LBL_ON_SWITCH',$MODULE)}" data-off-text="{vtranslate('LBL_OFF_SWITCH',$MODULE)}" data-handle-width="30"/>
							</div>
							<div class="col-xs-9 paddingRightZero" >
								<div class="col-xs-10 paddingLRZero">
									<label for="{$ITEM}" class="muted no-margin cursorPointer" >{vtranslate($ITEM,$MODULE)}</label>
								</div>
								<div class="col-xs-2 paddingLRZero">
									<span class="listCol_{$ITEM} pull-right square9"></span>
								</div>
							</div>
						</div>
					</li>
				{/foreach}
			</ul>					
		</div>
	{/if}
	<script type="text/javascript">
		jQuery(document).ready(function () {
			Calendar_CalendarView_Js.registerSwitches();
		});
	</script>
{/strip}
