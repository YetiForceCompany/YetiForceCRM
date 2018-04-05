{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
    {assign var="FIELD_INFO" value=\App\Json::encode($FIELD_MODEL->getFieldInfo())}
    {assign var=PICKLIST_VALUES value=$FIELD_MODEL->getModulesListValues()}
    {assign var=SEARCH_VALUES value=explode('##',$SEARCH_INFO['searchValue'])}
    <div class="tpl-List-Field-Modules picklistSearchField">
        <select class="select2noactive listSearchContributor" name="{$FIELD_MODEL->getName()}" title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}" multiple data-fieldinfo='{$FIELD_INFO|escape}'>
			{foreach item=PICKLIST_LABEL key=PICKLIST_KEY from=$PICKLIST_VALUES}
                <option value="{\App\Purifier::encodeHtml($PICKLIST_LABEL.name)}" {if in_array($PICKLIST_LABEL.name,$SEARCH_VALUES) && ($PICKLIST_LABEL.name neq "") } selected{/if}>{\App\Purifier::encodeHtml($PICKLIST_LABEL.label)}</option>
			{/foreach}
		</select>
    </div>
{/strip}
