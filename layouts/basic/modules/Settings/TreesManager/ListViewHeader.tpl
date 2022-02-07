{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-TreesManager-ListViewHeader listViewPageDiv">
		<div class="listViewTopMenuDiv">
			<div class="o-breadcrumb widget_header row">
				<div class="col-12">
					{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
				</div>
			</div>
			<div class="form-row align-items-center my-2">
				<div class="col-md-4 btn-toolbar">
					{foreach item=LISTVIEW_BASICACTION from=$LISTVIEW_LINKS['LISTVIEWBASIC']}
						<button class="btn addButton btn-success" {if stripos($LISTVIEW_BASICACTION->getUrl(), 'javascript:')===0} onclick='{$LISTVIEW_BASICACTION->getUrl()|substr:strlen("javascript:")};'
							{else} onclick='window.location.href = "{$LISTVIEW_BASICACTION->getUrl()}"' 
							{/if}>
							<i class="fas fa-plus"></i>&nbsp;
							<strong>{\App\Language::translate('LBL_ADD_RECORD', $QUALIFIED_MODULE)}</strong>
						</button>
					{/foreach}
				</div>
				<div class="col-md-4 btn-toolbar ml-0">
					<select class="select2 form-control ml-1" id="moduleFilter" data-placeholder="{\App\Language::translate('LBL_ALL', $QUALIFIED_MODULE)}"
						data-select="allowClear">
						<optgroup class="p-0">
							<option value="">{\App\Language::translate('LBL_ALL', $QUALIFIED_MODULE)}</option>
						</optgroup>
						{foreach item=MODULE_MODEL key=TAB_ID from=$SUPPORTED_MODULE_MODELS}
							<option {if !empty($SOURCE_MODULE) && $SOURCE_MODULE eq $MODULE_MODEL->getName()} selected="" {/if} value="{$MODULE_MODEL->getName()}">
								{\App\Language::translate($MODULE_MODEL->getName(),$MODULE_MODEL->getName())}
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
