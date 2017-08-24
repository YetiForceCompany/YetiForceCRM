{strip}
{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
	<div class="container-fluid container-fluid-main">
		<div class="baseContainer {if AppConfig::module('Users','IS_VISIBLE_USER_INFO_FOOTER')}userInfoFooter{/if}">
			{assign var=LEFTPANELHIDE value=$USER_MODEL->get('leftpanelhide')}	
			{include file='BodyHeaderMobile.tpl'|@\App\Layout::getTemplatePath:$MODULE}
			<div class="mobileLeftPanel noSpaces">
				{include file='BodyLeft.tpl'|@\App\Layout::getTemplatePath:$MODULE DEVICE=Mobile}
			</div>
			<div class="leftPanel noSpaces">
				{include file='BodyLeft.tpl'|@\App\Layout::getTemplatePath:$MODULE DEVICE=Desktop}
			</div>
			{include file='BodyHeader.tpl'|@\App\Layout::getTemplatePath:$MODULE}
			<div class="basePanel noSpaces {if $LEFTPANELHIDE} menuOpen{/if} {$MODULE}_{$VIEW}">
				<div class="mainBody {if AppConfig::module('Users','IS_VISIBLE_USER_INFO_FOOTER')}userInfoFooter{/if}">
				{include file='BodyContent.tpl'|@\App\Layout::getTemplatePath:$MODULE}
{/strip}
