{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="listViewPageDiv" id="listViewContainer">
		<div class="listViewTopMenuDiv">
			<div class="row">
				<div class="col-md-12">
					<h3>{vtranslate($MODULE,$QUALIFIED_MODULE)}</h3>
				</div>
			</div>
			<hr>
			<div class="row">
				<div class="col-md-4 btn-toolbar">
					<button class="btn btn-default addButton" id="addButton" data-url="{Settings_MappedFields_Module_Model::getCreateRecordUrl()}">
						<span class="glyphicon glyphicon-plus"></span>&nbsp;
						<strong>{vtranslate('LBL_ADD_TEMPLATE',$QUALIFIED_MODULE)}</strong>
					</button>
					<button class="btn btn-default importButton" id="importButton" data-url="{Settings_MappedFields_Module_Model::getImportViewUrl()}" title="{vtranslate('LBL_IMPORT_TEMPLATE', $QUALIFIED_MODULE)}">
						<i class="glyphicon glyphicon-import"></i>
					</button>
				</div>
				<div class="col-md-4 btn-toolbar">
					<select class="chzn-select" id="moduleFilter" >
						<option value="">{vtranslate('LBL_ALL', $QUALIFIED_MODULE)}</option>
						{foreach item=MODULE_MODEL key=TAB_ID from=$SUPPORTED_MODULE_MODELS}
							<option {if $SOURCE_MODULE eq $MODULE_MODEL->getId()} selected="" {/if} value="{$MODULE_MODEL->getId()}">
								{vtranslate($MODULE_MODEL->getName(),$MODULE_MODEL->getName())}
							</option>
						{/foreach}
					</select>
				</div>
				<div class="col-md-4 btn-toolbar">
					{include file='ListViewActions.tpl'|@vtemplate_path}
				</div>
			</div>
		</div>
		<div class="listViewContentDiv" id="listViewContents">
		{/strip}
