{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-List-ConditionBuilder-MultiReference -->
	{assign var=FIELD_INFO value=\App\Json::encode($FIELD_MODEL->getFieldInfo())}
	{assign var="REFERENCE_LIST" value=$FIELD_MODEL->getReferenceList()}
	{assign var="LABEL" value=$FIELD_MODEL->getFieldInfo()}
	{assign var=SEARCH_VALUES value=explode('##', $VALUE)}
	<div class="picklistSearchField">
		<select class="select2 js-picklist-field js-condition-builder-value {$FIELD_MODEL->getName()}" name="{$FIELD_MODEL->getName()}"
			multiple="multiple" data-fieldinfo='{$FIELD_INFO|escape}'
			{if !empty($FIELD_MODEL->get('source_field_name'))}
				data-source-field-name="{$FIELD_MODEL->get('source_field_name')}"
				data-module-name="{$FIELD_MODEL->getModuleName()}"
			{/if} data-ajax-search="1"
			data-ajax-url="index.php?module={$FIELD_MODEL->getModuleName()}&action=Fields&mode=getReference&fieldName={$FIELD_MODEL->getName()}"
			data-minimum-input="3">
			{foreach from=$SEARCH_VALUES item=ID}
				{if \App\Record::isExists($ID)}
					{assign var="RECORD_NAME" value=\App\Record::getLabel($ID)}
					<option value="{$ID}" title="{\App\Purifier::encodeHtml($RECORD_NAME)}"
						selected="selected">{\App\Purifier::encodeHtml(\App\TextUtils::textTruncate($RECORD_NAME, 30))}</option>
				{/if}
			{/foreach}
		</select>
	</div>
	<!-- /tpl-Base-List-ConditionBuilder-MultiReference -->
{/strip}
