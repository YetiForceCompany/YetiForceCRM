{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="mfTemplateContents">
		<form name="editMFTemplate" action="index.php" method="post" id="mf_step1" class="form-horizontal">
			<input type="hidden" name="module" value="MappedFields">
			<input type="hidden" name="view" value="Edit">
			<input type="hidden" name="mode" value="Step2" />
			<input type="hidden" name="parent" value="Settings" />
			<input type="hidden" class="step" value="1" />
			{if $RECORDID}
				<input type="hidden" name="tabid" value="{$MAPPEDFIELDS_MODULE_MODEL->get('tabid')}" />
				<input type="hidden" name="reltabid" value="{$MAPPEDFIELDS_MODULE_MODEL->get('reltabid')}" />
				<input type="hidden" name="record" value="{$RECORDID}" />
			{/if}
			<div class="col-md-12 px-0">
				<div class="card">
					<div class="card-header">
						<label>
							<strong>{\App\Language::translateArgs('LBL_STEP_N',$QUALIFIED_MODULE, 1)}: {\App\Language::translate('LBL_ENTER_BASIC_DETAILS',$QUALIFIED_MODULE)}</strong>
						</label>
					</div>
					<div class="card-body">
						<div class="form-group">
							<label class="col-sm-3 col-form-label">
								{\App\Language::translate('LBL_STATUS', $QUALIFIED_MODULE)}<span class="redColor">*</span>
							</label>
							<div class="col-sm-8 controls">
								<select class="select2 form-control" id="status" name="status" required="true">
									<option value="1" {if $MAPPEDFIELDS_MODULE_MODEL->get('status')}selected{/if}>
										{\App\Language::translate('active', $QUALIFIED_MODULE)}
									</option>
									<option value="0" {if !$MAPPEDFIELDS_MODULE_MODEL->get('status')}selected{/if}>
										{\App\Language::translate('inactive', $QUALIFIED_MODULE)}
									</option>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 col-form-label">
								{\App\Language::translate('LBL_SELECT_MODULE', $QUALIFIED_MODULE)}<span class="redColor">*</span>
							</label>
							<div class="col-sm-8 controls">
								<select class="select2 form-control" id="tabid" name="tabid" required="true" data-validation-engine="validate[required]" {if $RECORDID} disabled {/if}>
									{foreach from=$ALL_MODULES key=TABID item=MODULE}
										{if $MODULE->getName() eq 'OSSMailView'} continue {/if}
										<option value="{$TABID}" {if $MAPPEDFIELDS_MODULE_MODEL->get('tabid') == $TABID} selected {/if}>
											{\App\Language::translate($MODULE->getName(), $MODULE->getName())}
										</option>
									{/foreach}
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 col-form-label">
								{\App\Language::translate('LBL_SELECT_REL_MODULE', $QUALIFIED_MODULE)}<span class="redColor">*</span>
							</label>
							<div class="col-sm-8 controls">
								<select class="select2 form-control" id="reltabid" name="reltabid" required="true" data-validation-engine="validate[required]" {if $RECORDID} disabled {/if}>
									{foreach from=$ALL_MODULES key=TABID item=MODULE}
										{if $MODULE->getName() eq 'OSSMailView'} continue {/if}
										<option value="{$TABID}" {if $MAPPEDFIELDS_MODULE_MODEL->get('reltabid') == $TABID} selected {/if}>
											{\App\Language::translate($MODULE->getName(), $MODULE->getName())}
										</option>
									{/foreach}
								</select>
							</div>
						</div>
					</div>
					<div class="card-footer clearfix">
						<div class="btn-toolbar float-right">
							<button class="btn btn-success" type="submit">
								<span class="fas fa-caret-right mr-1"></span>
								{\App\Language::translate('LBL_NEXT', $QUALIFIED_MODULE)}
							</button>
							<button class="btn btn-warning cancelLink" type="reset">
								<span class="fas fa-times mr-1"></span>
								{\App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}
							</button>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
{/strip}
