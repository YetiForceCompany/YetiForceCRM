{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="mfTemplateContents leftRightPadding3p">
		<form name="editMFTemplate" action="index.php" method="post" id="mf_step1" class="form-horizontal">
			<input type="hidden" name="module" value="MappedFields">
			<input type="hidden" name="view" value="Edit">
			<input type="hidden" name="mode" value="Step2" />
			<input type="hidden" name="parent" value="Settings" />
			<input type="hidden" class="step" value="1" />
			<input type="hidden" name="record" value="{$RECORDID}" />

			{if $RECORDID}
				<input type="hidden" name="tabid" value="{$MF_MODEL->get('tabid')}" />
				<input type="hidden" name="reltabid" value="{$MF_MODEL->get('reltabid')}" />
			{/if}
			<div class="padding1per stepBorder">
				<label>
					<strong>{vtranslate('LBL_STEP_N',$QUALIFIED_MODULE, 1)}: {vtranslate('LBL_ENTER_BASIC_DETAILS',$QUALIFIED_MODULE)}</strong>
				</label>
				<br>
				<div class="form-group">
					<label class="col-sm-3 control-label">
						{vtranslate('LBL_STATUS', $QUALIFIED_MODULE)}<span class="redColor">*</span>
					</label>
					<div class="col-sm-6 controls">
						<select class="chzn-select form-control" id="status" name="status" required="true">
							<option value="active" {if $MF_MODEL->get('status') eq 'active'} selected {/if}>
								{vtranslate('active', $QUALIFIED_MODULE)}
							</option>
							<option value="inactive" {if $MF_MODEL->get('status') eq 'inactive'} selected {/if}>
								{vtranslate('inactive', $QUALIFIED_MODULE)}
							</option>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">
						{vtranslate('LBL_SELECT_MODULE', $QUALIFIED_MODULE)}<span class="redColor">*</span>
					</label>
					<div class="col-sm-6 controls">
						<select class="chzn-select form-control" id="tabid" name="tabid" required="true" data-validation-engine='validate[required]' {if $RECORDID} disabled {/if}>
							{foreach from=$ALL_MODULES key=TABID item=MODULE}
								{if $MODULE->getName() eq 'OSSMailView'} continue {/if}
								<option value="{$TABID}" {if $MF_MODEL->get('tabid') == $TABID} selected {/if}>
									{vtranslate($MODULE->getName(), $MODULE->getName())}
								</option>
							{/foreach}
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">
						{vtranslate('LBL_SELECT_REL_MODULE', $QUALIFIED_MODULE)}<span class="redColor">*</span>
					</label>
					<div class="col-sm-6 controls">
						<select class="chzn-select form-control" id="reltabid" name="reltabid" required="true" data-validation-engine='validate[required]' {if $RECORDID} disabled {/if}>
							{foreach from=$ALL_MODULES key=TABID item=MODULE}
								{if $MODULE->getName() eq 'OSSMailView'} continue {/if}
								<option value="{$TABID}" {if $MF_MODEL->get('reltabid') == $TABID} selected {/if}>
									{vtranslate($MODULE->getName(), $MODULE->getName())}
								</option>
							{/foreach}
						</select>
					</div>
				</div>
			</div>
			<br>
			<div class="pull-right">
				<button class="btn btn-success" type="submit" ><strong>{vtranslate('LBL_NEXT', $QUALIFIED_MODULE)}</strong></button>&nbsp;&nbsp;
				<button class="btn btn-warning cancelLink" type="reset">{vtranslate('LBL_CANCEL', $QUALIFIED_MODULE)}</button>
			</div>
		</form>
	</div>
{/strip}
