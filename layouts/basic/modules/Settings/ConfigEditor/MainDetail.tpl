{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-ConfigEditor-MainDetail -->
	<div class="contents">
		<table class="table tableRWD table-bordered table-sm themeTableColor">
			<thead>
				<tr class="blockHeader">
					<th colspan="2" class="{$WIDTHTYPE}">
						<span class="alignMiddle">{\App\Language::translate('LBL_MAIN_CONFIG', $QUALIFIED_MODULE)}</span>
						<div class="float-right">
							<button class="btn btn-success editButton" data-url='{$MODEL->getEditViewUrl()}' type="button"
								title="{\App\Language::translate('LBL_EDIT', $QUALIFIED_MODULE)}"><span
									class="fa fa-edit u-mr-5px"></span><strong>{\App\Language::translate('LBL_EDIT', $QUALIFIED_MODULE)}</strong>
							</button>
						</div>
					</th>
				</tr>
			</thead>
			<tbody>
				{foreach key=FIELD_NAME item=FIELD_LABEL from=$MODEL->listFields}
					{assign var="FIELD_MODEL" value=$MODEL->getFieldInstanceByName($FIELD_NAME)->set('fieldvalue',$MODEL->get($FIELD_NAME))}
					<tr>
						<td width="30%" class="{$WIDTHTYPE} textAlignRight">
							<label class="muted marginRight10px">
								{\App\Language::translate($FIELD_LABEL, $QUALIFIED_MODULE)}
							</label>
						</td>
						<td style="border-left: none;" class="{$WIDTHTYPE}">
							{$MODEL->getDisplayValue($FIELD_NAME)}
						</td>
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
	<!-- /tpl-Settings-ConfigEditor-MainDetail -->
{/strip}
