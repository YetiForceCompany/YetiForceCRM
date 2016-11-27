{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div id="salesProcessesHierarchyContainer" class="modelContainer modal fade" tabindex="-1">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<button class="close" data-dismiss="modal" title="{vtranslate('LBL_CLOSE')}">x</button>
					<h3 class="modal-title">{vtranslate('LBL_SHOW_HIERARCHY', $MODULE)}</h3>
				</div>
				<div class="modal-body">
					<div id ="hierarchyScroll" style="margin-right: 8px;">
						<table class="table table-bordered">
							<thead>
								<tr class="blockHeader">
								{foreach item=HEADERNAME from=$HIERARCHY['header']}
									<th>{vtranslate($HEADERNAME, $MODULE)}</th>
								{/foreach}
								</tr>
							</thead>
						{foreach item=ENTRIES from=$HIERARCHY['entries']}
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
					<div class=" pull-right cancelLinkContainer">
						<button class="btn btn-warning" type="reset" data-dismiss="modal"><strong>{vtranslate('LBL_CLOSE', $MODULE)}</strong></button>
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}
