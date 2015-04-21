<style>
{foreach from=Vtiger_Module_Model::getAll() item=MODULE}
	.modIcon_{$MODULE->get('name')}{ background-image: url("layouts/vlayout/skins/images/{$MODULE->get('name')}.png") !important;; }
{/foreach}
</style>
<div id="gantt_here" style='width:100%; height:500px;'></div>
<script>
$(document).ready(function(){

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
		},/*{
			name:"progress", 
			label:"Status", 
			template:function(obj){
				return Math.round(obj.progress*100)+"%";
			},
			align: "center", 
		},*/{
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
	gantt._on_dblclick = false;
	gantt.init('gantt_here');
	gantt.parse({
		data:{$DATA},
		links:[]
	});
});


</script>
