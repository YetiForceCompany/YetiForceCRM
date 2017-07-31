{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
<select name="{$FLD_NAME}[]" id="{$FLD_NAME}{if $EDIT_VIEW}_edit{/if}" class="chzn-select col-md-3" multiple="multiple">
    {foreach from=$OPTION_LIST item=item key=key}
        <option value="{$item}">{$key}</option>
    {/foreach}
</select>