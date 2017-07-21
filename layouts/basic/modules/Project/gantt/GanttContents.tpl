{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
<style>
{foreach from=Vtiger_Module_Model::getAll() item=MODULE}
	.modIcon_{$MODULE->get('name')}{ background-image: url("{\App\Layout::getLayoutFile('skins/images/'|cat:$MODULE->get('name')|cat:'.png')}") !important; }
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
			<legend class="hide">{\App\Language::translate('LBL_FILTERING',$QUALIFIED_MODULE)}</legend>
			<td><strong> {\App\Language::translate('LBL_FILTERING',$QUALIFIED_MODULE)}: &nbsp; </strong></td>
			<td><input name="filter" id="all" class="filter" type="radio" value="" checked="true"><label for="all"><span>&nbsp;{\App\Language::translate('LBL_ALL_PRIORITY',$QUALIFIED_MODULE)}</span></label></td>
			<td><input name="filter" id="low" class="filter" type="radio" value="PLL_LOW"><label for="low"><span>&nbsp;{\App\Language::translate('LBL_LOW_PRIORITY',$QUALIFIED_MODULE)}</span></label></td>
			<td><input name="filter" id="high" class="filter" type="radio" value="PLL_HIGH"><label for="high"><span>&nbsp;{\App\Language::translate('LBL_HIGH_PRIORITY',$QUALIFIED_MODULE)}</span></label></td>
		</fieldset>
		<fieldset>
			<legend class="hide">{\App\Language::translate('LBL_ZOOMING',$QUALIFIED_MODULE)}</legend>
			<td><strong><span>| &nbsp;</span> {\App\Language::translate('LBL_ZOOMING',$QUALIFIED_MODULE)}: </strong></td>
			<td><input name="scales" id="days" class="zoom" type="radio" value="trplweek" checked="true"><label for="days"><span>&nbsp;{\App\Language::translate('LBL_DAYS_CHART',$QUALIFIED_MODULE)}</span></label></td>
			<td><input name="scales" id="months" class="zoom" type="radio" value="year"><label for="months"><span>&nbsp;{\App\Language::translate('LBL_MONTHS_CHART',$QUALIFIED_MODULE)}</span></label></td>
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
		month_full:[app.\App\Language::translate('JS_JANUARY'), app.\App\Language::translate('JS_FEBRUARY'), app.\App\Language::translate('JS_MARCH'),
		app.\App\Language::translate('JS_APRIL'), app.\App\Language::translate('JS_MAY'), app.\App\Language::translate('JS_JUNE'), app.\App\Language::translate('JS_JULY'),
		app.\App\Language::translate('JS_AUGUST'), app.\App\Language::translate('JS_SEPTEMBER'), app.\App\Language::translate('JS_OCTOBER'),
		app.\App\Language::translate('JS_NOVEMBER'), app.\App\Language::translate('JS_DECEMBER')],
		month_short:[app.\App\Language::translate('JS_JAN'), app.\App\Language::translate('JS_FEB'), app.\App\Language::translate('JS_MAR'),
		app.\App\Language::translate('JS_APR'), app.\App\Language::translate('JS_MAY'), app.\App\Language::translate('JS_JUN'), app.\App\Language::translate('JS_JUL'),
		app.\App\Language::translate('JS_AUG'), app.\App\Language::translate('JS_SEP'), app.\App\Language::translate('JS_OCT'), app.\App\Language::translate('JS_NOV'),
		app.\App\Language::translate('JS_DEC')],
		day_full:[app.\App\Language::translate('JS_SUNDAY'), app.\App\Language::translate('JS_MONDAY'), app.\App\Language::translate('JS_TUESDAY'),
		app.\App\Language::translate('JS_WEDNESDAY'), app.\App\Language::translate('JS_THURSDAY'), app.\App\Language::translate('JS_FRIDAY'),
		app.\App\Language::translate('JS_SATURDAY')],
		day_short:[app.\App\Language::translate('JS_SUN'), app.\App\Language::translate('JS_MON'), app.\App\Language::translate('JS_TUE'),
		app.\App\Language::translate('JS_WED'), app.\App\Language::translate('JS_THU'), app.\App\Language::translate('JS_FRI'),
		app.\App\Language::translate('JS_SAT')]
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
			label:app.\App\Language::translate('JS_NAME'),
			width:"*", 
			tree:true 
		},{
			name:"progress", 
			label:app.\App\Language::translate('JS_PROGRESS'),
			template:function(obj){
				if(typeof obj.progress != 'undefined'){
					return Math.round(obj.progress*100)+"%";
				}
				return '';
			},
			align: "center", 
		},{
			name:"priority",  
			label:app.\App\Language::translate('JS_PRIORITY'), 
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
