{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-SlaPolicy-EditViewBlocks -->
	<div class='verticalScroll'>
		<div class='editViewContainer'>
			<form class="form-horizontal recordEditView" id="EditView" name="EditView" method="post" action="index.php" enctype="multipart/form-data">
				<input type="hidden" name="module" value="{$MODULE}" />
				<input type="hidden" name="parent" value="{$PARENT_MODULE}" />
				<input type="hidden" name="conditions" value="" />
				<input type="hidden" name="action" value="Save" />
				<input type="hidden" name="business_hours" value="{$RECORD->get('business_hours')}" />
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
							<div class="col-12 col-md-3">
								<label>{\App\Language::translate('LBL_NAME',$QUALIFIED_MODULE)}</label>
								<input type="text" name="name" class="form-control" value="{$RECORD->getName()}" data-validation-engine="validate[required,maxSize[255]]">
							</div>
							<div class="col-12 col-md-3">
								<label>{\App\Language::translate('LBL_SOURCE_MODULE',$QUALIFIED_MODULE)}</label>
								<select name="source_module" class="select2" data-validation-engine="validate[required]">
									{foreach item=MODULE_NAME from=$MODULES}
										<option value="{$MODULE_NAME}" {if \App\Module::getModuleName($RECORD->get('tabid')) === $MODULE_NAME}selected="selected" {/if}>{\App\Language::translate($MODULE_NAME, $MODULE_NAME)}</option>
									{/foreach}
								</select>
							</div>
							<div class="col-12 col-md-3">
								<label>{\App\Language::translate('LBL_OPERATIONAL_HOURS','ServiceContracts')}</label>
								<select name="operational_hours" class="select2" data-validation-engine="validate[required]">
									<option value="0" {if $RECORD->get('operational_hours')===0}selected="selected" {/if}>{\App\Language::translate('LBL_CALENDAR_HOURS','ServiceContracts')}</option>
									<option value="1" {if $RECORD->get('operational_hours')===1}selected="selected" {/if}>{\App\Language::translate('LBL_BUSINESS_HOURS','ServiceContracts')}</option>
								</select>
							</div>
							<div class="col-12 col-md-3">
								<label>{\App\Language::translate('LBL_AVAILABLE_FOR_RECORD_TIME_CALCULATION',$QUALIFIED_MODULE)} <span class="js-popover-tooltip" data-js="popover" data-trigger="hover focus" data-content="{\App\Language::translate("LBL_AVAILABLE_FOR_RECORD_TIME_CALCULATION_INFO",$QUALIFIED_MODULE)}">
										<span class="fas fa-info-circle"></span>
									</span></label>
								<div> <input type="checkbox" {if $RECORD->get('available_for_record_time_count')}checked{/if} name="available_for_record_time_count" title="{\App\Language::translate('LBL_AVAILABLE_FOR_RECORD_TIME_CALCULATION_INFO',$QUALIFIED_MODULE)}" />
								</div>
							</div>
						</div>
						<div class="form-group row js-business-hours-container pt-2 d-none">
							<div class="col-12 js-business-hours-container-content" data-js="container"></div>
						</div>
						<div class="form-group row border-top pt-2">
							<div class="col-12 js-condition-builder-view" data-js="container"></div>
						</div>
						<div class="form-group row border-top pt-4">
							<div class="col-12 col-md-4">
								<label>{\App\Language::translate('LBL_REACTION_TIME','ServiceContracts')}</label>
								<div class="input-group time">
									<input type="hidden" name="reaction_time" class="c-time-period" value="{$RECORD->get('reaction_time')}">
								</div>
							</div>
							<div class="col-12 col-md-4">
								<label>{\App\Language::translate('LBL_IDLE_TIME','ServiceContracts')}</label>
								<div class="input-group time">
									<input type="hidden" name="idle_time" class="c-time-period" value="{$RECORD->get('idle_time')}">
								</div>
							</div>
							<div class="col-12 col-md-4">
								<label>{\App\Language::translate('LBL_RESOLVE_TIME','ServiceContracts')}</label>
								<div class="input-group time">
									<input type="hidden" name="resolve_time" class="c-time-period" value="{$RECORD->get('resolve_time')}">
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- /tpl-Settings-SlaPolicy-EditViewBlocks -->
{/strip}
