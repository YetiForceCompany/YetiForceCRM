{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{if empty($ALL_ACTIVEUSER_LIST)}
		{assign var=ALL_ACTIVEUSER_LIST value=$USER_MODEL->getAccessibleUsers('Public')}
	{/if}
	<div class="panelItem">
		<div class="panel panel-default" data-index="{$INDEX}" data-module="{$MODULE_ID}">
			<div class="panel-heading">
				{assign 'MODULE_NAME' Vtiger_Functions::getModuleName($MODULE_ID)}
				{vtranslate($MODULE_NAME, $MODULE_NAME)}
			</div>
			{assign var=ALL_ACTIVEGROUP_LIST value=$USER_MODEL->getAccessibleGroups('Public',$MODULE_NAME)}
			<div class="panel-body padding5">
				<div class="col-xs-12 col-sm-5 paddingLRZero">
					<div class="table-responsive">
						<div class="col-xs-12">
							<table class="table table-bordered table-condensed dataTable">
								<thead>
									<tr>
										<th><strong>{vtranslate('LBL_USER',$QUALIFIED_MODULE)}</strong></th>
									</tr>
								</thead>
								<tbody class="dropContainer">
									{foreach from=$DATA key=TYPE item=IDS}
										{foreach from=$IDS item=ID}
											{if $TYPE eq 'users'}
												{assign 'USER_NAME' $ALL_ACTIVEUSER_LIST[$ID]}
											{else}
												{assign 'USER_NAME' $ALL_ACTIVEGROUP_LIST[$ID]}
											{/if}	
											{if $USER_NAME}
												<tr class="dragDrop{$INDEX}" data-id="{$ID}" data-type="{$TYPE}">
													<td>{$USER_NAME}</td>
												</tr>
											{/if}
										{/foreach}
									{/foreach}
								</tbody>
							</table>
						</div>
					</div>
				</div>
				<div class="col-xs-12 col-sm-2"></div>
				<div class="col-xs-12 col-sm-5 paddingLRZero">
					<div class="table-responsive">
						<div class="col-xs-12">
							<table class="table table-bordered table-condensed dataTable" data-mode="base">
								<thead>
									<tr>
										<th><strong>{vtranslate('LBL_USER',$QUALIFIED_MODULE)}</strong></th>
									</tr>
								</thead>
								<tbody class="dropContainer">
									{foreach from=$ALL_ACTIVEUSER_LIST key=ID item=USER_NAME}
									{if $DATA && in_array($ID, $DATA['users'])}{continue}{/if}
									<tr class="dragDrop{$INDEX}" data-id="{$ID}" data-type="users">
										<td>{$USER_NAME}</td>
									</tr>
								{/foreach}
								{foreach from=$ALL_ACTIVEGROUP_LIST key=ID item=USER_NAME}
								{if $DATA && in_array($ID, $DATA['groups'])}{continue}{/if}
								<tr class="dragDrop{$INDEX}" data-id="{$ID}" data-type="groups">
									<td>{$USER_NAME}</td>
								</tr>
							{/foreach}
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
</div>
{/strip}
