{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<div class="tpl-Detail-Field-UserRole u-paragraph-m-0">
	{assign var="ROLE_LABEL" value=$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'), $RECORD->getId(), $RECORD)}
	{if $USER_MODEL->isAdminUser() && !empty($FIELD_MODEL->get('fieldvalue'))}
		<a href="{Settings_Roles_Record_Model::getInstanceById($FIELD_MODEL->get('fieldvalue'))->getEditViewUrl()}">{$ROLE_LABEL}</a>
	{else}
		{$ROLE_LABEL}
	{/if}
</div>
