{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{assign var="SUPMODULE" value='Supplies'}
	{assign var="FIELDS" value=$SUPFIELD_MODEL->getFields($MODULE, true)}
	{if count($FIELDS) neq 0}
		{assign var="COLUMNS" value=$SUPFIELD_MODEL->getColumns($MODULE)}
		{assign var="SUP_RECORD_DATA" value=$RECORD->getSupplieData()}
		{assign var="MAIN_PARAMS" value=$SUPFIELD_MODEL->getMainParams($FIELDS[1])}
		{assign var="BLACK_SUP_DATA" value=[]}

		<input name="suppliesRowNo" id="suppliesRowNo" type="hidden" value="{count($SUP_RECORD_DATA)}" />
		<input id="accountReferenceField" type="hidden" value="{$ACCOUNT_REFERENCE_FIELD}" />
		{if count($FIELDS[0]) neq 0}
			<table class="table table-bordered blockContainer suppliesItemTable">
				<thead>
					<tr data-rownumber="0">
						{foreach item=FIELD from=$FIELDS[0]}
							<th colspan="{$FIELD->get('colspan')}">
								<span class="inventoryLineItemHeader">{vtranslate($FIELD->get('label'), $SUPMODULE)}</span>&nbsp;&nbsp;
								{assign var="FIELD_TPL_NAME" value="fields/"|cat:$FIELD->getTemplateName('EditView')}
								{include file=$FIELD_TPL_NAME|@vtemplate_path:Supplies_Module_Model::getModuleNameForTpl($FIELD_TPL_NAME,$MODULE) SUP_VALUE=$SUP_RECORD_DATA[0][$FIELD->get('columnname')]}
							</th>
						{/foreach}
					</tr>

				</thead>
			</table>
		{/if}
		<table class="table table-bordered blockContainer suppliesItemTable">
			<thead>
				<tr>
					<th width="50px">&nbsp;&nbsp;</th>
					{foreach item=FIELD from=$FIELDS[1]}
						<th colspan="{$FIELD->get('colspan')}" class="col{$FIELD->getName()}">{vtranslate($FIELD->get('label'), $SUPMODULE)}</th>
					{/foreach}
				</tr>
			</thead>
			<tbody>
				{foreach key=KEY item=SUP_DATA from=$SUP_RECORD_DATA}
					{assign var="ROW_NO" value=$KEY+1}
					<tr class="rowSup">
						<td>
							<span class="glyphicon glyphicon-trash deleteRow cursorPointer {if $KEY == 0 }hide{/if}" title="{vtranslate('LBL_DELETE',$SUPMODULE)}"></span>
							&nbsp;&nbsp;<a class="dragHandle"><img src="{vimage_path('drag.png')}" border="0" alt="{vtranslate('LBL_DRAG',$SUPMODULE)}"/></a>
							<input name="seq{$ROW_NO}" type="hidden" value="{$ROW_NO}" class="sequence" />
						</td>
						{foreach item=FIELD from=$FIELDS[1]}
							<td class="col{$FIELD->getName()}">
								{assign var="FIELD_TPL_NAME" value="fields/"|cat:$FIELD->getTemplateName('EditView')}
								{include file=$FIELD_TPL_NAME|@vtemplate_path:Supplies_Module_Model::getModuleNameForTpl($FIELD_TPL_NAME,$MODULE) SUP_VALUE=$SUP_DATA[$FIELD->get('columnname')]}
							</td>
						{/foreach}
					</tr>
				{/foreach}
			</tbody>
			<tfoot>
				<tr>
					<th class="btn-toolbar" colspan="{count($FIELDS[1]) + 1}">
						{foreach item=MAIN_MODULE from=$MAIN_PARAMS['modules']}
							{assign var="CRMEntity" value=CRMEntity::getInstance($MAIN_MODULE)}
							<span class="btn-group">
								<button type="button" data-module="{$MAIN_MODULE}" data-field="{$CRMEntity->table_index}" 
										data-wysiwyg="{Supplies_EditView_Model::isWysiwygType($MAIN_MODULE)}" class="btn btn-default addButton">
									<span class="glyphicon glyphicon-plus"></span>&nbsp;<strong>{vtranslate('LBL_ADD',$SUPMODULE)} {vtranslate('SINGLE_'|cat:$MAIN_MODULE,$MAIN_MODULE)}</strong>
								</button>
							</span>
						{/foreach}
					</th>
				</tr>
			</tfoot>
		</table>
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-6">

				</div>
				<div class="col-md-6">
					<table class="table table-bordered blockContainer suppliesSumaryTable">
						<tbody>
							{foreach item=FIELD from=$FIELDS[2]}
								<tr data-rownumber="1">
									<td>
										xx
									</td>
									<td>
										vv
									</td>
								</tr>
							{/foreach}
						</tbody>
					</table>
				</div>
			</div>
		</div>
		{assign var="SUP_DATA" value=[]}
		<table id="blackSuppliesTable" class="noValidate" style="display: none;">
			<tbody>
				{assign var="ROW_NO" value='_NUM_'}
				<tr class="rowSup">
					<td>
						<span class="glyphicon glyphicon-trash deleteRow cursorPointer {if $KEY == 0 }hide{/if}" title="{vtranslate('LBL_DELETE',$SUPMODULE)}"></span>
						&nbsp;&nbsp;<a class="dragHandle"><img src="{vimage_path('drag.png')}" border="0" alt="{vtranslate('LBL_DRAG',$SUPMODULE)}"/></a>
						<input name="seq{$ROW_NO}" type="hidden" value="{$ROW_NO}" class="sequence" />
					</td>
					{foreach item=FIELD from=$FIELDS[1]}
						<td>
							{assign var="FIELD_TPL_NAME" value="fields/"|cat:$FIELD->getTemplateName('EditView')}
							{include file=$FIELD_TPL_NAME|@vtemplate_path:Supplies_Module_Model::getModuleNameForTpl($FIELD_TPL_NAME,$MODULE) SUP_VALUE='0'}
						</td>
					{/foreach}
				</tr>
			</tbody>
		</table>
		<br/>
	{/if}
{/strip}
