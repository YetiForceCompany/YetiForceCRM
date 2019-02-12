{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="">
		<div class="widget_header row">
			<div class="col-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
		</div>
		<div class="d-flex justify-content-between my-1">
			<div class="btn-toolbar">
				{foreach item=LISTVIEW_BASICACTION from=$LISTVIEW_LINKS['LISTVIEWBASIC']}
					<button class="btn addButton btn-success" {if stripos($LISTVIEW_BASICACTION->getUrl(), 'javascript:')===0} onclick='{$LISTVIEW_BASICACTION->getUrl()|substr:strlen("javascript:")};'
							{else} onclick='window.location.href = "{$LISTVIEW_BASICACTION->getUrl()}"' {/if}>
									<i class="fas fa-plus"></i>&nbsp;
									<strong>{\App\Language::translate('LBL_ADD_RECORD', $QUALIFIED_MODULE)}</strong>
								</button>
								{/foreach}
								</div>
								<div class="btn-toolbar">
									{include file=\App\Layout::getTemplatePath('ListViewActions.tpl', $QUALIFIED_MODULE)}
								</div>
							</div>
					</div>
					<div class="listViewContentDiv" id="listViewContents">
						{/strip}
