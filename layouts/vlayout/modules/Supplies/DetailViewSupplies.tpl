{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{assign var="FIELDS" value=Supplies_SupField_Model::getFields($SUP_MODULE, true)}
	{if count($FIELDS) neq 0}
		{assign var="SUPMODULE" value='Supplies'}
		{assign var="COLUMNS" value=Supplies_SupField_Model::getColumns($SUP_MODULE)}
		{assign var="SUP_RECORD_DATA" value=$RECORD->getSupplieData()}
		{assign var="SUP_RECORD_DATA" value=Supplies_SupField_Model::getRecordData($SUP_MODULE, $SUP_RECORD)}
		{assign var="MAIN_PARAMS" value=Supplies_SupField_Model::getMainParams($FIELDS[1])}
		{assign var="COUNT_FIELDS0" value=count($FIELDS[0])}
		{assign var="COUNT_FIELDS1" value=count($FIELDS[1])}
		{assign var="COUNT_FIELDS2" value=count($FIELDS[2])}
		<table class="table table-bordered blockContainer suppliesItemTable">
			<thead>
				{if count($FIELDS[0]) neq 0}
					<tr>
						<th><strong>{vtranslate('LBL_ITEM_DETAILS', $SUP_MODULE)}</strong></th>
								{foreach item=FIELD from=$FIELDS[0]}
							<th colspan="{$FIELD->get('colspan')}">
								{assign var="FIELD_TPL_NAME" value="fields/"|cat:$FIELD->getTemplateName('DetailView')}
								{include file=$FIELD_TPL_NAME|@vtemplate_path:Supplies_Module_Model::getModuleNameForTpl($FIELD_TPL_NAME,$SUP_MODULE) SUP_VALUE=$SUP_DATA[$FIELD->get('columnname')]}
							</th>
						{/foreach}
					</tr>
				{/if}
				<tr>
					{foreach item=FIELD from=$FIELDS[1]}
						<th colspan="{$FIELD->get('colspan')}">{vtranslate($FIELD->get('label'), $SUP_MODULE)}</th>
						{/foreach}
				</tr>
			</thead>
			<tbody>
				{foreach key=KEY item=SUP_DATA from=$SUP_RECORD_DATA}
					{assign var="ROW_NO" value=$KEY+1}
					<tr>
						{foreach item=FIELD from=$FIELDS[1]}
							<td>
								{assign var="FIELD_TPL_NAME" value="fields/"|cat:$FIELD->getTemplateName('DetailView')}
								{include file=$FIELD_TPL_NAME|@vtemplate_path:Supplies_Module_Model::getModuleNameForTpl($FIELD_TPL_NAME,$SUP_MODULE) SUP_VALUE=$SUP_DATA[$FIELD->get('columnname')]}
							</td>
						{/foreach}
					</tr>
				{/foreach}
			</tbody>
		</table>
	{/if}
{/strip}
