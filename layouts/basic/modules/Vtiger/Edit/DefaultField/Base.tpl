{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Edit-Field-DefaultBase -->
	{assign var="FIELD_MODEL" value=$FIELD_MODEL->set('fieldvalue',$FIELD_MODEL->get('defaultvalue'))}
	{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $FIELD_MODEL->getModuleName()) RECORD=false MODULE=$FIELD_MODEL->getModuleName()}
{/strip}
