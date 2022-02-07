{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-MappedFields-ListViewHeader -->
	<div class="listViewPageDiv" id="listViewContainer">
		<div class="listViewTopMenuDiv">
			<div class="o-breadcrumb widget_header row">
				<div class="col-12">
					{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $QUALIFIED_MODULE)}
				</div>
			</div>
			<div class="row align-items-center my-1">
				<div class="col-md-4 btn-toolbar">
					<button class="btn btn-light addButton" id="addButton" data-url="{$MODULE_MODEL->getCreateRecordUrl()}">
						<span class="fas fa-plus"></span>&nbsp;
						<strong>{\App\Language::translate('LBL_ADD_TEMPLATE',$QUALIFIED_MODULE)}</strong>
					</button>
					<button class="btn btn-light importButton" id="importButton"
						data-url="{$MODULE_MODEL->getImportViewUrl()}"
						title="{\App\Language::translate('LBL_IMPORT_TEMPLATE', $QUALIFIED_MODULE)}">
						<i class="fas fa-download"></i>
					</button>
				</div>
				<div class="col-md-4 btn-toolbar">
					<select class="select2" id="moduleFilter"
						data-placeholder="{\App\Language::translate('LBL_ALL', $QUALIFIED_MODULE)}"
						data-select="allowClear">
						<optgroup class="p-0">
							<option value="">{\App\Language::translate('LBL_ALL', $QUALIFIED_MODULE)}</option>
						</optgroup>
						{foreach item=MODULE_MODEL key=TAB_ID from=$SUPPORTED_MODULE_MODELS}
							{if $MODULE_MODEL->getName() eq 'OSSMailView'} continue {/if}
							<option {if !empty($SOURCE_MODULE) && $SOURCE_MODULE eq $MODULE_MODEL->getId()} selected="" {/if}
								value="{$MODULE_MODEL->getId()}">
								{\App\Language::translate($MODULE_MODEL->getName(),$MODULE_MODEL->getName())}
							</option>
						{/foreach}
					</select>
				</div>
				<div class="col-md-4 btn-toolbar justify-content-end">
					{include file=\App\Layout::getTemplatePath('ListViewActions.tpl')}
				</div>
			</div>
		</div>
		<div class="listViewContentDiv" id="listViewContents">
			<!-- /tpl-Settings-MappedFields-ListViewHeader -->
{/strip}
