{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
    {assign var="FIELD_INFO" value=Zend_Json::encode($FIELD_MODEL->getFieldInfo())}
    <div class="picklistSearchField">
		{assign var=ASSIGNED_USER_ID value=$FIELD_MODEL->get('name')}
		{assign var=ALL_ACTIVEUSER_LIST value=$USER_MODEL->getAccessibleUsers()}
		{assign var=SEARCH_VALUES value=explode(',',$SEARCH_INFO['searchValue'])}
		{assign var=SEARCH_VALUES value=array_map("trim",$SEARCH_VALUES)}

		{if $ASSIGNED_USER_ID neq 'modifiedby'}
			{assign var=ALL_ACTIVEGROUP_LIST value=$USER_MODEL->getAccessibleGroups()}
		{else}
			{assign var=ALL_ACTIVEGROUP_LIST value=array()}
		{/if}

		{assign var=ACCESSIBLE_USER_LIST value=$USER_MODEL->getAccessibleUsersForModule($MODULE)}
		{assign var=ACCESSIBLE_GROUP_LIST value=$USER_MODEL->getAccessibleGroupForModule($MODULE)}

		<select id="{$ASSIGNED_USER_ID}" class="select2noactive listSearchContributor {$ASSIGNED_USER_ID}"  name="{$ASSIGNED_USER_ID}" multiple data-fieldinfo='{$FIELD_INFO|escape}'>
			<optgroup label="{vtranslate('LBL_USERS')}">
				{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEUSER_LIST}
                    <option value="{$OWNER_ID}" data-picklistvalue= '{$OWNER_NAME}' {if in_array($OWNER_ID,$SEARCH_VALUES)} selected {/if}
							{if array_key_exists($OWNER_ID, $ACCESSIBLE_USER_LIST)} data-recordaccess=true {else} data-recordaccess=false {/if}
							data-userId="{$CURRENT_USER_ID}">
						{$OWNER_NAME}
                    </option>
				{/foreach}
			</optgroup>
			{if count($ALL_ACTIVEGROUP_LIST) gt 0}
				<optgroup label="{vtranslate('LBL_GROUPS')}">
					{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEGROUP_LIST}
						<option value="{$OWNER_ID}" data-picklistvalue="{$OWNER_NAME}" {if in_array(trim($OWNER_ID),$SEARCH_VALUES)} selected {/if}
								{if array_key_exists($OWNER_ID, $ACCESSIBLE_GROUP_LIST)} data-recordaccess=true {else} data-recordaccess=false {/if} >
							{$OWNER_NAME}
						</option>
					{/foreach}
				</optgroup>
			{/if}
		</select>
    </div>
{/strip}
