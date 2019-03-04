{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<div class="tpl-Users-Detail-Field-Base u-paragraph-m-0">
    {if $FIELD_MODEL->getName() === 'default_country_carddav'}
        <span class="js-popover-tooltip ml-1" data-toggle="popover"
                            data-placement="top"
                            data-content="{\App\Purifier::encodeHtml(\App\Language::translate('LBL_CARDDAV_DEFAULT_COUNTRY_DESC', $MODULE_NAME))}" data-js="popover">
            <span class="fas fa-info-circle"></span>
        </span>
    {/if}
    {$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'), $RECORD->getId(), $RECORD)}
</div>
{/strip}
