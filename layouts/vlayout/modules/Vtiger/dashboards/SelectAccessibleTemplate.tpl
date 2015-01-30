{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is:  vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
  *
 ********************************************************************************/
-->*}


	<div>
		<select class="widgetFilter" id="owner" name="owner" style='width:100px;margin-bottom:0px'>
			<option value="{$CURRENTUSER->getId()}" >{vtranslate('LBL_MINE')}</option>
			<option value="all">{vtranslate('LBL_ALL')}</option>
			{assign var=ALL_ACTIVEUSER_LIST value=$CURRENTUSER->getAccessibleUsers()}
			{if count($ALL_ACTIVEUSER_LIST) gt 1}
				<optgroup label="{vtranslate('LBL_USERS')}">
					{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEUSER_LIST}
						{if $OWNER_ID neq {$CURRENTUSER->getId()}}
							<option value="{$OWNER_ID}">{$OWNER_NAME}</option>
						{/if}
					{/foreach}
				</optgroup>
			{/if}
			{assign var=ALL_ACTIVEGROUP_LIST value=$CURRENTUSER->getAccessibleGroups()}
			{if !empty($ALL_ACTIVEGROUP_LIST)}
				<optgroup label="{vtranslate('LBL_GROUPS')}">
					{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEGROUP_LIST}
						<option value="{$OWNER_ID}">{$OWNER_NAME}</option>
					{/foreach}
				</optgroup>
			{/if}
		</select>
	</div>

