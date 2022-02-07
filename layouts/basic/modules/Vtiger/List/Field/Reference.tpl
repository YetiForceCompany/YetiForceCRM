{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-List-Field-Reference -->
	{assign var=FIELD_INFO value=\App\Json::encode($FIELD_MODEL->getFieldInfo())}
	{if isset($SEARCH_INFO['searchValue'])}
		{assign var=SEARCH_VALUES value=explode('##', $SEARCH_INFO['searchValue'])}
	{else}
		{assign var=SEARCH_VALUES value=[]}
	{/if}
	<div class="picklistSearchField">
		<select name="{$FIELD_MODEL->getName()}" class="select2noactive listSearchContributor {$FIELD_MODEL->getName()}" multiple="multiple" data-ajax-search="1"
			data-minimum-input="3" data-fieldinfo='{$FIELD_INFO|escape}'
			{if !empty($FIELD_MODEL->get('source_field_name'))}
				data-source-field-name="{$FIELD_MODEL->get('source_field_name')}" data-module-name="{$FIELD_MODEL->getModuleName()}"
				data-ajax-url="index.php?module={$FIELD_MODEL->getModuleName()}&action=Fields&mode=getReference&fieldName={$FIELD_MODEL->getName()}"
			{elseif $FIELD_MODEL->get('relationId')}
				data-ajax-url="index.php?module={$FIELD_MODEL->getModuleName()}&action=Fields&mode=getReference&relationId={$FIELD_MODEL->get('relationId')}"
			{else}
				data-ajax-url="index.php?module={$MODULE}&action=Fields&mode=getReference&fieldName={$FIELD_MODEL->getName()}"
			{/if}
			{if !$FIELD_MODEL->isActiveSearchView()}disabled{/if}>
			{foreach from=$SEARCH_VALUES item=ID}
				<option value="{$ID}" selected="selected">{\App\Purifier::encodeHtml(\App\Record::getLabel($ID))}</option>
			{/foreach}
		</select>
	</div>
	<!-- /tpl-Base-List-Field-Reference -->
{/strip}
