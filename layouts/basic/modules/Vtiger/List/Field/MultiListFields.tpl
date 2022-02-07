{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-List-Field-MultiListFields -->
	{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPicklistValues(true)}
	{assign var=FIELD_INFO value=\App\Json::encode($FIELD_MODEL->getFieldInfo())}
	{if isset($SEARCH_INFO['searchValue'])}
		{assign var=SEARCH_VALUES value=explode('##', \App\Purifier::decodeHtml($SEARCH_INFO['searchValue']))}
	{else}
		{assign var=SEARCH_VALUES value=[]}
	{/if}
	<div class="picklistSearchField">
		<select class="select2noactive listSearchContributor" name="{$FIELD_MODEL->getName()}" title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}" multiple="multiple"
			data-fieldinfo='{$FIELD_INFO|escape}'
			{if !empty($FIELD_MODEL->get('source_field_name'))}
				data-source-field-name="{$FIELD_MODEL->get('source_field_name')}" data-module-name="{$FIELD_MODEL->getModuleName()}"
			{/if}
			{if !$FIELD_MODEL->isActiveSearchView()}disabled{/if}>
			{foreach item=PICKLIST_LABEL key=PICKLIST_KEY from=$PICKLIST_VALUES}
				<option value="{\App\Purifier::encodeHtml($PICKLIST_KEY)}" {if in_array($PICKLIST_KEY,$SEARCH_VALUES) && ($PICKLIST_KEY neq "")} selected{/if}>
					{\App\Purifier::encodeHtml($PICKLIST_LABEL)}
				</option>
			{/foreach}
		</select>
	</div>
	<!-- /tpl-Base-List-Field-MultiListFields -->
{/strip}
