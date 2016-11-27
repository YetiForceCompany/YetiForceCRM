{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{assign var="FIELD_INFO" value=\App\Json::encode($FIELD_MODEL->getFieldInfo())}
	{assign var="REFERENCE_LIST" value=$FIELD_MODEL->getReferenceList()}
	{assign var="LABEL" value=$FIELD_MODEL->getFieldInfo()}
	{if isset($SEARCH_INFO['searchValue'])}
		{assign var=SEARCH_VALUES value=explode(',',$SEARCH_INFO['searchValue'])}
	{else}
		{assign var=SEARCH_VALUE value=''}
	{/if}
	<div class="picklistSearchField">
		<select class="select2noactive listSearchContributor {$FIELD_MODEL->get('name')}" name="{$FIELD_MODEL->get('name')}" multiple data-fieldinfo='{$FIELD_INFO|escape}' data-ajax-search="1" data-ajax-url="index.php?module={$MODULE}&action=Fields&mode=searchReference&fid={$FIELD_MODEL->get('id')}" data-minimum-input="3">
			{foreach from=$SEARCH_VALUES item=ID}
				<option value="{$ID}" selected="selected">{\App\Record::getLabel($ID)}</option>
			{/foreach}
		</select>
	</div>
{/strip}
