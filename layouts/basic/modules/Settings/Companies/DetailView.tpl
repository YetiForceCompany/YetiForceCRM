{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
	<div class="widget_header row">
		<div class="col-md-8">
			{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
			{if isset($SELECTED_PAGE)}
				{vtranslate($SELECTED_PAGE->get('description'),$QUALIFIED_MODULE)}
			{/if}
		</div>
		<div class="col-md-4 ">
			<a href="{$RECORD_MODEL->getEditViewUrl()}" class="btn btn-info pull-right">
				<strong>{vtranslate('LBL_EDIT_RECORD', $QUALIFIED_MODULE)}</strong>
			</a>
		</div>
	</div>
	<div class="detailViewInfo" id="groupsDetailContainer">
		<div class="">
			<form id="detailView" class="form-horizontal" method="POST">
				<div class="form-group">
					<div class="col-md-2 text-right">
						{vtranslate('LBL_NAME', $QUALIFIED_MODULE)} 
					</div>
					<div class="col-md-10">
						<strong>{$RECORD_MODEL->getName()}</strong>
					</div>
				</div>
				<div class="form-group">
					<div class="col-md-2 text-right">
						{vtranslate('LBL_ACTION', $QUALIFIED_MODULE)}  
					</div>
					<div class="col-md-10">
						<strong>{vtranslate($RECORD_MODEL->getDisplayValue('action'), $QUALIFIED_MODULE)}</strong>
					</div>
				</div>
				<div class="form-group">
					<div class="col-md-2 text-right">
						{vtranslate('LBL_STATUS', $QUALIFIED_MODULE)}  
					</div>
					<div class="col-md-10">
						<strong>{vtranslate($RECORD_MODEL->getDisplayValue('status'), $QUALIFIED_MODULE)}</strong>
					</div>
				</div>
				<div class="form-group">
					<div class="col-md-2 text-right">
						{vtranslate('LBL_PRIORITY', $QUALIFIED_MODULE)}  
					</div>
					<div class="col-md-10">
						<strong>{vtranslate($RECORD_MODEL->getDisplayValue('priority'))}</strong>
					</div>
				</div>
				<div class="form-group">
					<div class="col-md-2 text-right">
						{vtranslate('LBL_MODULE', $QUALIFIED_MODULE)}  
					</div>
					<div class="col-md-10">
						<strong>{vtranslate($RECORD_MODEL->getDisplayValue('tabid'), $RECORD_MODEL->getDisplayValue('tabid'))}</strong>
					</div>
				</div>
				<div class="form-group">
					<div class="col-md-2 text-right">
						{vtranslate('LBL_MEMBERS', $QUALIFIED_MODULE)}  
					</div>
					<div class="col-md-10">
						<strong>{$RECORD_MODEL->getDisplayValue('members')}</strong>
					</div>
				</div>
				<div class="form-group">
					<div class="col-md-2 text-right">
						{vtranslate('LBL_USERS', $QUALIFIED_MODULE)}  
					</div>
					<div class="col-md-10">
						{foreach from=$RECORD_MODEL->getUserByMember() item=NAME}
							<div><strong>{$NAME}</strong></div>
						{/foreach}
					</div>
				</div>
			</form>
		</div>
	</div>
	{strip}
