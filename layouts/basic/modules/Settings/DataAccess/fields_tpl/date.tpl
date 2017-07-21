{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
<select type="text" name="{$FLD_NAME}" id="{$FLD_ID}" class="select-date chzn-select  chzn-done {if $FLD_REQUIRED}required{/if}" style="width: 250px;">
    <option value="create_date">{\App\Language::translate('CREATE_DATE_RECORD', 'OSSProjectTemplates')}</option>
    <option value="num_day">{\App\Language::translate('NUM_DAY_FROM_CREATE', 'OSSProjectTemplates')}</option>
    <option value="none">{\App\Language::translate('NONE', 'OSSProjectTemplates')}</option>
</select>

    <input name="{$FLD_NAME}_day" readonly class="day-input" type="text" />
    <br />
    {\App\Language::translate('ONLY_WORK_DAY', 'OSSProjectTemplates')}: <input name="{$FLD_NAME}_day_type" disabled class="day-type-input" type="checkbox" />