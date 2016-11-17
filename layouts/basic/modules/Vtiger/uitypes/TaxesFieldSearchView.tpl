{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{assign var="FIELD_INFO" value=\App\Json::encode($FIELD_MODEL->getFieldInfo())}
	{assign var=SEARCH_VALUES value=explode(',',$SEARCH_INFO['searchValue'])}
	<div class="row">
		<select class="select2noactive listSearchContributor col-md-9" name="{$FIELD_MODEL->get('name')}" title="{vtranslate($FIELD_MODEL->get('label'), $MODULE)}" multiple data-fieldinfo='{$FIELD_INFO|escape}'>
			{foreach item=PICKLIST_NAME key=PICKLIST_VALUE from=$FIELD_MODEL->getPicklistValues()}
				<option value="{$PICKLIST_VALUE}" {if isset($SEARCH_VALUES[$PICKLIST_VALUE])} selected {/if}>{$PICKLIST_NAME}</option>
			{/foreach}
		</select>
	</div>
{/strip}
