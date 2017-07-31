{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
<select name="{$FLD_NAME}" id="{$FLD_ID}" class="chzn-select  chzn-done {if $FLD_REQUIRED}required{/if}" style="width: 250px;">
    <option value="person_who_created_record">{\App\Language::translate('PRESON_WHO_CREATE_RECORD', 'OSSProjectTemplates')}</option>
    <optgroup label="{\App\Language::translate('LBL_USERS')}">
        {foreach from=$OPTION['User'] item=item key=key}
            {if !empty($item)}
            <option value="{$key}">{$item}</option>
            {/if}
        {/foreach}
    </optgroup>
    <optgroup label="{\App\Language::translate('LBL_GROUPS')}">
        {foreach from=$OPTION['Group'] item=item key=key}
            {if !empty($item)}
            <option value="{$key}">{$item}</option>
            {/if}
        {/foreach}
    </optgroup>
</select>