{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="FieldBlock leftRightPadding3p">
		<div class="form-group">
			<div class="form-group row">
				<label class="col-sm-2 control-label">
					{vtranslate('LBL_SELECT_MODULE', $QUALIFIED_MODULE)}
				</label>
				<div class="col-sm-3 controls">
					<select class="chzn-select form-control" name="module_name" required="true">
						<option value="" selected="">{vtranslate('LBL_SELECT', $QUALIFIED_MODULE)}</option>
						{foreach from=$ALL_MODULES key=TABID item=MODULE_MODEL}
							<option value="{$MODULE_MODEL->getName()}" {if $PDF_MODEL->get('module_name') == $MODULE_MODEL->getName()} selected {/if}>
								{if $MODULE_MODEL->getName() eq 'Calendar'}
									{vtranslate('LBL_TASK', $MODULE_MODEL->getName())}
								{else}
									{vtranslate($MODULE_MODEL->getName(), $MODULE_MODEL->getName())}
								{/if}
							</option>
						{/foreach}
					</select>
				</div>
				<label class="col-sm-2 control-label">
					{vtranslate('LBL_MAIN_MODULE_FIELDS', $QUALIFIED_MODULE)}
				</label>
				<div class="col-sm-4 controls">
					<select class="chzn-select form-control" name="main_fields">
						{foreach from=Settings_PDF_Module_Model::getMainModuleFields($PDF_MODEL->get('module_name')) key=BLOCK_LABEL item=BLOCK}
							<optgroup label="{$BLOCK_LABEL}">
								{foreach from=$BLOCK item=FIELD}
									<option value="{$FIELD['name']}">{$FIELD['label']}</option>
								{/foreach}
							</optgroup>
						{/foreach}
					</select>
				</div>
				<div class="col-md-1 input-group">
					<input type="hidden" value="" id="mainFieldValue" /><button class="btn btn-sm btn-info pull-right marginRight5px" data-clipboard-target="mainFieldValue" id="mainFieldsValueCopy"  title="{vtranslate('LBL_FIELD', $QUALIFIED_MODULE)}"><span class="glyphicon glyphicon-download-alt"></span></button>
					<input type="hidden" value="" id="mainFieldLabel" /><button class="btn btn-sm btn-warning pull-right marginRight5px" data-clipboard-target="mainFieldLabel" id="mainFieldsLabelCopy"  title="{vtranslate('LBL_LABEL', $QUALIFIED_MODULE)}"><span class="glyphicon glyphicon-download-alt"></span></button>
				</div>
			</div>
			<div class="form-group row">
				<label class="col-sm-2 control-label">
					{vtranslate('LBL_RELATED_MODULES', $QUALIFIED_MODULE)}
				</label>
				<div class="col-sm-3 controls">
					<select class="chzn-select form-control" name="related_module">
						<option value="" selected="">{vtranslate('LBL_SELECT', $QUALIFIED_MODULE)}</option>
						{foreach from=$RELATED_MODULES key=TABID item=MODULE_NAME name=RELATEDMODULES}
							<option value="{$TABID}" {if $smarty.foreach.RELATEDMODULES.first}selected="selected"{/if}>{$MODULE_NAME}</option>
						{/foreach}
					</select>
				</div>
				<label class="col-sm-2 control-label">
					{vtranslate('LBL_RELATED_MODULES_FIELDS', $QUALIFIED_MODULE)}
				</label>
				<div class="col-sm-4 controls">
					<select class="chzn-select form-control" name="related_fields">
						{foreach from=$RELATED_FIELDS key=BLOCK_LABEL item=BLOCK}
							<optgroup label="{$BLOCK_LABEL}">
								{foreach from=$BLOCK item=FIELD}
									<option value="{$FIELD['name']}">{$FIELD['label']}</option>
								{/foreach}
							</optgroup>
						{/foreach}
					</select>
				</div>
				<div class="col-md-1 input-group">
					<input type="hidden" value="" id="relatedFieldValue" /><button class="btn btn-sm btn-info pull-right marginRight5px" data-clipboard-target="relatedFieldValue" id="relatedFieldsValueCopy"  title="{vtranslate('LBL_FIELD', $QUALIFIED_MODULE)}"><span class="glyphicon glyphicon-download-alt"></span></button>
					<input type="hidden" value="" id="relatedFieldLabel" /><button class="btn btn-sm btn-warning pull-right marginRight5px" data-clipboard-target="relatedFieldLabel" id="relatedFieldsLabelCopy"  title="{vtranslate('LBL_LABEL', $QUALIFIED_MODULE)}"><span class="glyphicon glyphicon-download-alt"></span></button>
				</div>
			</div>
			<div class="form-group row">
				<label class="col-sm-2 control-label">
					{vtranslate('LBL_SPECIAL_FUNCTIONS', $QUALIFIED_MODULE)}
				</label>
				<div class="col-sm-3 controls">
					<select class="chzn-select form-control" name="special_functions" id="special_functions">
						{foreach from=$SPECIAL_FUNCTIONS key=FUNCTION item=NAME name=SPECIALFUNCTIONS}
							<option value="{$FUNCTION}">{$NAME}</option>
						{/foreach}
					</select>
				</div>
				<label class="col-sm-2 control-label">
					<input type="hidden" value="" id="specialFieldValue" /><button class="btn btn-sm btn-info pull-left marginRight5px" data-clipboard-target="special_functions" id="specialFieldValueCopy"  title="{vtranslate('LBL_FIELD', $QUALIFIED_MODULE)}"><span class="glyphicon glyphicon-download-alt"></span></button>
						{vtranslate('LBL_COMPANY_FIELDS', $QUALIFIED_MODULE)}
				</label>
				<div class="col-sm-4 controls">
					<select class="chzn-select form-control" name="company_fields">
						{foreach from=Settings_PDF_Module_Model::getCompanyFields() key=FIELD item=NAME}
							<option value="{$FIELD}">{$NAME}</option>
						{/foreach}
					</select>
				</div>
				<div class="col-md-1 input-group">
					<input type="hidden" value="" id="companyFieldValue" /><button class="btn btn-sm btn-info pull-right marginRight5px" data-clipboard-target="companyFieldValue" id="companyFieldsValueCopy"  title="{vtranslate('LBL_FIELD', $QUALIFIED_MODULE)}"><span class="glyphicon glyphicon-download-alt"></span></button>
					<input type="hidden" value="" id="companyFieldLabel" /><button class="btn btn-sm btn-warning pull-right marginRight5px" data-clipboard-target="companyFieldLabel" id="companyFieldsLabelCopy"  title="{vtranslate('LBL_LABEL', $QUALIFIED_MODULE)}"><span class="glyphicon glyphicon-download-alt"></span></button>
				</div>
			</div>
		</div>
	</div>
{/strip}
