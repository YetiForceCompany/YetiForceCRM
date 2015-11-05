<style>
	.blockHeader th{
		text-align:center !important; 
		vertical-align:middle !important; 
	}
	.confTable td, label, span{
		text-align:center !important; 
		vertical-align:middle !important; 
	}	
</style>
<div class="" style="margin-top:10px;">
	<div class="row">
		<div class="col-md-7">
			<h3>{vtranslate('LBL_UPDATES', $MODULE)}</h3>&nbsp;{vtranslate('LBL_UPDATES_DESCRIPTION', $MODULE)}
		</div>
		<div class="col-md-5">
			<div class="pull-right">
				<a class="btn btn-success addMenu" href="{Settings_ModuleManager_Module_Model::getUserModuleImportUrl()}"><strong>{vtranslate('LBL_IMPORT_UPDATE', $QUALIFIED_MODULE)}</strong></a>
			</div>
		</div>
	</div>
	<hr>
	<table class="table table-bordered table-condensed themeTableColor confTable">
		<thead>
			<tr class="blockHeader">
				<th colspan="1" class="mediumWidthType">
					<span>{vtranslate('LBL_TIME', $MODULE)}</span>
				</th>    
				<th colspan="1" class="mediumWidthType">
					<span>{vtranslate('LBL_USER', $MODULE)}</span>
				</th>   
				</th>   
				<th colspan="1" class="mediumWidthType">
					<span>{vtranslate('LBL_FROM_VERSION', $MODULE)}</span>
				</th>   
				<th colspan="1" class="mediumWidthType">
					<span>{vtranslate('LBL_TO_VERSION', $MODULE)}</span>
				</th>   
				<th colspan="1" class="mediumWidthType">
					<span>{vtranslate('LBL_RESULT', $MODULE)}</span>
				</th>   								
			</tr>
		</thead>
		<tbody>
			{foreach from=$UPDATES key=key item=foo}						
				<tr>	
					<td width="16%"><label class="marginRight5px">{$foo.time}</label></td>
					<td width="16%"><label class="marginRight5px">{$foo.user}</label></td>
					<td width="16%"><label class="marginRight5px">{$foo.from_version}</label></td>
					<td width="16%"><label class="marginRight5px">{$foo.to_version}</label></td>
					<td width="16%"><label class="marginRight5px">{if $foo.result eq 1}{vtranslate('LBL_YES', $MODULE)}{else}{vtranslate('LBL_NO', $MODULE)}{/if}</label></td>
				</tr>
			{/foreach}
		</tbody>
	</table>
</div>
