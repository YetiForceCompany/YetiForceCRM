{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<div id="accountHierarchyContainer" class="modelContainer modal fade" taindex="-1">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button class="close" data-dismiss="modal" title="{\App\Language::translate('LBL_CLOSE')}">x</button>
				<h3 class="modal-title">{\App\Language::translate('LBL_SHOW_EMPLOYEES_HIERARCHY', $MODULE)}</h3>
			</div>
			<div class="modal-body">
				<div id ="hierarchyScroll" class="table-responsive">
					<table class="table table-bordered">
						<thead>
							<tr class="blockHeader">
							{foreach item=HEADERNAME from=$EMPLOYEES_HIERARCHY['header']}
								<th>{\App\Language::translate($HEADERNAME, $MODULE)}</th>
							{/foreach}
							</tr>
						</thead>
					{foreach item=ENTRIES from=$EMPLOYEES_HIERARCHY['entries']}
						<tbody>
							<tr>
							{foreach item=LISTFIELDS from=$ENTRIES}
								<td>{$LISTFIELDS}</td>
							{/foreach}
							</tr>
						</tbody>
					{/foreach}
					</table>
				</div>
			</div>
			<div class="modal-footer">
				<div class="float-right cancelLinkContainer">
					<button class="btn btn-primary" type="reset" data-dismiss="modal"><strong>{\App\Language::translate('LBL_CLOSE', $MODULE)}</strong></button>
				</div>
			</div>
		</div>
	</div>
</div>
    {/strip}
