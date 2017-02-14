{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{assign var=FIELD_INFO value=Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{if isset($SEARCH_INFO['searchValue'])}
		{assign var=SEARCH_VALUE value=explode(',',$SEARCH_INFO['searchValue'])}
	{else}
		{assign var=SEARCH_VALUE value=[]}
	{/if}
    {assign var=SEARCH_VALUES value=array_map("trim",$SEARCH_VALUE)}
	<div class="picklistSearchField">
		<select class="select2noactive listSearchContributor form-control" title="{\App\Language::translate($FIELD_MODEL->get('label'), $MODULE)}"  name="{$FIELD_MODEL->getFieldName()}" multiple{/strip} {strip}
				{if AppConfig::performance('SEARCH_ROLES_BY_AJAX')}
					data-ajax-search="1" data-ajax-url="index.php?module={$MODULE}&action=Fields&mode=searchValues&fld={$FIELD_MODEL->getId()}" data-minimum-input="{AppConfig::performance('ROLE_MINIMUM_INPUT_LENGTH')}"
				{/if}
				data-fieldinfo="{$FIELD_INFO}">
			{if AppConfig::performance('SEARCH_ROLES_BY_AJAX')}
				{foreach from=$SEARCH_VALUES item=PICKLIST_VALUE}
					<option value="{$PICKLIST_VALUE}" selected>{\App\PrivilegeUtil::getRoleName($PICKLIST_VALUE)}</option>
				{/foreach}
			{else}
				{foreach key=PICKLIST_VALUE item=PICKLIST_NAME from=$FIELD_MODEL->getPicklistValues()}
					<option value="{$PICKLIST_VALUE}" {if in_array($PICKLIST_VALUE, $SEARCH_VALUES)} selected {/if}>{$PICKLIST_NAME}</option>
				{/foreach}
			{/if}
		</select>
	</div>
{/strip}
