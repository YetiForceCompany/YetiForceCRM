{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{if $VIEW != 'Login'}
		{include file='BodyHidden.tpl'|@vtemplate_path:$MODULE}
	{/if}
	<div class="container-fluid container-fluid-main">
		<div class="row baseContainer">
			{if $VIEW != 'Login'}
				{if !empty($CHILD_LINKS)}
					<span class="caret"></span>
				{/if}
				<div class="col-md-2 leftPanel noSpaces">
					{include file='BodyLeft.tpl'|@vtemplate_path:$MODULE}
				</div>
				<div class="col-md-10 basePanel noSpaces">
					{include file='BodyHeader.tpl'|@vtemplate_path:$MODULE}
					{include file='BodyContent.tpl'|@vtemplate_path:$MODULE}
				{/if}
			{/strip}
