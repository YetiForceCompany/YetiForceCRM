{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
<select type="text" name="{$FLD_NAME}" id="{$FLD_ID}" class="chzn-select  chzn-done {if $FLD_REQUIRED}required{/if}" style="width: 250px;">
    <option value="none">{\App\Language::translate('NONE', 'OSSProjectTemplates')}</option>
    <option value="base_on_record">{\App\Language::translate('BASE_ON_RECORD', 'OSSProjectTemplates')}</option>
</select>