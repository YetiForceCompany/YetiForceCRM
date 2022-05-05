{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var=FIELD_INFO value=\App\Json::encode($FIELD_MODEL->getFieldInfo())}
	{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPicklistValues()}
	{if isset($SEARCH_INFO['searchValue'])}
		{assign var=SEARCH_VALUES value=explode('##', $SEARCH_INFO['searchValue'])}
	{else}
		{assign var=SEARCH_VALUES value=[]}
	{/if}
	<div class="tpl-List-Field-Country">
		<select name="{$FIELD_MODEL->getName()}" class="select2noactive listSearchContributor form-control" title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}" multiple="multiple" data-fieldinfo='{$FIELD_INFO|escape}' data-allow-clear="true"
			{if !empty($FIELD_MODEL->get('source_field_name'))}
				data-source-field-name="{$FIELD_MODEL->get('source_field_name')}" data-module-name="{$FIELD_MODEL->getModuleName()}"
			{/if}
			{if !$FIELD_MODEL->isActiveSearchView()}disabled{/if}>
			<option value="">{\App\Language::translate('LBL_SELECT_OPTION','Vtiger')}</option>
			{foreach item=PICKLIST_VALUE key=KEY from=$PICKLIST_VALUES}
				<option value="{\App\Purifier::encodeHtml($KEY)}" {if in_array($KEY,$SEARCH_VALUES) && ($KEY neq "")}selected{/if}>
					{\App\Purifier::encodeHtml($PICKLIST_VALUE)}
				</option>
			{/foreach}
		</select>
	</div>
{/strip}
