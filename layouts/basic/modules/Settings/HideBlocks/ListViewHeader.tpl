{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
{strip}
<div class="">
	<div class="widget_header row">
		<div class="col-xs-12">
			{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
			{\App\Language::translate('LBL_HIDEBLOCKS_DESCRIPTION', $QUALIFIED_MODULE)}
		</div>
	</div>
		<div class="pull-left btn-toolbar">
			{foreach item=LISTVIEW_BASICACTION from=$LISTVIEW_LINKS['LISTVIEWBASIC']}
			<button class="btn addButton btn-success" {if stripos($LISTVIEW_BASICACTION->getUrl(), 'javascript:')===0} onclick='{$LISTVIEW_BASICACTION->getUrl()|substr:strlen("javascript:")};'
					{else} onclick='window.location.href="{$LISTVIEW_BASICACTION->getUrl()}"' {/if}>
				<i class="glyphicon glyphicon-plus"></i>&nbsp;
				<strong>{\App\Language::translate('LBL_ADD_RECORD', $QUALIFIED_MODULE)}</strong>
			</button>
			{/foreach}
		</div>
		<div class="pull-right btn-toolbar">
			{include file='ListViewActions.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
		</div>
		<div class="clearfix"></div>
	</div>
	<div class="listViewContentDiv" id="listViewContents">
{/strip}
