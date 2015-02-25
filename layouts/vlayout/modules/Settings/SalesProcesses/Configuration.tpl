{*/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/*}
 
<style>
.fieldDetailsForm .zeroOpacity{
display: none;
}
.visibility{
visibility: hidden;
}
.marginLeft20{
margin-left: 20px;
}
.marginRight20{
	margin-right: 20px;
}
.marginTop5{
	margin-top: 5px;
}
.paddingNoTop20{
padding: 20px 20px 20px 20px;
}
</style>
<div class="container-fluid" id="widgetsManagementEditorContainer">
	<div class="widget_header row-fluid">
		<div class="span12">
			<h3>{vtranslate('LBL_SALES_PROCESSES', $QUALIFIED_MODULE)}</h3>
			{vtranslate('LBL_SALES_PROCESSES_DESCRIPTION', $QUALIFIED_MODULE)}
		</div>
	</div>

	<div class="contents tabbable">
		<div class="tab-content layoutContent paddingNoTop20 themeTableColor overflowVisible">
			<table>
				<tr>
					<td><input type="checkbox" name="productsRel2PotentialsOnly" id="productsRel2PotentialsOnly" value="{$CONFIG['products_rel_potentials']}" {if $CONFIG['products_rel_potentials'] eq 1} checked{/if}/></td>
					<td style="padding-left:10px;"><label for="productsRel2PotentialsOnly">{vtranslate('LBL_PRODUCTS_REL_DESCRIPTION', $QUALIFIED_MODULE)}</label></td>
				</tr>
			</table>
		</div>
	</div>
</div>

