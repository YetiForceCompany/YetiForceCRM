{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var="FIELD_INFO" value=\App\Json::encode($FIELD_MODEL->getFieldInfo())}
	{assign var="REFERENCE_LIST" value=$FIELD_MODEL->getReferenceList()}
	{assign var="LABEL" value=$FIELD_MODEL->getFieldInfo()}
	{if isset($SEARCH_INFO['searchValue'])}
		{assign var=SEARCH_VALUES value=explode('##', $SEARCH_INFO['searchValue'])}
	{else}
		{assign var=SEARCH_VALUE value=[]}
	{/if}
	<div class="tpl-List-Field-Reference picklistSearchField">
		<select class="select2noactive listSearchContributor {$FIELD_MODEL->getName()}" name="{$FIELD_MODEL->getName()}"
				multiple="multiple" data-fieldinfo='{$FIELD_INFO|escape}'
				{if !empty($FIELD_MODEL->get('source_field_name'))}
					data-source-field-name="{$FIELD_MODEL->get('source_field_name')}"
					data-module-name="{$FIELD_MODEL->getModuleName()}"
				{/if} data-ajax-search="1"
				data-ajax-url="index.php?module={$MODULE}&action=Fields&mode=getReference&fieldName={$FIELD_MODEL->getName()}"
				data-minimum-input="3">
			{foreach from=$SEARCH_VALUES item=ID}
				<option value="{$ID}"
						selected="selected">{\App\Purifier::encodeHtml(\App\Record::getLabel($ID))}</option>
			{/foreach}
		</select>
	</div>
{/strip}
