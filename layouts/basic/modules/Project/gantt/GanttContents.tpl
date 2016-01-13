{*<!--
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
-->*}
<style>
{foreach from=Vtiger_Module_Model::getAll() item=MODULE}
	.modIcon_{$MODULE->get('name')}{ background-image: url("{Yeti_Layout::getLayoutFile('skins/images/'|cat:$MODULE->get('name')|cat:'.png')}") !important; }
{/foreach}
td{
	padding-left:10px;
}
label{
	display: inline;
}
.weekend{ background: #f4f7f4 !important;}
</style>
<div class="gantt_task_scale" style="width: 100%; padding:5px 0px 5px 0px;">
<table>
	<tr style="run-in">
		<fieldset>
			<legend class="hide">{vtranslate('LBL_FILTERING',$QUALIFIED_MODULE)}</legend>
			<td><strong> {vtranslate('LBL_FILTERING',$QUALIFIED_MODULE)}: &nbsp; </strong></td>
			<td><input name="filter" id="all" class="filter" type="radio" value="" checked="true"><label for="all"><span>&nbsp;{vtranslate('LBL_ALL_PRIORITY',$QUALIFIED_MODULE)}</span></label></td>
			<td><input name="filter" id="low" class="filter" type="radio" value="PLL_LOW"><label for="low"><span>&nbsp;{vtranslate('LBL_LOW_PRIORITY',$QUALIFIED_MODULE)}</span></label></td>
			<td><input name="filter" id="high" class="filter" type="radio" value="PLL_HIGH"><label for="high"><span>&nbsp;{vtranslate('LBL_HIGH_PRIORITY',$QUALIFIED_MODULE)}</span></label></td>
		</fieldset>
		<fieldset>
			<legend class="hide">{vtranslate('LBL_ZOOMING',$QUALIFIED_MODULE)}</legend>
			<td><strong><span>| &nbsp;</span> {vtranslate('LBL_ZOOMING',$QUALIFIED_MODULE)}: </strong></td>
			<td><input name="scales" id="days" class="zoom" type="radio" value="trplweek" checked="true"><label for="days"><span>&nbsp;{vtranslate('LBL_DAYS_CHART',$QUALIFIED_MODULE)}</span></label></td>
			<td><input name="scales" id="months" class="zoom" type="radio" value="year"><label for="months"><span>&nbsp;{vtranslate('LBL_MONTHS_CHART',$QUALIFIED_MODULE)}</span></label></td>
		</fieldset>
	</tr>
</table>
</div>
<div id="gantt_here" style='width:100%; height:500px;'></div>
<script>

$(document).ready(function(){
	// filtering
	gantt.attachEvent("onBeforeTaskDisplay", function(id, task){
		if (gantt_filter){
			value = task.priority ;
			if(typeof value == 'undefined')
				return false;
			var priorityOption = [value.toUpperCase(),'PLL_'+value.toUpperCase()];
			if (jQuery.inArray(gantt_filter, priorityOption) == -1)
				return false;
		}
		return true;
	});
	jQuery('.zoom').on('click',function(){
		value = jQuery(this).val();
		switch(value){
			case "trplweek":
				gantt.config.scale_unit = "month";
				gantt.config.date_scale = "%F, %Y";
				gantt.config.scale_height = 50;
				gantt.config.subscales = [
					{
					unit:"day", 
					step:1,
					date:"%j, %D" }
				];
			break;
			case "year":
				gantt.config.scale_unit = "month"; 
				gantt.config.date_scale = "%F"; 
				gantt.config.scale_height = 50;
				gantt.config.subscales = [
					  {
						  unit:"week",
						  step:1,
						  date:"#%W"
					  }
				];
			break;
		}
		gantt.render();
	});
	var gantt_filter = '';
	jQuery('.filter').on('click',function(node){
		gantt_filter = jQuery(this).val();
		gantt.refreshData();
	});

	// cell painting
	gantt.templates.task_cell_class = function(item,date){
		if(date.getDay()==0||date.getDay()==6){ 
			return "weekend" ;
		}
	};

	gantt.locale.date = {
		month_full:[app.vtranslate('JS_JANUARY'), app.vtranslate('JS_FEBRUARY'), app.vtranslate('JS_MARCH'),
		app.vtranslate('JS_APRIL'), app.vtranslate('JS_MAY'), app.vtranslate('JS_JUNE'), app.vtranslate('JS_JULY'),
		app.vtranslate('JS_AUGUST'), app.vtranslate('JS_SEPTEMBER'), app.vtranslate('JS_OCTOBER'),
		app.vtranslate('JS_NOVEMBER'), app.vtranslate('JS_DECEMBER')],
		month_short:[app.vtranslate('JS_JAN'), app.vtranslate('JS_FEB'), app.vtranslate('JS_MAR'),
		app.vtranslate('JS_APR'), app.vtranslate('JS_MAY'), app.vtranslate('JS_JUN'), app.vtranslate('JS_JUL'),
		app.vtranslate('JS_AUG'), app.vtranslate('JS_SEP'), app.vtranslate('JS_OCT'), app.vtranslate('JS_NOV'),
		app.vtranslate('JS_DEC')],
		day_full:[app.vtranslate('JS_SUNDAY'), app.vtranslate('JS_MONDAY'), app.vtranslate('JS_TUESDAY'),
		app.vtranslate('JS_WEDNESDAY'), app.vtranslate('JS_THURSDAY'), app.vtranslate('JS_FRIDAY'),
		app.vtranslate('JS_SATURDAY')],
		day_short:[app.vtranslate('JS_SUN'), app.vtranslate('JS_MON'), app.vtranslate('JS_TUE'),
		app.vtranslate('JS_WED'), app.vtranslate('JS_THU'), app.vtranslate('JS_FRI'),
		app.vtranslate('JS_SAT')]
	};

	gantt.templates.grid_file = function(item) {
		return "<div class='gantt_tree_icon gantt_file "+"modIcon_"+item.module+"'></div>";
	},
	gantt.templates.grid_folder = function(item) {
		 return "<div class='gantt_tree_icon gantt_folder_" + (item.$open ? "open" : "closed") + " "+"modIcon_"+item.module+"'></div>";
	},
	gantt.templates.rightside_text = function(start, end, task){
		if(task.type == gantt.config.types.milestone){
			return task.text;
		}
		return "";
	};

	gantt.config.columns = [{
			name:"text",
			label:app.vtranslate('JS_NAME'),
			width:"*", 
			tree:true 
		},{
			name:"progress", 
			label:app.vtranslate('JS_PROGRESS'),
			template:function(obj){
				if(typeof obj.progress != 'undefined'){
					return Math.round(obj.progress*100)+"%";
				}
				return '';
			},
			align: "center", 
		},{
			name:"priority",  
			label:app.vtranslate('JS_PRIORITY'), 
			template:function(obj){
				if(typeof obj.priority != 'undefined'){
					return obj.priority_label;
				}
				return '';
			},
			align: "center", 
	}];

	gantt.config.scale_unit = "month";
	gantt.config.date_scale = "%F, %Y";
	gantt.config.scale_height = 50;

	gantt.config.subscales = [
		{
		unit:"day", 
		step:1,
		date:"%j, %D" }
	];
	gantt._get_safe_type = function(type){
		if(type == gantt.config.types.milestone){
			return gantt.config.types.milestone;
		}
		else if(type == gantt.config.types.project){
			return gantt.config.types.project;
		}
		return "task";
	};
	gantt._on_dblclick = function(){};
	gantt.config.drag_links = false;
	gantt.config.drag_progress = false;
	gantt.config.drag_move = false;
	gantt.config.drag_resize = false;
	
	gantt.init('gantt_here');
	gantt.parse({$DATA});
});


</script>
