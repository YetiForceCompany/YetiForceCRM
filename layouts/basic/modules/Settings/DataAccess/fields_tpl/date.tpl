{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
<select type="text" name="{$FLD_NAME}" id="{$FLD_ID}" class="select-date chzn-select  chzn-done {if $FLD_REQUIRED}required{/if}" style="width: 250px;">
    <option value="create_date">{vtranslate('CREATE_DATE_RECORD', 'OSSProjectTemplates')}</option>
    <option value="num_day">{vtranslate('NUM_DAY_FROM_CREATE', 'OSSProjectTemplates')}</option>
    <option value="none">{vtranslate('NONE', 'OSSProjectTemplates')}</option>
</select>

    <input name="{$FLD_NAME}_day" readonly class="day-input" type="text" />
    <br />
    {vtranslate('ONLY_WORK_DAY', 'OSSProjectTemplates')}: <input name="{$FLD_NAME}_day_type" disabled class="day-type-input" type="checkbox" />