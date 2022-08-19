{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-List-Field-SharedOwner -->
	{assign var=FIELD_INFO value=\App\Json::encode($FIELD_MODEL->getFieldInfo())}
	{if !isset($CURRENT_USER_ID)}
		{assign var="CURRENT_USER_ID" value=$USER_MODEL->getId()}
	{/if}
	<div class="picklistSearchField u-min-w-150pxr">
		{assign var=ASSIGNED_USER_ID value=$FIELD_MODEL->getName()}
		{if isset($SEARCH_INFO['searchValue'])}
			{assign var=SEARCH_VALUES value=explode('##', $SEARCH_INFO['searchValue'])}
		{else}
			{assign var=SEARCH_VALUES value=[]}
		{/if}
		{assign var=SEARCH_VALUES value=array_map("trim",$SEARCH_VALUES)}
		{if !empty($VIEWID) && App\Config::performance('SEARCH_SHOW_OWNER_ONLY_IN_LIST') && !\App\Config::module($FIELD_MODEL->getModuleName(), 'DISABLED_SHOW_OWNER_ONLY_IN_LIST', false)}
			{assign var=USERS_GROUP_LIST value=Vtiger_SharedOwner_UIType::getSearchViewList($MODULE, $VIEWID, $FIELD_MODEL->getFullName())}
			{assign var=ALL_ACTIVEUSER_LIST value=$USERS_GROUP_LIST['users']}
			{assign var=ALL_ACTIVEGROUP_LIST value=$USERS_GROUP_LIST['group']}
		{else}
			{assign var=ALL_ACTIVEUSER_LIST value=\App\Fields\Owner::getInstance()->getAccessibleUsers()}
			{assign var=ALL_ACTIVEGROUP_LIST value=\App\Fields\Owner::getInstance()->getAccessibleGroups()}
		{/if}
		<select class="select2noactive listSearchContributor {$ASSIGNED_USER_ID} form-control"
			name="{$ASSIGNED_USER_ID}" multiple="multiple" data-fieldinfo='{$FIELD_INFO|escape}'
			{if !empty($FIELD_MODEL->get('source_field_name'))}
				data-source-field-name="{$FIELD_MODEL->get('source_field_name')}" data-module-name="{$FIELD_MODEL->getModuleName()}"
			{/if}
			{if App\Config::performance('SEARCH_OWNERS_BY_AJAX')}
				data-ajax-search="1" data-ajax-url="index.php?module={$MODULE}&action=Fields&mode=getOwners&fieldName={$ASSIGNED_USER_ID}" data-minimum-input="{App\Config::performance('OWNER_MINIMUM_INPUT_LENGTH')}"
			{/if}
			{if !$FIELD_MODEL->isActiveSearchView()}disabled{/if}>
			{if App\Config::performance('SEARCH_OWNERS_BY_AJAX')}
				{foreach from=$SEARCH_VALUES item=OWNER_ID}
					{if !empty($OWNER_ID)}
						{assign var=OWNER_NAME value=\App\Fields\Owner::getLabel($OWNER_ID)}
						<option value="{$OWNER_ID}" data-picklistvalue="{\App\Purifier::encodeHtml($OWNER_NAME)}"
							selected="selected" data-userId="{$OWNER_ID}">
							{\App\Purifier::encodeHtml($OWNER_NAME)}
						</option>
					{/if}
				{/foreach}
			{else}
				<optgroup label="{\App\Language::translate('LBL_USERS')}">
					{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEUSER_LIST}
						<option value="{$OWNER_ID}"
							data-picklistvalue='{\App\Purifier::encodeHtml($OWNER_NAME)}' {if in_array($OWNER_ID,$SEARCH_VALUES)} selected {/if}
							data-userId="{$CURRENT_USER_ID}">
							{\App\Purifier::encodeHtml($OWNER_NAME)}
						</option>
					{/foreach}
				</optgroup>
				{if count($ALL_ACTIVEGROUP_LIST) gt 0}
					<optgroup label="{\App\Language::translate('LBL_GROUPS')}">
						{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEGROUP_LIST}
							<option value="{$OWNER_ID}"
								data-picklistvalue="{\App\Purifier::encodeHtml($OWNER_NAME)}" {if in_array(trim($OWNER_ID),$SEARCH_VALUES)} selected {/if}>
								{\App\Purifier::encodeHtml($OWNER_NAME)}
							</option>
						{/foreach}
					</optgroup>
				{/if}
			{/if}
		</select>
	</div>
	<!-- /tpl-Base-List-Field-SharedOwner -->
{/strip}
