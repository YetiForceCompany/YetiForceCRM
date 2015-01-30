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


	<div>
		<select class="widgetFilter" id="owner" name="owner" style='width:100px;margin-bottom:0px'>
			{if array_key_exists( $CURRENTUSER->getId(), $ACCESSIBLE_USERS )}
				<option value="{$CURRENTUSER->getId()}" >{vtranslate('LBL_MINE')}</option>
			{/if}
			<option value="all">{vtranslate('LBL_ALL')}</option>
			{if !empty($ACCESSIBLE_USERS)}
				<optgroup label="{vtranslate('LBL_USERS')}">
					{foreach key=OWNER_ID item=OWNER_NAME from=$ACCESSIBLE_USERS}
						{if $OWNER_ID neq {$CURRENTUSER->getId()}}
							<option value="{$OWNER_ID}">{$OWNER_NAME}</option>
						{/if}
					{/foreach}
				</optgroup>
			{/if}
			{if !empty($ACCESSIBLE_GROUPS)}
				<optgroup label="{vtranslate('LBL_GROUPS')}">
					{foreach key=OWNER_ID item=OWNER_NAME from=$ACCESSIBLE_GROUPS}
						<option value="{$OWNER_ID}">{$OWNER_NAME}</option>
					{/foreach}
				</optgroup>
			{/if}
		</select>
	</div>

