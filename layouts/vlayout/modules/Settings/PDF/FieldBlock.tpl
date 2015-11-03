{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="FieldBlock leftRightPadding3p">
		<div class="form-group">
			<div class="form-group row">
				<label class="col-sm-2 control-label">
					{vtranslate('LBL_MAIN_MODULE', $QUALIFIED_MODULE)}
				</label>
				<div class="col-sm-3 controls">
					<input type="hidden" name="module_name" value="{$PDF_MODEL->get('module_name')}" />
					<select class="chzn-select form-control" name="module_name_select" disabled="disabled">
						{foreach from=$ALL_MODULES key=TABID item=MODULE_MODEL}
							{if $PDF_MODEL->get('module_name') == $MODULE_MODEL->getName()}
								<option value="{$MODULE_MODEL->getName()}" selected="selected">
									{if $MODULE_MODEL->getName() eq 'Calendar'}
										{vtranslate('LBL_TASK', $MODULE_MODEL->getName())}
									{else}
										{vtranslate($MODULE_MODEL->getName(), $MODULE_MODEL->getName())}
									{/if}
								</option>
							{/if}
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
					<input type="hidden" value="" id="mainFieldValue{$STEP_NO}" /><button class="btn btn-sm btn-info pull-right marginRight5px" data-clipboard-target="mainFieldValue{$STEP_NO}" id="mainFieldsValueCopy{$STEP_NO}"  title="{vtranslate('LBL_FIELD', $QUALIFIED_MODULE)}"><span class="glyphicon glyphicon-download-alt"></span></button>
					<input type="hidden" value="" id="mainFieldLabel{$STEP_NO}" /><button class="btn btn-sm btn-warning pull-right marginRight5px" data-clipboard-target="mainFieldLabel{$STEP_NO}" id="mainFieldsLabelCopy{$STEP_NO}"  title="{vtranslate('LBL_LABEL', $QUALIFIED_MODULE)}"><span class="glyphicon glyphicon-download-alt"></span></button>
				</div>
			</div>
			<div class="form-group row">
				<label class="col-sm-2 control-label">
					{vtranslate('LBL_RELATED_MODULES', $QUALIFIED_MODULE)}
				</label>
				<div class="col-sm-3 controls">
					<select class="chzn-select form-control" name="related_module">
						{foreach from=Settings_PDF_Module_Model::getRelatedModules($PDF_MODEL->get('module_name')) key=KEY item=NAME}
							<option value="{$KEY}">{vtranslate($NAME['moduleName'] ,$NAME['moduleName'])} ({vtranslate($NAME['label'], $PDF_MODEL->get('module_name'))})</option>
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
					<input type="hidden" value="" id="relatedFieldValue{$STEP_NO}" /><button class="btn btn-sm btn-info pull-right marginRight5px" data-clipboard-target="relatedFieldValue{$STEP_NO}" id="relatedFieldsValueCopy{$STEP_NO}"  title="{vtranslate('LBL_FIELD', $QUALIFIED_MODULE)}"><span class="glyphicon glyphicon-download-alt"></span></button>
					<input type="hidden" value="" id="relatedFieldLabel{$STEP_NO}" /><button class="btn btn-sm btn-warning pull-right marginRight5px" data-clipboard-target="relatedFieldLabel{$STEP_NO}" id="relatedFieldsLabelCopy{$STEP_NO}"  title="{vtranslate('LBL_LABEL', $QUALIFIED_MODULE)}"><span class="glyphicon glyphicon-download-alt"></span></button>
				</div>
			</div>
			<div class="form-group row">
				<label class="col-sm-2 control-label">
					{vtranslate('LBL_SPECIAL_FUNCTIONS', $QUALIFIED_MODULE)}
				</label>
				<div class="col-sm-3 controls">
					<select class="chzn-select form-control" name="special_functions" id="special_functions">
						{foreach from=$SPECIAL_FUNCTIONS key=FUNCTION item=NAME name=SPECIALFUNCTIONS}
							<option value="{$FUNCTION}">{vtranslate($FUNCTION, $QUALIFIED_MODULE)}</option>
						{/foreach}
					</select>
				</div>
				<label class="col-sm-2 control-label">
					<input type="hidden" value="" id="specialFieldValue{$STEP_NO}" /><button class="btn btn-sm btn-info pull-left marginRight5px" data-clipboard-target="specialFieldValue{$STEP_NO}" id="specialFieldValueCopy{$STEP_NO}"  title="{vtranslate('LBL_FIELD', $QUALIFIED_MODULE)}"><span class="glyphicon glyphicon-download-alt"></span></button>
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
					<input type="hidden" value="" id="companyFieldValue{$STEP_NO}" /><button class="btn btn-sm btn-info pull-right marginRight5px" data-clipboard-target="companyFieldValue{$STEP_NO}" id="companyFieldsValueCopy{$STEP_NO}"  title="{vtranslate('LBL_FIELD', $QUALIFIED_MODULE)}"><span class="glyphicon glyphicon-download-alt"></span></button>
					<input type="hidden" value="" id="companyFieldLabel{$STEP_NO}" /><button class="btn btn-sm btn-warning pull-right marginRight5px" data-clipboard-target="companyFieldLabel{$STEP_NO}" id="companyFieldsLabelCopy{$STEP_NO}"  title="{vtranslate('LBL_LABEL', $QUALIFIED_MODULE)}"><span class="glyphicon glyphicon-download-alt"></span></button>
				</div>
			</div>
			{if $STEP_NO eq 3 || $STEP_NO eq 5}
				<div class="form-group row">
					<label class="col-sm-2 control-label">
						{vtranslate('LBL_INSERT', $QUALIFIED_MODULE)}
					</label>
					<div class="col-sm-3 controls">
						<select class="chzn-select form-control" name="insert_functions" id="insert_functions">
							{foreach from=$INSERT key=KEY item=OPERATION name=INSERT_OPERATIONS}
								<option value="{$OPERATION}">{vtranslate($KEY, $QUALIFIED_MODULE)}</option>
							{/foreach}
						</select>
					</div>
					<label class="col-sm-2 control-label">
						<input type="hidden" value="" id="insertFieldValue{$STEP_NO}" /><button class="btn btn-sm btn-info pull-left marginRight5px" data-clipboard-target="insertFieldValue{$STEP_NO}" id="insertFieldValueCopy{$STEP_NO}"  title="{vtranslate('LBL_FIELD', $QUALIFIED_MODULE)}"><span class="glyphicon glyphicon-download-alt"></span></button>
					</label>
				</div>
			{/if}
		</div>
	</div>
{/strip}
