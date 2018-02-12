{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	<div class="">
		<div class='widget_header row '>
			<div class="col-xs-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE)}
			</div>
		</div>
		{if !\AppConfig::security('PERMITTED_BY_ADVANCED_PERMISSION')}
			<div class="alert alert-block alert-danger fade in">
				<button type="button" class="close" data-dismiss="alert">×</button>
				<h4 class="alert-heading">{\App\Language::translate('ERR_INACTIVE_ALERT_TITLE', $QUALIFIED_MODULE)}</h4>
				<p>{\App\Language::translate('ERR_INACTIVE_ALERT_DESC', $QUALIFIED_MODULE)}</p>
			</div>	
		{/if}
		{if !\AppConfig::security('CACHING_PERMISSION_TO_RECORD')}
			<div class="alert alert-block alert-danger fade in">
				<h4 class="alert-heading">{\App\Language::translate('ERR_INACTIVE_ALERT_TITLE', $QUALIFIED_MODULE)}</h4>
				<p>{\App\Language::translate('ERR_INACTIVE_CACHING_PERM_ALERT_DESC', $QUALIFIED_MODULE)}</p>
			</div>

		{/if}
		<div class="listViewActionsDiv row">
			<div class="col-md-8 btn-toolbar">
				{if \AppConfig::security('CACHING_PERMISSION_TO_RECORD')}
					{foreach item=LISTVIEW_BASICACTION from=$LISTVIEW_LINKS['LISTVIEWBASIC']}
						<button class="btn btn-success addButton" {if stripos($LISTVIEW_BASICACTION->getUrl(), 'javascript:')===0} onclick='{$LISTVIEW_BASICACTION->getUrl()|substr:strlen("javascript:")};'
								{else} onclick='window.location.href = "{$LISTVIEW_BASICACTION->getUrl()}"' {/if}>
										<span class="fas fa-plus"></span>&nbsp;
										<strong>{\App\Language::translate($LISTVIEW_BASICACTION->getLabel(), $QUALIFIED_MODULE)}</strong>
									</button>
									{/foreach}
										{/if}
										</div>
										<div class="col-md-4">
											{include file=\App\Layout::getTemplatePath('ListViewActions.tpl', $QUALIFIED_MODULE)}
										</div>
									</div>
									<div class="clearfix"></div>
									<div class="listViewContentDiv listViewPageDiv" id="listViewContents">
										{/strip}
