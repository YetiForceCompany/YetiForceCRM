{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-List-Field-SimplePicklist -->
	{assign var=FIELD_INFO value=\App\Json::encode($FIELD_MODEL->getFieldInfo())}
	{if isset($SEARCH_INFO['searchValue'])}
		{assign var=SEARCH_VALUES value=explode('##', $SEARCH_INFO['searchValue'])}
	{else}
		{assign var=SEARCH_VALUES value=[]}
	{/if}
	<div>
		<select name="{$FIELD_MODEL->getName()}" class="select2noactive listSearchContributor form-control" title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}" {' '}
			multiple="multiple" data-fieldinfo='{$FIELD_INFO|escape}' {' '}
			{if !empty($FIELD_MODEL->get('source_field_name'))} data-source-field-name="{$FIELD_MODEL->get('source_field_name')}" data-module-name="{$FIELD_MODEL->getModuleName()}" {/if}
			{if !$FIELD_MODEL->isActiveSearchView()}disabled{/if}>
			<option value="">{\App\Language::translate('LBL_SELECT_OPTION')}</option>
			{foreach item=VALUE key=KEY from=$FIELD_MODEL->getPicklistValues()}
				<option value="{\App\Purifier::encodeHtml($KEY)}" title="{\App\Purifier::encodeHtml($VALUE['name'])}" {if in_array($KEY,$SEARCH_VALUES) && ($KEY neq "") }selected {/if}>
					{\App\Purifier::encodeHtml($VALUE['name'])}
				</option>
			{/foreach}
		</select>
	</div>
	<!-- /tpl-Base-List-Field-SimplePicklist -->
{/strip}
