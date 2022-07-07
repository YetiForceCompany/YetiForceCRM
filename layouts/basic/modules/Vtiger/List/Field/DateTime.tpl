{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-List-Field-DateTime -->
	{assign var=FIELD_INFO value=\App\Json::encode($FIELD_MODEL->getFieldInfo())}
	{assign var="dateFormat" value=$USER_MODEL->get('date_format')}
	{if isset($SEARCH_INFO['searchValue'])}
		{assign var=SEARCH_VALUES value=$SEARCH_INFO['searchValue']}
	{else}
		{assign var=SEARCH_VALUES value=''}
	{/if}
	<div class="picklistSearchField u-min-w-150pxr">
		<input name="{$FIELD_MODEL->getName()}" class="listSearchContributor dateTimePickerField form-control datepicker"
			title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $FIELD_MODEL->getModule()->getName())}"
			type="text" value="{$SEARCH_VALUES}" data-date-format="{$dateFormat}" data-calendar-type="range"
			data-fieldinfo='{$FIELD_INFO|escape}'
			{if !empty($FIELD_MODEL->get('source_field_name'))}
				data-source-field-name="{$FIELD_MODEL->get('source_field_name')}"
				data-module-name="{$FIELD_MODEL->getModuleName()}"
				{/if} autocomplete="off" {if !$FIELD_MODEL->isActiveSearchView()}disabled{/if} />
		</div>
		<!-- /tpl-Base-List-Field-DateTime -->
	{/strip}
