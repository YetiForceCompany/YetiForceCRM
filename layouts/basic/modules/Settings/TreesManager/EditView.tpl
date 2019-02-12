{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-TreesManager-EditView editViewContainer">
		<form class="form-horizontal recordEditView" id="EditView" name="EditView" method="post" action="index.php" enctype="multipart/form-data">
			<input type="hidden" name="module" value="TreesManager"/>
			<input type="hidden" name="parent" value="Settings"/>
			<input type="hidden" name="action" value="Save"/>
			<input type="hidden" name="record" value="{$RECORD_ID}"/>
			<input type="hidden" id="treeLastID" value="{$LAST_ID}"/>
			<input type="hidden" id="access" value="{$ACCESS}"/>
			<input type="hidden" name="tree" id="treeValues" value='{\App\Purifier::encodeHtml($TREE)}'/>
			<input type="hidden" name="replace" id="replaceIds" value=""/>
			<div class='widget_header row '>
				<div class="col-12">
					{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
				</div>
			</div>
			<div class="row">
				<label class="col-md-3"><strong><span class="redColor">*</span>{\App\Language::translate('LBL_NAME', $QUALIFIED_MODULE)}
						: </strong></label>
				<div class="col-md-4">
					<input type="text" class="fieldValue form-control" name="name" id="treeename" value="{$RECORD_MODEL->get('name')}" data-validation-engine='validate[required]'/>
				</div>
			</div>
			<br/>
			{assign var="SUPPORTED_MODULE_MODELS" value=Settings_Workflows_Module_Model::getSupportedModules()}
			<div class="row">
				<div class="col-md-3">
					<label class=""><strong>{\App\Language::translate('LBL_MODULE', $QUALIFIED_MODULE)}
							: </strong></label>
				</div>
				<div class="col-md-4 fieldValue">
					<select class="select2 form-control" name="templatemodule" {if !$ACCESS} disabled {/if} >
						{foreach item=MODULE_MODEL key=TAB_ID from=$SUPPORTED_MODULE_MODELS}
							<option {if $SOURCE_MODULE eq $TAB_ID} selected="" {/if} value="{$TAB_ID}">
								{\App\Language::translate($MODULE_MODEL->getName(),$MODULE_MODEL->getName())}
							</option>
						{/foreach}
					</select>
					{if !$ACCESS}
						<input type="text" class="fieldValue form-control d-none" name="templatemodule" value="{$SOURCE_MODULE}"/>
					{/if}
				</div>
			</div>
			<br/>
			<div class="row">
				<div class="col-md-3">
					<label class=""><strong>{\App\Language::translate('LBL_SHARE_WITH', $QUALIFIED_MODULE)}
							: </strong></label>
				</div>
				<div class="col-md-4 fieldValue">
					<select class="select2 form-control" name="share[]" multiple>
						{foreach item=MODULE_MODEL key=TAB_ID from=$SUPPORTED_MODULE_MODELS}
							<option {if in_array($TAB_ID, $RECORD_MODEL->get('share'))} selected="" {/if} value="{$TAB_ID}">
								{\App\Language::translate($MODULE_MODEL->getName(),$MODULE_MODEL->getName())}
							</option>
						{/foreach}
					</select>
				</div>
			</div>
			<br/>
			<hr>
			<div class="row align-items-center">
				<div class="col-md-3">
					<label class=""><strong>{\App\Language::translate('LBL_ADD_ITEM_TREE', $QUALIFIED_MODULE)}</strong></label>
				</div>
				<div class="col-md-8 d-flex">
					<input type="text" class="fieldValue col-md-4 addNewElement form-control">
					<button class="btn btn-primary addNewElementBtn ml-1" type="button">
						<span class="fas fa-plus u-mr-5px"></span><strong>{\App\Language::translate('LBL_ADD_TO_TREES', $QUALIFIED_MODULE)}</strong>
					</button>
				</div>
			</div>
			<hr class="mt-1">
			<div class="modal-header contentsBackground" tabindex="-1">
				<div id="treeContents"></div>
			</div>
			<br/>
			<div class="float-right">
				<button class="btn btn-success saveTree mr-1">
					<span class="fas fa-check mr-1"></span>
					<strong>{\App\Language::translate('LBL_SAVE', $MODULE)}</strong>
				</button>
				<button class="cancelLink btn btn-danger" type="reset" onclick="javascript:window.history.back();">
					<span class="fas fa-times"></span>&nbsp;&nbsp;
					<strong>{\App\Language::translate('LBL_CANCEL', $MODULE)}</strong>
				</button>
			</div>
			<div class="clearfix"></div>
	</div>
{/strip}
