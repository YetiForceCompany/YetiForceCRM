{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
<select name="{$FLD_NAME}" id="{$FLD_ID}" class="chzn-select  chzn-done {if $FLD_REQUIRED}required{/if}" style="width: 250px;">
    {foreach from=$OPTION_LIST key=key item=item}
        <option value="{$item}">{\App\Language::translate($item|upper, 'OSSProjectTemplates')}</option>
    {/foreach}
</select>
