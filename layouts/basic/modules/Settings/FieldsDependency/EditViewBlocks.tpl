{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-FieldsDependency-EditViewBlocks -->
	<div class="verticalScroll">
		<div class="editViewContainer">
			<form class="form-horizontal recordEditView" id="EditView" name="EditView" method="post" action="index.php" enctype="multipart/form-data">
				<input type="hidden" name="module" value="{$MODULE}" />
				<input type="hidden" name="parent" value="{$PARENT_MODULE}" />
				<input type="hidden" name="conditions" value="" />
				<input type="hidden" name="action" value="Save" />
				{if !empty($RECORD_ID)}
					<input type="hidden" name="record" id="recordId" value="{$RECORD_ID}" />
				{/if}
				<div class="o-breadcrumb widget_header row mb-3">
					<div class="col-md-8">
						{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
					</div>
				</div>
				<div class="card">
					<div class="card-header">
						{if !empty($RECORD->getId())}
							<span class="yfi yfi-full-editing-view mr-2"></span>
							{\App\Language::translate('LBL_EDIT',$QUALIFIED_MODULE)} - {$RECORD->getName()}
						{else}
							<span class="fas fa-plus mr-2"></span>
							{\App\Language::translate('LBL_CREATE',$QUALIFIED_MODULE)}
						{/if}
					</div>
					<div class="card-body">
						<div class="form-group row">
							<label for="inputName" class="col-sm-3 col-form-label text-right"><span class="redColor">*</span>{\App\Language::translate('LBL_NAME',$QUALIFIED_MODULE)}</label>
							<div class="col-sm-9">
								<input type="text" name="name" value="{$RECORD->getName()}" class="form-control" id="inputName" data-validation-engine="validate[required,maxSize[100]]">
							</div>
						</div>
						<div class="form-group row">
							<label for="inputSourceModule" class="col-sm-3 col-form-label text-right"><span class="redColor">*</span>{\App\Language::translate('LBL_SOURCE_MODULE',$QUALIFIED_MODULE)}</label>
							<div class="col-sm-9">
								<select name="tabid" class="select2 form-control" data-module="{$SOURCE_MODULE}" id="inputSourceModule" data-validation-engine="validate[required]">
									{foreach item=MODULE from=$MODULES}
										<option value="{$MODULE['tabid']}" data-module="{$MODULE['name']}" {if $RECORD->get('tabid') === $MODULE['tabid']}selected="selected" {/if}>
											{\App\Language::translate($MODULE['name'],$MODULE['name'])}
										</option>
									{/foreach}
								</select>
							</div>
						</div>
						<div class="form-group row">
							<label for="inputStatus" class="col-sm-3 col-form-label text-right">{\App\Language::translate('FL_ACTIVE',$QUALIFIED_MODULE)}</label>
							<div class="col-sm-9">
								<div class="form-check mt-1">
									<input type="checkbox" name="status" value="1" class="form-check-input" id="inputStatus" {if !$RECORD->get('status')}checked{/if}>
								</div>
							</div>
						</div>
						<div class="form-group row">
							<label for="inputViews" class="col-sm-3 col-form-label text-right">
								<span class="redColor">*</span>{\App\Language::translate('LBL_VIEWS',$QUALIFIED_MODULE)}
								<a href="#" class="js-popover-tooltip ml-2" data-placement="top" data-content="{\App\Language::translate('LBL_VIEWS_INFO', $QUALIFIED_MODULE)}">
									<i class="fas fa-info-circle"></i>
								</a>
							</label>
							<div class="col-sm-9">
								<select name="views[]" class="select2 form-control" id="inputViews" multiple="multiple" data-validation-engine="validate[required]">
									{foreach item=ITEM key=KEY from=\App\FieldsDependency::VIEWS}
										<option value="{$KEY}" {if in_array($KEY,$VIEWS)}selected="selected" {/if}>
											{\App\Language::translate($ITEM)}
										</option>
									{/foreach}
								</select>
							</div>
						</div>
						<div class="form-group row">
							<label for="inputGui" class="col-sm-3 col-form-label text-right">
								{\App\Language::translate('LBL_GUI',$QUALIFIED_MODULE)}
								<a href="#" class="js-popover-tooltip ml-2" data-placement="top" data-content="{\App\Language::translate('LBL_GUI_INFO', $QUALIFIED_MODULE)}">
									<i class="fas fa-info-circle"></i>
								</a>
							</label>
							<div class="col-sm-9">
								<div class="form-check mt-1">
									<input type="checkbox" name="gui" value="1" class="form-check-input" id="inputGui" {if $RECORD->get('gui')}checked{/if}>
								</div>
							</div>
						</div>
						<div class="form-group row mb-1">
							<label for="inputMandatory" class="col-sm-3 col-form-label text-right">
								{\App\Language::translate('LBL_MANDATORY',$QUALIFIED_MODULE)}
								<a href="#" class="js-popover-tooltip ml-2" data-placement="top" data-content="{\App\Language::translate('LBL_MANDATORY_INFO', $QUALIFIED_MODULE)}">
									<i class="fas fa-info-circle"></i>
								</a>
							</label>
							<div class="col-sm-9">
								<div class="form-check mt-1">
									<input type="checkbox" name="mandatory" value="1" class="form-check-input" id="inputMandatory" {if $RECORD->get('mandatory')}checked{/if}>
								</div>
							</div>
						</div>
						<div class="pt-3 border-top js-dynamic-blocks" data-js="container">
							{include file=\App\Layout::getTemplatePath('DynamicBlocks.tpl', $QUALIFIED_MODULE)}
						</div>
					</div>
				</div>
				<!-- /tpl-Settings-FieldsDependency-EditViewBlocks -->
{/strip}
