{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	{assign var="ANNOUNCEMENTS" value=Vtiger_Module_Model::getInstance('Announcements')}
	{if $ANNOUNCEMENTS->checkActive()}
		{include file=\App\Layout::getTemplatePath('Announcement.tpl', $MODULE)}
	{/if}
	<div class="container-fluid container-fluid-main">
		<div class="baseContainer {if AppConfig::module('Users','IS_VISIBLE_USER_INFO_FOOTER')}userInfoFooter{/if}">
			{assign var=LEFTPANELHIDE value=$USER_MODEL->get('leftpanelhide')}
			<div class="js-sidebar leftPanel noSpaces" data-js="class: .open-menu">
				{include file=\App\Layout::getTemplatePath('BodyLeft.tpl', $MODULE) DEVICE=Desktop}
			</div>
			{include file=\App\Layout::getTemplatePath('BodyHeader.tpl', $MODULE)}
			<div class="basePanel noSpaces {if $LEFTPANELHIDE} menuOpen{/if} {$MODULE}_{$VIEW}">
				<div class="mainBody {if AppConfig::module('Users','IS_VISIBLE_USER_INFO_FOOTER')}userInfoFooter{/if}">
					{include file=\App\Layout::getTemplatePath('BodyContent.tpl', $MODULE)}
				{/strip}
