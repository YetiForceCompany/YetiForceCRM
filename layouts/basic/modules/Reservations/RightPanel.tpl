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
	{if !empty($ALL_ACTIVEGROUP_LIST) || !empty($ALL_ACTIVEUSER_LIST)}
		<div class="calendarUserList">
			<div class="row no-margin">
				<div class="col-xs-12 marginTB10">
				<select class="select2 col-xs-12" id="calendarUserList" multiple>
					<optgroup label="{vtranslate('LBL_USERS')}">
						{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEUSER_LIST}
							<option class="userCol_{$OWNER_ID} marginBottom5px" value="{$OWNER_ID}" {if $USER_MODEL->id eq $OWNER_ID} selected {/if}>{$OWNER_NAME}</option>
						{/foreach}
					</optgroup>
					<optgroup label="{vtranslate('LBL_GROUPS')}">
						{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEGROUP_LIST}
							<option class="userCol_{$OWNER_ID} marginBottom5px" value="{$OWNER_ID}">{$OWNER_NAME}</option>
						{/foreach}
					</optgroup>
				</select>
				</div>
			</div>
		</div>
	{/if}
	{if !empty($ALL_ACTIVETYPES_LIST)}
		<div class="calendarTypeList">
			<div class="row no-margin">
				<div class="col-xs-12 marginTB10">
					<select class="select2 form-control col-xs-12" id="timecontrolTypes" name="timecontrolTypes" multiple>
						{foreach key=ITEM_ID item=ITEM from=$ALL_ACTIVETYPES_LIST}
							<option class="calCol_{$ITEM} marginBottom5px" value="{$ITEM_ID}" selected>{vtranslate($ITEM,$MODULE)}</option>
						{/foreach}
					</select>
				</div>
			</div>
		</div>
	{/if}
<script type="text/javascript">
jQuery(document).ready(function() {
	Reservations_Calendar_Js.registerUserListWidget();
});
</script>
{/strip}
