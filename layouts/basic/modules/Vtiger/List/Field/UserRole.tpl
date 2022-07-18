{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-List-Field-UserRole -->
	{assign var=FIELD_INFO value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{if isset($SEARCH_INFO['searchValue'])}
		{assign var=SEARCH_VALUE value=explode('##', $SEARCH_INFO['searchValue'])}
	{else}
		{assign var=SEARCH_VALUE value=[]}
	{/if}
	{assign var=SEARCH_VALUES value=array_map("trim",$SEARCH_VALUE)}
	<div class="picklistSearchField u-min-w-150pxr">
		<select name="{$FIELD_MODEL->getFieldName()}" class="select2noactive listSearchContributor form-control"
			title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}" multiple="multiple" data-fieldinfo="{$FIELD_INFO}"
			{if App\Config::performance('SEARCH_ROLES_BY_AJAX')}
				data-ajax-search="1" data-ajax-url="index.php?module={$MODULE}&action=Fields&mode=getUserRole&fieldName={{$FIELD_MODEL->getFieldName()}}" data-minimum-input="{App\Config::performance('ROLE_MINIMUM_INPUT_LENGTH')}"
			{/if}

			{if !empty($FIELD_MODEL->get('source_field_name'))}
				data-source-field-name="{$FIELD_MODEL->get('source_field_name')}" data-module-name="{$FIELD_MODEL->getModuleName()}"
			{/if}
			{if !$FIELD_MODEL->isActiveSearchView()}disabled{/if}>
			{if App\Config::performance('SEARCH_ROLES_BY_AJAX')}
				{foreach from=$SEARCH_VALUES item=PICKLIST_VALUE}
					<option value="{\App\Purifier::encodeHtml($PICKLIST_VALUE)}" selected>
						{\App\Purifier::encodeHtml(\App\PrivilegeUtil::getRoleName($PICKLIST_VALUE))}
					</option>
				{/foreach}
			{else}
				{foreach key=PICKLIST_VALUE item=PICKLIST_NAME from=$FIELD_MODEL->getPicklistValues()}
					<option value="{\App\Purifier::encodeHtml($PICKLIST_VALUE)}" {if in_array($PICKLIST_VALUE, $SEARCH_VALUES)} selected {/if}>
						{\App\Purifier::encodeHtml($PICKLIST_NAME)}
					</option>
				{/foreach}
			{/if}
		</select>
	</div>
	<!-- /tpl-Base-List-Field-UserRole -->
{/strip}
