{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{assign var="FIELD_INFO" value=Zend_Json::encode($FIELD_MODEL->getFieldInfo())}
	{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getUITypeModel()->getPicklistValues()}
	{assign var=SEARCH_VALUES value=explode(',',$SEARCH_INFO['searchValue'])}
	<div class="picklistSearchField">
		<select class="select2noactive listSearchContributor" name="{$FIELD_MODEL->get('name')}" title="{vtranslate($FIELD_MODEL->get('label'), $MODULE)}" multiple data-fieldinfo="{$FIELD_INFO|escape}">
			{foreach item=TRANSLATED key=LABEL from=$PICKLIST_VALUES}
				<option value="{$LABEL}" {if in_array($LABEL,$SEARCH_VALUES) && ($LABEL neq "")}selected{/if}>{$TRANSLATED}</option>
			{/foreach}
		</select>
	</div>
{/strip}
