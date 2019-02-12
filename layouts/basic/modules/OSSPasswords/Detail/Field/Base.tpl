{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{* if this is password input field, display pass as hidden *}
<div class="tpl-Detail-Field-Base u-paragraph-m-0">
{if $FIELD_MODEL->getName() eq 'password'}
    {str_repeat('*', 10)}
{else}
    {$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'), $RECORD->getId(), $RECORD)}
{/if}
</div>