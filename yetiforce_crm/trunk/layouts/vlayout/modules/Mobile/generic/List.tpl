{include file="modules/Mobile/generic/Header.tpl"}

<body>

<div id="__listview__" {if $_MODE eq 'search'}style='display:none;'{/if}>
	<table width=100% cellpadding=0 cellspacing=0 border=0>
	<tr class="toolbar">
		<td><a class="link" href="javascript:window.close();"><img src="resources/images/iconza/royalblue/undo_32x32.png" border="0"></a></td>
		<td width="100%">
			<h1 class='page_title'>
			
			{if $_PAGER && $_PAGER->hasPrevious()}
			<a class="link" href="?_operation=listModuleRecords&module={$_MODULE->name()}&page={$_PAGER->previous()}&search={$_SEARCH}"><img src="resources/images/iconza/yellow/left_arrow_24x24.png" border="0"></a>
			{else}
			<a class="link" href="javascript:void(0);"><img src="resources/images/iconza/white/left_arrow_24x24.png" border="0"></a>
			{/if}
			
			{$_MODULE->label()}
			
			{if $_PAGER && $_PAGER->hasNext(count($_RECORDS))}
			<a class="link" href="?_operation=listModuleRecords&module={$_MODULE->name()}&page={$_PAGER->next()}&search={$_SEARCH}"><img src="resources/images/iconza/yellow/right_arrow_24x24.png" border="0"></a>
			{else}
			<a class="link" href="javascript:void(0);"><img src="resources/images/iconza/white/right_arrow_24x24.png" border="0"></a>
			{/if}
			
			</h1>
		</td>
		<td align="right" style="padding-right: 5px;"><a class="link" href="javascript:void(0);" onclick="$fnT('__listview__', '__searchbox__'); $fnFocus('__searchbox__q_');" target="_self"><img src="resources/images/iconza/yellow/lens_32x32.png" border="0"></a></td>
	</tr>
	
	<tr>
		<td colspan="3">	
		
			<table width=100% cellpadding=0 cellspacing=0 border=0 class="table_list">
				{foreach item=_RECORD from=$_RECORDS}
				<tr>
				<td width="100%">
					<a href="?_operation=fetchRecordWithGrouping&record={$_RECORD->id()}" target="_self">{$_RECORD->label()}</a>
				</td>
				<td>
					<a href="?_operation=fetchRecordWithGrouping&record={$_RECORD->id()}" target="_self" class="link_rhook"><img src="resources/images/iconza/royalblue/right_arrow_16x16.png" border="0"></a>								
				</td>
				</tr>
				
				{foreachelse}
				
				<tr class="info">
				<td width=25% align="right">
					<img src="resources/images/iconza/royalblue/info_24x24.png" border=0 />
				</td>
				<td width=100% align="left" valign="center">
					{if $_PAGER->hasPrevious()}
					<p>No more records found.</p>
					{else}
					<p>No records available.</p>
					{/if}
				</td>
				</tr>
				
				{/foreach}
			</table>
		
		</td>
	</tr>
	</table>
</div>

<div id="__searchbox__" {if $_MODE neq 'search'}style='display:none;'{/if}>
	<table width=100% cellpadding=0 cellspacing=0 border=0>
	<tr class="toolbar">
		<td><a class="link" href="?_operation=searchConfig&module={$_MODULE->name()}" target="_self"><img src="resources/images/iconza/yellow/wrench_32x32.png" border="0"></a></td>
		<td width="100%">
			<h1 class='page_title'>
			Search {$_MODULE->label()}
			</h1>
		</td>
		<td align="right" style="padding-right: 5px;"><a class="link" href="javascript:void(0);" onclick="$fnT('__searchbox__','__listview__');"><img src="resources/images/iconza/yellow/zoom_out_32x32.png" border="0"></a></td>
	</tr>
	
	<tr>
		<td colspan=3 align="center">
		
			<form action="index.php" method="GET" onsubmit="if(this.search.value == '') return false;">
				<input type="hidden" name="_operation" value="listModuleRecords" />
				<input type="hidden" name="module" value="{$_MODULE->name()}" />
				<input id='__searchbox__q_' type="text" name="search" class="searchbox" value="{$_SEARCH}"/>
			</form>
		
		</td>
		
	</tr>
	</table>
</div>

</body>

{include file="modules/Mobile/generic/Footer.tpl"}