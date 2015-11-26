{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="container-fluid container-fluid-main">
		<div class="baseContainer">
			{assign var=LEFTPANELHIDE value=$USER_MODEL->get('leftpanelhide')}
			{assign var="announcement" value=$ANNOUNCEMENT->get('announcement')}	
			{include file='BodyHeaderMobile.tpl'|@vtemplate_path:$MODULE}
			<div class="mobileLeftPanel noSpaces">
				{include file='BodyLeft.tpl'|@vtemplate_path:$MODULE DEVICE=Mobile}
			</div>
			<div class="leftPanel noSpaces{if $LEFTPANELHIDE} stillOpen{/if}">
				{include file='BodyLeft.tpl'|@vtemplate_path:$MODULE DEVICE=Desktop}
			</div>
			<div class="basePanel noSpaces{if $LEFTPANELHIDE} leftPanelStillOpen{/if}">
				{include file='BodyHeader.tpl'|@vtemplate_path:$MODULE}
				{include file='BodyContent.tpl'|@vtemplate_path:$MODULE}
{/strip}
