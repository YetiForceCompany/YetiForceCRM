{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="listViewPageDiv">
		<div class="listViewTopMenuDiv">
			<div class="widget_header row">
				<div class="col-12">
					{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE)}
				</div>
			</div>
			<div class="badge badge-info my-2">
				{if isset($SELECTED_PAGE)}
					{\App\Language::translate($SELECTED_PAGE->get('description'),$QUALIFIED_MODULE)}
				{/if}
			</div>
			<div class="form-row align-items-center mb-2">
				<div class="col-md-4 btn-toolbar">
					{foreach item=LISTVIEW_BASICACTION from=$LISTVIEW_LINKS['LISTVIEWBASIC']}
						<button class="btn addButton btn-success" {if stripos($LISTVIEW_BASICACTION->getUrl(), 'javascript:')===0} onclick='{$LISTVIEW_BASICACTION->getUrl()|substr:strlen("javascript:")};'
								{else} onclick='window.location.href = "{$LISTVIEW_BASICACTION->getUrl()}"' {/if}>
										<i class="fas fa-plus"></i>&nbsp;
										<strong>{\App\Language::translate('LBL_ADD_RECORD', $QUALIFIED_MODULE)}</strong>
									</button>
									{/foreach}
									</div>
									<div class="col-md-4 btn-toolbar ml-0" >
										<select class="chzn-select form-control ml-1" id="moduleFilter">
											<option value="">{\App\Language::translate('LBL_ALL', $QUALIFIED_MODULE)}</option>
											{foreach item=MODULE_MODEL key=TAB_ID from=$SUPPORTED_MODULE_MODELS}
												<option {if $SOURCE_MODULE eq $MODULE_MODEL->getName()} selected="" {/if} value="{$MODULE_MODEL->getName()}">
													{if $MODULE_MODEL->getName() eq 'Calendar'}
														{\App\Language::translate('LBL_TASK', $MODULE_MODEL->getName())}
													{else}
														{\App\Language::translate($MODULE_MODEL->getName(),$MODULE_MODEL->getName())}
													{/if}
												</option>
											{/foreach}
										</select>

									</div>
									<div class="col-md-4 d-flex justify-content-end">
										{include file=\App\Layout::getTemplatePath('ListViewActions.tpl', $QUALIFIED_MODULE)}
									</div>
								</div>
						</div>
					</div>
					<div class="listViewContentDiv listViewPageDiv" id="listViewContents">
						{/strip}
