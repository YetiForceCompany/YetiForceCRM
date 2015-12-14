{strip}
	<div class="settingsIndexPage">
		<div class="widget_header row">
			<div class="col-xs-12">
				{include file='BreadCrumbs.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
			</div>
			<div class="col-xs-12">
				{vtranslate('LBL_CREDITS_DESCRIPTION', $QUALIFIED_MODULE)}
			</div>
		</div>
		{include file="licenses/Credits.html"}
	</div>
{/strip}
