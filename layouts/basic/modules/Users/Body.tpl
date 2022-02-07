{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var="ANNOUNCEMENTS" value=Vtiger_Module_Model::getInstance('Announcements')}
	{if $ANNOUNCEMENTS->checkActive()}
		{include file=\App\Layout::getTemplatePath('Announcement.tpl', $MODULE)}
	{/if}
	{assign var=LEFTPANELHIDE value=$USER_MODEL->get('leftpanelhide')}
	<div class="container-fluid container-fluid-main">
		<div class="o-base-container js-base-container c-menu--animation {if $LEFTPANELHIDE} c-menu--open{/if} {if App\Config::module('Users','IS_VISIBLE_USER_INFO_FOOTER')}userInfoFooter{/if}" data-js="container | class: c-menu--animation">
			{if $VIEW != 'Login'}
				{if !empty($CHILD_LINKS)}
					<span class="caret"></span>
				{/if}
				<div class="js-sidebar c-menu__container noSpaces" data-js="class: .js-expand">
					{include file=\App\Layout::getTemplatePath('BodyLeft.tpl', $MODULE)}
				</div>
				{include file=\App\Layout::getTemplatePath('BodyHeader.tpl', $MODULE)}
				<div class="basePanel">
					<div class="mainBody">
						{include file=\App\Layout::getTemplatePath('BodyContent.tpl', $MODULE)}
					{/if}
{/strip}
