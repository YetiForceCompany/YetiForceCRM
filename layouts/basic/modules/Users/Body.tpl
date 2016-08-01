{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="container-fluid container-fluid-main">
		<div class="baseContainer">
			{if $VIEW != 'Login'}
				{if !empty($CHILD_LINKS)}
					<span class="caret"></span>
				{/if}
				{assign var=LEFTPANELHIDE value=$USER_MODEL->get('leftpanelhide')}	
				{include file='BodyHeaderMobile.tpl'|@vtemplate_path:$MODULE}
				<div class="mobileLeftPanel noSpaces">
					{include file='BodyLeft.tpl'|@vtemplate_path:$MODULE DEVICE=Mobile}
				</div>
				<div class="leftPanel noSpaces">
					{include file='BodyLeft.tpl'|@vtemplate_path:$MODULE DEVICE=Desktop}
				</div>
				{if $USER_MODEL->get('theme') eq 'blue'}
					{include file='BodyHeader.tpl'|@vtemplate_path:$MODULE}
				{/if}
				<div class="basePanel noSpaces">
					{if $USER_MODEL->get('theme') eq 'twilight'}
						{include file='BodyHeader.tpl'|@vtemplate_path:$MODULE}
					{/if}
					{include file='BodyContent.tpl'|@vtemplate_path:$MODULE}
				{/if}
			{/strip}
