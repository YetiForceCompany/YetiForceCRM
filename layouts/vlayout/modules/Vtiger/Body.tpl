{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{include file='BodyHidden.tpl'|@vtemplate_path:$MODULE}
	<div class="container-fluid container-fluid-main">
		<div class="baseContainer">
			{assign var=LEFTPANELHIDE value=$CURRENT_USER_MODEL->get('leftpanelhide')}
			<div class="leftPanel noSpaces{if $LEFTPANELHIDE} stillOpen{/if}">
				{include file='BodyLeft.tpl'|@vtemplate_path:$MODULE}
			</div>
			<div class="basePanel noSpaces{if $LEFTPANELHIDE} leftPanelStillOpen{/if}">
				{include file='BodyHeader.tpl'|@vtemplate_path:$MODULE}
				{include file='BodyContent.tpl'|@vtemplate_path:$MODULE}
{/strip}
