{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="widget_header row ">
		<div class="col-xs-12">
			{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
			{vtranslate('LBL_'|cat:$MODULE|upper|cat:'_DESCRIPTION', $QUALIFIED_MODULE)}
		</div>
	</div>
{/strip}
