{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-inventoryfields-GroupHeaders-Name -->
	{assign var="FIELD_TPL_NAME" value="inventoryfields/"|cat:$GROUP_FIELD->getTemplateName('EditView',$MODULE_NAME)}
	{include file=\App\Layout::getTemplatePath($FIELD_TPL_NAME, $MODULE_NAME) FIELD=$GROUP_FIELD}
	<!-- /tpl-Base-inventoryfields-GroupHeaders-Name -->
{/strip}
