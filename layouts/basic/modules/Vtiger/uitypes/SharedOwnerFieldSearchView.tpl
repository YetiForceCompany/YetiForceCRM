{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
    {assign var="FIELD_INFO" value=\App\Json::encode($FIELD_MODEL->getFieldInfo())}
    <div class="picklistSearchField">
		{assign var=ASSIGNED_USER_ID value=$FIELD_MODEL->get('name')}
		{assign var=SEARCH_VALUES value=explode(',',$SEARCH_INFO['searchValue'])}
		{assign var=SEARCH_VALUES value=array_map("trim",$SEARCH_VALUES)}
		{if $VIEWID && AppConfig::performance('SEARCH_SHOW_OWNER_ONLY_IN_LIST')}
			{assign var=USERS_GROUP_LIST value=Vtiger_SharedOwner_UIType::getSearchViewList($MODULE, $VIEWID)}
			{assign var=ALL_ACTIVEUSER_LIST value=$USERS_GROUP_LIST['users']}
			{assign var=ALL_ACTIVEGROUP_LIST value=$USERS_GROUP_LIST['group']}
		{else}
			{assign var=ALL_ACTIVEUSER_LIST value=\App\Fields\Owner::getInstance()->getAccessibleUsers()}
			{assign var=ALL_ACTIVEGROUP_LIST value=\App\Fields\Owner::getInstance()->getAccessibleGroups()}
		{/if}
		<select id="{$ASSIGNED_USER_ID}" class="select2noactive listSearchContributor {$ASSIGNED_USER_ID}"  name="{$ASSIGNED_USER_ID}" multiple data-fieldinfo='{$FIELD_INFO|escape}' 
				{if AppConfig::performance('SEARCH_OWNERS_BY_AJAX')}
					data-ajax-search="1" data-ajax-url="index.php?module={$MODULE}&action=Fields&mode=getOwners&type=Edit" data-minimum-input="{AppConfig::performance('OWNER_MINIMUM_INPUT_LENGTH')}"
				{/if}>
			{if AppConfig::performance('SEARCH_OWNERS_BY_AJAX')} 
				{foreach from=$SEARCH_VALUES item=OWNER_ID}
					{if !empty($OWNER_ID)}
						{assign var=OWNER_NAME value=vtlib\Functions::getOwnerRecordLabel($OWNER_ID)}
						<option value="{$OWNER_ID}" data-picklistvalue="{$OWNER_NAME}" selected="selected" data-userId="{$OWNER_ID}">
							{$OWNER_NAME}
						</option>
					{/if}
				{/foreach}
			{else}
				<optgroup label="{vtranslate('LBL_USERS')}">
					{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEUSER_LIST}
						<option value="{$OWNER_ID}" data-picklistvalue= '{$OWNER_NAME}' {if in_array($OWNER_ID,$SEARCH_VALUES)} selected {/if}
								data-userId="{$CURRENT_USER_ID}">
							{$OWNER_NAME}
						</option>
					{/foreach}
				</optgroup>
				{if count($ALL_ACTIVEGROUP_LIST) gt 0}
					<optgroup label="{vtranslate('LBL_GROUPS')}">
						{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEGROUP_LIST}
							<option value="{$OWNER_ID}" data-picklistvalue="{$OWNER_NAME}" {if in_array(trim($OWNER_ID),$SEARCH_VALUES)} selected {/if}>
								{$OWNER_NAME}
							</option>
						{/foreach}
					</optgroup>
				{/if}
			{/if}			
		</select>
    </div>
{/strip}
