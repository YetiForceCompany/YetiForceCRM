{strip}
	<div class="settingsIndexPage">
		<div class="widget_header row">
			<div class="col-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $QUALIFIED_MODULE)}
			</div>
			<div class="col-12">
				{\App\Language::translate('LBL_CREDITS_DESCRIPTION', $QUALIFIED_MODULE)}
			</div>
		</div>
		{include file="licenses/Credits.tpl"}
	</div>
{/strip}
