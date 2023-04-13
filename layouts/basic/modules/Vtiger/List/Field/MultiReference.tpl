{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-List-Field-MultiReference -->
	{assign var=FIELD_INFO value=\App\Json::encode($FIELD_MODEL->getFieldInfo())}
	{assign var=REFERENCE_LIST value=current($FIELD_MODEL->getReferenceList())}
	{if isset($SEARCH_INFO['searchValue'])}
		{assign var=SEARCH_VALUES value=explode('##', $SEARCH_INFO['searchValue'])}
	{else}
		{assign var=SEARCH_VALUES value=[]}
	{/if}
	<div class="picklistSearchField">
		<select class="select2noactive listSearchContributor {$FIELD_MODEL->getName()}" name="{$FIELD_MODEL->getName()}"
			multiple="multiple" data-fieldinfo='{$FIELD_INFO|escape}'
			{if !empty($FIELD_MODEL->get('source_field_name'))}
				data-source-field-name="{$FIELD_MODEL->get('source_field_name')}" data-module-name="{$FIELD_MODEL->getModuleName()}"
			{/if} data-ajax-search="1"
			data-ajax-url="index.php?module={$FIELD_MODEL->getModuleName()}&action=Fields&mode=getReference&fieldName={$FIELD_MODEL->getName()}"
			data-minimum-input="3"
			{if !$FIELD_MODEL->isActiveSearchView()}disabled{/if}>
			{foreach from=$SEARCH_VALUES item=ID}
				{if $REFERENCE_LIST ==='Users'}
					{assign var=RECORD_NAME value=\App\Fields\Owner::getLabel($ID)}
				{else}
					{assign var=RECORD_NAME value=\App\Record::getLabel($ID)}
				{/if}
				<option value="{$ID}" title="{\App\Purifier::encodeHtml($RECORD_NAME)}"
					selected="selected">{\App\Purifier::encodeHtml(\App\TextUtils::textTruncate($RECORD_NAME, 30))}</option>
			{/foreach}
		</select>
	</div>
	<!-- /tpl-Base-List-Field-MultiReference -->
{/strip}
