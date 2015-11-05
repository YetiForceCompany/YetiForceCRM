{include file="modules/Mobile/generic/Header.tpl"}

<body>

<table width=100% cellpadding=0 cellspacing=0 border=0>
<tr class="toolbar">
	<td><a class="link" href="?_operation=listModuleRecords&module={$_MODULE->name()}"><img src="resources/images/iconza/royalblue/left_arrow_24x24.png" border="0"></a></td>
	<td width="100%">
		<h1 class='page_title'>
		Search {$_MODULE->label()}
		</h1>
	</td>
	<td align="right" style="padding-right: 5px;"><button onclick="$('_searchconfig_form_').submit();">Save</button></td>
</tr>
	
<tr>
	<td colspan="3">	
	
		<form method="POST" action="?_operation=searchConfig&mode=update&module={$_MODULE->name()}" id="_searchconfig_form_">
	
		<table width=100% cellpadding=8 cellspacing=0 border=0 class="table_detail">
			{foreach item=_BLOCK key=_BLOCKLABEL from=$_RECORD->blocks()}
			
			<tr>
				<td colspan=2 class="hdrlabel">{$_BLOCKLABEL}</td>
			</tr>
			
			{foreach item=_FIELD from=$_BLOCK->fields()}
			{assign var="_FIELDNAME" value=$_FIELD->name()}
			
			<tr>
				<th align="right" class="label2" nowrap="nowrap" width="10%">{$_FIELD->label()}</th>
				<td width="100%">
				
				<table cellpadding=0 cellspacing=0 border=0 class="table_checkbox">
				<tr>
					<td>
					
					{assign var=_checkbox_on_checked value='false'}
					{assign var=_checkbox_off_checked value='true'}
					
					{assign var=_checkbox_on_class value='on'}
					{assign var=_checkbox_off_class value='off hide'}
					
					{if in_array($_FIELDNAME, $_SEARCHIN) || $_SEARCHIN_ALL }
						{assign var=_checkbox_on_checked value='true'}
						{assign var=_checkbox_off_checked value='false'}
						
						{assign var=_checkbox_on_class value='on hide'}
						{assign var=_checkbox_off_class value='off'}
					{/if}
					
					<div class='{$_checkbox_on_class}'>
					<a href='javascript:void(0);' id='_checkbox_{$_FIELDNAME}_on' onclick="$('include_{$_FIELDNAME}').checked=true;$fnCheckboxOn('_checkbox_{$_FIELDNAME}');">ON</a>
					</div>
					
					<div class='{$_checkbox_off_class}'>
					<a href='javascript:void(0);' id='_checkbox_{$_FIELDNAME}_off' onclick="$('include_{$_FIELDNAME}').checked=false;$fnCheckboxOff('_checkbox_{$_FIELDNAME}');">OFF</a>
					</div>
					
					</td>
				</tr>
				</table>
				
				<input id='include_{$_FIELDNAME}' name="field_{$_FIELDNAME}" type="checkbox" class="input_checkbox" style="display: none;" {if $_checkbox_on_checked eq 'true'}checked=true{/if}>
				
				</td>
			</tr>
			{/foreach}
			
			{/foreach}
		</table>
		
		</form>
	
	</td>
</tr>
</table>


</body>

{include file="modules/Mobile/generic/Footer.tpl"}
