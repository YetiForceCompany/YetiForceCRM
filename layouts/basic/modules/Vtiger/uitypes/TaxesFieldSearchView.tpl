{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
    {assign var="FIELD_INFO" value=\includes\utils\Json::encode($FIELD_MODEL->getFieldInfo())}
	{assign var=UITYPE_MODEL value=$FIELD_MODEL->getUITypeModel()}
	{assign var=PICKLIST_VALUES value=$UITYPE_MODEL->getTaxes()}
    {assign var=SEARCH_VALUES value=explode(',',$SEARCH_INFO['searchValue'])}
    <div class="row">
        <select class="select2noactive listSearchContributor col-md-9" name="{$FIELD_MODEL->get('name')}" title="{vtranslate($FIELD_MODEL->get('label'), $MODULE)}" multiple style="width:150px;" data-fieldinfo='{$FIELD_INFO|escape}'>
            {foreach item=VALUE key=KEY from=$PICKLIST_VALUES}
                <option value="{$KEY}" {if in_array($KEY,$SEARCH_VALUES) && ($KEY neq "")} selected{/if}>{$VALUE['value']}% - {$VALUE['name']}</option>
            {/foreach}
        </select>
    </div>
{/strip}
