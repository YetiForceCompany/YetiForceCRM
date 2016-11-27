{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
    {assign var="FIELD_INFO" value=\App\Json::encode($FIELD_MODEL->getFieldInfo())}
    {assign var=ALL_VALUES value=$FIELD_MODEL->getUITypeModel()->getAllValue()}
    {assign var=SEARCH_VALUES value=explode(',',$SEARCH_INFO['searchValue'])}
    <div class="picklistSearchField">
        <select id="{$FIELD_MODEL->get('name')}" class="select2noactive listSearchContributor tree form-control" title="{vtranslate($FIELD_MODEL->get('label'), $MODULE)}" multiple name="{$FIELD_MODEL->get('name')}"  data-fieldinfo='{$FIELD_INFO|escape}'>
        {foreach item=LABEL key=KEY from=$ALL_VALUES}
                <option value="{$KEY}"  data-parent="{$LABEL[1]}" {if in_array($KEY,$SEARCH_VALUES) && ($KEY neq "") } selected{/if}>{$LABEL[0]}</option>
        {/foreach}
    </select>
    </div>
{/strip}
