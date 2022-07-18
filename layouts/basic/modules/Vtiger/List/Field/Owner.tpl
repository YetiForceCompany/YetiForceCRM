{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-List-Field-Owner -->
	{assign var=FIELD_INFO value=\App\Json::encode($FIELD_MODEL->getFieldInfo())}
	{assign var=FIELD_MODULE_NAME value=$FIELD_MODEL->getModuleName()}
	{assign var=ASSIGNED_USER_ID value=$FIELD_MODEL->getName()}
	{if isset($SEARCH_INFO['searchValue'])}
		{assign var=SEARCH_VALUE value=explode('##', $SEARCH_INFO['searchValue'])}
	{else}
		{assign var=SEARCH_VALUE value=[]}
	{/if}
	{assign var=SEARCH_VALUES value=array_map("trim",$SEARCH_VALUE)}
	{if !App\Config::performance('SEARCH_OWNERS_BY_AJAX')}
		{if !empty($VIEWID) && App\Config::performance('SEARCH_SHOW_OWNER_ONLY_IN_LIST') && !\App\Config::module($FIELD_MODULE_NAME, 'DISABLED_SHOW_OWNER_ONLY_IN_LIST', false)}
			{assign var=USERS_GROUP_LIST value=\App\Fields\Owner::getInstance($MODULE)->getUsersAndGroupForModuleList($VIEWID, false, $FIELD_MODEL->getFullName())}
			{assign var=ALL_ACTIVEUSER_LIST value=$USERS_GROUP_LIST['users']}
			{assign var=ALL_ACTIVEGROUP_LIST value=$USERS_GROUP_LIST['group']}
		{else}
			{assign var=ALL_ACTIVEUSER_LIST value=\App\Fields\Owner::getInstance($FIELD_MODULE_NAME)->getAccessibleUsers()}
			{if $ASSIGNED_USER_ID neq 'modifiedby'}
				{assign var=ALL_ACTIVEGROUP_LIST value=\App\Fields\Owner::getInstance($FIELD_MODULE_NAME)->getAccessibleGroups()}
			{else}
				{assign var=ALL_ACTIVEGROUP_LIST value=[]}
			{/if}
		{/if}
	{/if}
	<div class="picklistSearchField u-min-w-150pxr">
		<select class="select2noactive listSearchContributor form-control {$ASSIGNED_USER_ID}"
			title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}" name="{$ASSIGNED_USER_ID}"
			multiple="multiple"
			{if App\Config::performance('SEARCH_OWNERS_BY_AJAX')}
				data-ajax-search="1" data-ajax-url="index.php?module={$MODULE}&action=Fields&mode=getOwners&fieldName={$ASSIGNED_USER_ID}" data-minimum-input="{App\Config::performance('OWNER_MINIMUM_INPUT_LENGTH')}" {' '}
			{/if}
			data-fieldinfo='{$FIELD_INFO|escape}'
			{if !empty($FIELD_MODEL->get('source_field_name'))}
				data-source-field-name="{$FIELD_MODEL->get('source_field_name')}" data-module-name="{$FIELD_MODEL->getModuleName()}"
			{/if}
			{if !$FIELD_MODEL->isActiveSearchView()}disabled{/if}>
			{if App\Config::performance('SEARCH_OWNERS_BY_AJAX')}
				{foreach from=$SEARCH_VALUES item=OWNER_ID}
					<option value="{$OWNER_ID}" selected>{\App\Fields\Owner::getLabel($OWNER_ID)}</option>
				{/foreach}
			{else}
				{if count($ALL_ACTIVEUSER_LIST) gt 0}
					<optgroup label="{\App\Language::translate('LBL_USERS')}">
						{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEUSER_LIST}
							<option value="{$OWNER_ID}"
								data-picklistvalue="{$OWNER_NAME}" {if in_array(trim(App\Purifier::decodeHtml($OWNER_NAME)),$SEARCH_VALUES) || in_array($OWNER_ID, $SEARCH_VALUES)} selected {/if}
								data-userId="{$OWNER_ID}">
								{$OWNER_NAME}
							</option>
						{/foreach}
					</optgroup>
				{/if}
				{if count($ALL_ACTIVEGROUP_LIST) gt 0}
					<optgroup label="{\App\Language::translate('LBL_GROUPS')}">
						{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEGROUP_LIST}
							<option value="{$OWNER_ID}"
								data-picklistvalue="{$OWNER_NAME}" {if in_array(trim(App\Purifier::decodeHtml($OWNER_NAME)),$SEARCH_VALUES) || in_array($OWNER_ID, $SEARCH_VALUES)} selected {/if}>
								{$OWNER_NAME}
							</option>
						{/foreach}
					</optgroup>
				{/if}
			{/if}
		</select>
	</div>
	<!-- /tpl-Base-List-Field-Owner -->
{/strip}
