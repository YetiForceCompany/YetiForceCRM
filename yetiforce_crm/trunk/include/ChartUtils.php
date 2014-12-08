<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

require_once('include/utils/utils.php');
require_once 'include/utils/CommonUtils.php';

Class ChartUtils {

	// Function to generate Bar Chart
	public static function getBarChart($xaxisData, $yaxisData, $title='', $width='', $height='', $charttype='vertical', $cachedFileName=false, $target=false, $color='') {

		global $log, $lang_crm, $default_charset;

		require_once('include/utils/utils.php');
		require_once('include/utils/GraphUtils.php');
		include_once ('Image/Graph.php');
		include_once ('Image/Canvas.php');

		$barwidth = '70';
		if ($cachedFileName === false) {
			$cache_file_name = 'cache/images/bar_chart_' . time() . '.png';
		} else {
			$cache_file_name = $cachedFileName;
		}
		if (empty($width))
			$width = '400';
		if (empty($height))
			$height = '300';
		if ($target === false)
			$target = array();
		if (empty($color))
			$color = 'black';

		$alts = array();
		$temp = array();
		for ($i = 0; $i < count($xaxisData); $i++) {
			$name = html_entity_decode($xaxisData[$i], ENT_QUOTES, $default_charset);
			$pos = substr_count($name, " ");
			$alts[] = $name;
			//If the daatx value of a string is greater, adding '\n' to it so that it'll cme inh 2nd line
			if (strlen($name) >= 14)
				$name = substr($name, 0, 44);
			if ($pos >= 2) {
				$val = explode(" ", $name);
				$n = count($val) - 1;
				$x = "";
				for ($j = 0; $j < count($val); $j++) {
					if ($j != $n) {
						$x .=" " . $val[$j];
					} else {
						$x .= "@#" . $val[$j];
					}
				}
				$name = $x;
			}
			$name = str_replace("@#", " ", $name);
			$temp[] = html_entity_decode($name, ENT_QUOTES, $default_charset);
		}
		$xaxisData = $temp;

		// Set the basic parameters of the graph
		$canvas = & Image_Canvas::factory('png', array('width' => $width, 'height' => $height, 'usemap' => true));
		$imagemap = $canvas->getImageMap();
		$graph = & Image_Graph::factory('graph', $canvas);

		$font = & $graph->addNew('font', calculate_font_name($lang_crm));
		$font->setSize(8);
		$font_color = "#000000";
		$font->setColor($font_color);
		$graph->setFont($font);

		$titlestr = & Image_Graph::factory('title', array($title, 8));
		$plotarea = & Image_Graph::factory('plotarea', array(
					'axis',
					'axis',
					$charttype
				));
		$graph->add(Image_Graph::vertical($titlestr, $plotarea, 5));

		// Now create a bar plot
		$max = 0;
		// To create unique lables we need to keep track of lable name and its count
		$uniquex = array();

		$xlabels = array();
		$dataset = & Image_Graph::factory('dataset');
		if ($charttype == 'horizontal') {
			$fill = & Image_Graph::factory('gradient', array(IMAGE_GRAPH_GRAD_VERTICAL_MIRRORED, $color, 'white'));
		} else {
			$fill = & Image_Graph::factory('gradient', array(IMAGE_GRAPH_GRAD_HORIZONTAL_MIRRORED, $color, 'white'));
		}

		for ($i = 0; $i < count($yaxisData); $i++) {
			$x = 1 + $i;
			if ($yaxisData[$i] >= $max)
				$max = $yaxisData[$i];
			$dataset->addPoint(
					$x,
					$yaxisData[$i],
					array(
						'url' => $target[$i],
						'alt' => $alts[$i] . '=' . $yaxisData[$i]
					)
			);
			$xlabels[$x] = $xaxisData[$i];

			// To have unique names even in case of duplicates let us add the id
			$xaxisData_appearance = $uniquex[$xaxisData[$i]];
			if ($xaxisData_appearance == null) {
				$uniquex[$xaxisData[$i]] = 1;
			} else {
				$xlabels[$x] = $xaxisData[$i] . ' [' . $xaxisData_appearance . ']';
				$uniquex[$xaxisData[$i]] = $xaxisData_appearance + 1;
			}
		}
		$bplot = & $plotarea->addNew('bar', $dataset);
		$bplot->setFillStyle($fill);

		//You can change the width of the bars if you like
		if (!empty($xaxisData))
			$bplot->setBarWidth($barwidth / count($xaxisData), "%");
		//$bplot->setPadding(array('top'=>10));
		$bplot->setBackground(Image_Graph::factory('gradient', array(IMAGE_GRAPH_GRAD_HORIZONTAL, 'white', 'white')));
		$xaxis = & $plotarea->getAxis(IMAGE_GRAPH_AXIS_X);
		$yaxis = & $plotarea->getAxis(IMAGE_GRAPH_AXIS_Y);
		$yaxis->setFontSize(8);
		$xaxis->setFontSize(8);

		if ($charttype == 'horizontal') { // Invert X-axis and put Y-axis at bottom
			$xaxis->setInverted(false);
			$yaxis->setAxisIntersection('max');
		}

		// set grid
		$gridY = & $plotarea->addNew('line_grid', IMAGE_GRAPH_AXIS_Y);
		$gridY->setLineColor('#FFFFFF@0.5');
		$gridY2 = & $plotarea->addNew('bar_grid', null, IMAGE_GRAPH_AXIS_Y);
		$gridY2->setFillColor('#FFFFFF@0.2');


		// Add some grace to y-axis so the bars doesn't go all the way to the end of the plot area
		$yaxis->forceMaximum(round(($max * 1.1) + 0.5));
		$ticks = get_tickspacing(round(($max * 1.1) + 0.5));

		// First make the labels look right
		if ($charttype == 'horizontal')
			$yaxis->setFontAngle('vertical');
		$yaxis->setLabelInterval($ticks[0]);
		$yaxis->setTickOptions(-5, 0);
		$yaxis->setLabelInterval($ticks[1], 2);
		$yaxis->setTickOptions(-2, 0, 2);

		// Create the xaxis labels
		$array_data = & Image_Graph::factory('Image_Graph_DataPreprocessor_Array',
						array($xlabels)
		);

		// The fix the tick marks
		$xaxis->setDataPreprocessor($array_data);
		$xaxis->forceMinimum(0.5);
		$xaxis->forceMaximum(0.5 + count($yaxisData));
		if ($charttype == 'vertical')
			$xaxis->setFontAngle('vertical');
		$xaxis->setLabelInterval(1);
		$xaxis->setTickOptions(0, 0);
		$xaxis->setLabelInterval(2, 2);

		// set markers
		if ($width > 400 && $height > 400) {
			$marker = & $graph->addNew('value_marker', IMAGE_GRAPH_VALUE_Y);
			$marker->setFillColor('000000@0.0');
			$marker->setBorderColor('000000@0.0');
			$marker->setFontSize(8);
			// shift markers 20 pix right
			if ($charttype == 'horizontal') {
				$marker_pointing = & $graph->addNew('Image_Graph_Marker_Pointing', array(10, 0, & $marker));
			} else {
				$marker_pointing = & $graph->addNew('Image_Graph_Marker_Pointing', array(0, -10, & $marker));
			}
			$marker_pointing->setLineColor('000000@0.0');
			$bplot->setMarker($marker_pointing);
		}

		//Getting the graph in the form of html page
		$img = $graph->done(
						array(
							'tohtml' => true,
							'border' => 0,
							'filename' => $cache_file_name,
							'filepath' => '',
							'urlpath' => ''
				));

		return $img;
	}

	// Function to generate Pie Chart
	public static function getPieChart($xaxisData, $yaxisData, $title='', $width='', $height='', $charttype='vertical', $cachedFileName=false, $target=false, $color='') {

		global $log, $lang_crm, $default_charset;

		require_once('include/utils/utils.php');
		require_once('include/utils/GraphUtils.php');
		include_once ('Image/Graph.php');
		include_once ('Image/Canvas.php');

		if ($cachedFileName === false) {
			$cache_file_name = 'cache/images/pie_chart_' . time() . '.png';
		} else {
			$cache_file_name = $cachedFileName;
		}

		if (empty($width))
			$width = '500';
		if (empty($height))
			$height = '400';
		if ($target === false)
			$target = array();

		$alts = array();
		$temp = array();
		for ($i = 0; $i < count($xaxisData); $i++) {
			$name = html_entity_decode($xaxisData[$i], ENT_QUOTES, $default_charset);
			$pos = substr_count($name, " ");
			$alts[] = $name;
			//If the datax value of a string is greater, adding '\n' to it so that it'll come in 2nd line
			if (strlen($name) >= 14)
				$name = substr($name, 0, 34);
			if ($pos >= 2) {
				$val = explode(" ", $name);
				$n = count($val) - 1;
				$x = "";
				for ($j = 0; $j < count($val); $j++) {
					if ($j != $n) {
						$x .=" " . $val[$j];
					} else {
						$x .= "@#" . $val[$j];
					}
				}
				$name = $x;
			}
			$name = str_replace("@#", "\n", $name);
			$temp[] = $name;
		}
		$xaxisData = $temp;
		$width = $width + ($width / 5);

		$canvas = & Image_Canvas::factory('png', array('width' => $width, 'height' => $height, 'usemap' => true));
		$imagemap = $canvas->getImageMap();
		$graph = & Image_Graph::factory('graph', $canvas);
		$font = & $graph->addNew('font', calculate_font_name($lang_crm));
		$font->setSize(8);
		$font->setColor($color);
		$graph->setFont($font);
		// create the plotarea layout
		$title = & Image_Graph::factory('title', array($title, 10));
		$plotarea = & Image_Graph::factory('plotarea', array(
					'category',
					'axis'
				));
		$graph->add(Image_Graph::vertical($title, $plotarea, 5));
		// To create unique lables we need to keep track of lable name and its count
		$uniquex = array();
		// Generate colours
		$colors = color_generator(count($yaxisData), '#33DDFF', '#3322FF');
		$dataset = & Image_Graph::factory('dataset');
		$fills = & Image_Graph::factory('Image_Graph_Fill_Array');
		$sum = 0;
		$pcvalues = array();
		for ($i = 0; $i < count($yaxisData); $i++) {
			$sum += $yaxisData[$i];
		}
		for ($i = 0; $i < count($yaxisData); $i++) {
			// To have unique names even in case of duplicates let us add the id
			$datalabel = $xaxisData[$i];
			$xaxisData_appearance = $uniquex[$xaxisData[$i]];
			if ($xaxisData_appearance == null) {
				$uniquex[$xaxisData[$i]] = 1;
			} else {
				$datalabel = $xaxisData[$i] . ' [' . $xaxisData_appearance . ']';
				$uniquex[$xaxisData[$i]] = $xaxisData_appearance + 1;
			}
			$dataset->addPoint(
					$datalabel,
					$yaxisData[$i],
					array(
						'url' => $target[$i],
						'alt' => $alts[$i] . '=' . sprintf('%0.1f%%', 100 * $yaxisData[$i] / $sum)
					)
			);
			$pcvalues[$yaxisData[$i]] = sprintf('%0.1f%%', 100 * $yaxisData[$i] / $sum);
			$fills->addColor($colors[$i]);
		}
		if ($sum == 0)
			return null;
		// create the pie chart and associate the filling colours
		$gbplot = & $plotarea->addNew('pie', $dataset);
		$plotarea->setPadding(array('top' => 0, 'bottom' => 0, 'left' => 0, 'right' => ($width / 20)));
		$plotarea->hideAxis();
		$gbplot->setFillStyle($fills);
		// format the data values
		$marker_array = & Image_Graph::factory('Image_Graph_DataPreprocessor_Array', array($pcvalues));
		// set markers
		$marker = & $graph->addNew('value_marker', IMAGE_GRAPH_VALUE_Y);
		$marker->setDataPreprocessor($marker_array);
		$marker->setFillColor('#FFFFFF');
		$marker->setBorderColor($color);
		$marker->setFontColor($color);
		$marker->setFontSize(8);
		$pointingMarker = & $graph->addNew('Image_Graph_Marker_Pointing_Angular', array(20, &$marker));
		$gbplot->setMarker($pointingMarker);
		$legend_box = & $plotarea->addNew('legend');
		$legend_box->setPadding(array('top' => 20, 'bottom' => 0, 'left' => 0, 'right' => 0));
		$legend_box->setFillColor('#F5F5F5');
		$legend_box->showShadow();

		$img = $graph->done(array(
					'tohtml' => true,
					'border' => 0,
					'filename' => $cache_file_name,
					'filepath' => '',
					'urlpath' => ''
				));
		return $img;
	}

	//Generates Chart Data in form of an array from the Query Result of reports
	public static function generateChartDataFromReports($queryResult, $groupbyField, $fieldDetails='', $reportid='') {
		require_once 'modules/Reports/CustomReportUtils.php';
		require_once('include/Webservices/Utils.php');
		require_once('include/Webservices/Query.php');
		global $adb, $current_user, $theme, $default_charset;
		$inventorymodules = array('Quotes', 'SalesOrder', 'PurchaseOrder', 'Invoice', 'Products', 'PriceBooks', 'Vendors', 'Services');
		$rows = $adb->num_rows($queryResult);
		$condition = "is";
		$current_theme = $theme;
		$groupByFields = array();
		$yaxisArray = array();
		$ChartDataArray = array();
		$target_val = array();

		$report = new ReportRun($reportid);
		$restrictedModules = array();
		if($report->secondarymodule!='') {
			$reportModules = explode(":",$report->secondarymodule);
		} else {
			$reportModules = array();
		}
		array_push($reportModules,$report->primarymodule);

		$restrictedModules = false;
		foreach($reportModules as $mod) {
			if(isPermitted($mod,'index') != "yes" || vtlib_isModuleActive($mod) == false) {
				if(!is_array($restrictedModules)) $restrictedModules = array();
				$restrictedModules[] = $mod;
			}
		}

		if(is_array($restrictedModules) && count($restrictedModules) > 0) {
			$ChartDataArray['error'] = "<h4>".getTranslatedString('LBL_NO_ACCESS', 'Reports').' - '.implode(',', $restrictedModules)."</h4>";
			return $ChartDataArray;
		}

		if ($fieldDetails != '') {
			list($tablename, $colname, $module_field, $fieldname, $single) = explode(":", $fieldDetails);
			list($module, $field) = split("_", $module_field);
			$dateField = false;
			if ($single == 'D') {
				$dateField = true;
				$query = "SELECT * FROM vtiger_reportgroupbycolumn WHERE reportid=? ORDER BY sortid";
				$result = $adb->pquery($query, array($reportid));
				$criteria = $adb->query_result($result, 0, 'dategroupbycriteria');
			}
		}
		preg_match('/&amp;/', $groupbyField, $matches);
		if (!empty($matches)) {
			$groupfield = str_replace('&amp;', '&', $groupbyField);
			$groupbyField = $report->replaceSpecialChar($groupfield);
		}
		$handler = vtws_getModuleHandlerFromName($module, $current_user);
		$meta = $handler->getMeta();
		$meta->retrieveMeta();
		$referenceFields = $meta->getReferenceFieldDetails();

		if($rows > 0) {
			$resultRow = $adb->query_result_rowdata($queryResult, 0);
			if(!array_key_exists($groupbyField, $resultRow)) {
				$ChartDataArray['error'] = "<h4>".getTranslatedString('LBL_NO_PERMISSION_FIELD', 'Dashboard')."</h4>";
				return $ChartDataArray;
			}
		}
		for ($i = 0; $i < $rows; $i++) {
			$groupFieldValue = $adb->query_result($queryResult, $i, strtolower($groupbyField));
			$decodedGroupFieldValue = html_entity_decode($groupFieldValue, ENT_QUOTES, $default_charset);
			if (!empty($groupFieldValue)) {
				if (in_array($module_field, $report->append_currency_symbol_to_value)) {
					$valueComp = explode('::', $groupFieldValue);
					$groupFieldValue = $valueComp[1];
				}
				if ($dateField) {
					if (!empty($groupFieldValue))
						$groupByFields[] = CustomReportUtils::getXAxisDateFieldValue($groupFieldValue, $criteria);
					else
						$groupByFields[] = "Null";
				}
				else if (in_array($fieldname, array_keys($referenceFields))) {
					if (count($referenceFields[$fieldname]) > 1) {
						$refenceModule = CustomReportUtils::getEntityTypeFromName($decodedGroupFieldValue, $referenceFields[$fieldname]);
					}
					else {
						$refenceModule = $referenceFields[$fieldname][0];
					}
					$groupByFields[] = $groupFieldValue;

					if ($fieldname == 'currency_id' && in_array($module, $inventorymodules)) {
						$tablename = 'vtiger_currency_info';
					} elseif ($refenceModule == 'DocumentFolders' && $fieldname == 'folderid') {
						$tablename = 'vtiger_attachmentsfolder';
						$colname = 'foldername';
					} else {
						require_once "modules/$refenceModule/$refenceModule.php";
						$focus = new $refenceModule();
						$tablename = $focus->table_name;
						$colname = $focus->list_link_field;
						$condition = "c";
					}
				} else {
					$groupByFields[] = $groupFieldValue;
				}
				$yaxisArray[] = $adb->query_result($queryResult, $i, 'groupby_count');
				if ($fieldDetails != '') {
					if ($dateField) {
						$advanceSearchCondition = CustomReportUtils::getAdvanceSearchCondition($fieldDetails, $criteria, $groupFieldValue);
						if ($module == 'Calendar') {
							$link_val = "index.php?module=" . $module . "&query=true&action=ListView&" . $advanceSearchCondition;
						}else
							$link_val = "index.php?module=" . $module . "&query=true&action=index&" . $advanceSearchCondition;
					}
					else {
						$cvid = getCvIdOfAll($module);
						$esc_search_str = urlencode($decodedGroupFieldValue);
						if ($single == 'DT') {
							$esc_search_str = urlencode($groupFieldValue);
							if (strtolower($fieldname) == 'modifiedtime' || strtolower($fieldname) == 'createdtime') {
								$tablename = 'vtiger_crmentity';
								$colname = $fieldname;
							}
						}
						if ($fieldname == 'assigned_user_id') {
							$tablename = 'vtiger_crmentity';
							$colname = 'smownerid';
						}
                        if ($fieldname == 'serviceid' && in_array($module, getInventoryModules())) {
                            $fieldname = 'productid';
                        }
						if ($module == 'Calendar') {
							$link_val = "index.php?module=" . $module . "&action=ListView&search_text=" . $esc_search_str . "&search_field=" . $fieldname . "&searchtype=BasicSearch&query=true&operator=e&viewname=" . $cvid;
						} else {
							$link_val = "index.php?module=" . $module . "&action=index&search_text=" . $esc_search_str . "&search_field=" . $fieldname . "&searchtype=BasicSearch&query=true&operator=e&viewname=" . $cvid;
						}
					}

					$target_val[] = $link_val;
				}
			}
		}
		if(count($groupByFields) == 0) {
			$ChartDataArray['error'] = "<div class='componentName'>".getTranslatedString('LBL_NO_DATA', 'Reports')."</div";
		}
		$ChartDataArray['xaxisData'] = $groupByFields;
		$ChartDataArray['yaxisData'] = $yaxisArray;
		$ChartDataArray['targetLink'] = $target_val;
		$theme = $current_theme;
		return $ChartDataArray;
	}

	public static function getReportBarChart($queryResult, $groupbyField, $fieldDetails, $reportid, $charttype='horizontal') {
		global $theme;
		$BarChartDetails = self::generateChartDataFromReports($queryResult, $groupbyField, $fieldDetails, $reportid);
		$groupbyFields = $BarChartDetails['xaxisData'];
		$yaxisArray = $BarChartDetails['yaxisData'];
		$targerLinks = $BarChartDetails['targetLink'];
		if ($theme == "softed") {
			$font_color = "#212473";
		} else {
			$font_color = "#000000";
		}
		if(!empty($BarChartDetails['error'])) {
			return $BarChartDetails['error'];
		} else {
			$barChart = ChartUtils::getBarChart($groupbyFields, $yaxisArray, '', '350', '300', $charttype, false, $targerLinks, $font_color);
			return $barChart;
		}
	}

	public static function getReportPieChart($queryResult, $groupbyField, $fieldDetails, $reportid) {
		global $theme;
		$PieChartDetails = self::generateChartDataFromReports($queryResult, $groupbyField, $fieldDetails, $reportid);
		$groupbyFields = $PieChartDetails['xaxisData'];
		$yaxisArray = $PieChartDetails['yaxisData'];
		$targerLinks = $PieChartDetails['targetLink'];
		$charttype = 'vertical';
		if ($theme == "softed") {
			$font_color = "#212473";
		} else {
			$font_color = "#000000";
		}
		if(!empty($PieChartDetails['error'])) {
			return $PieChartDetails['error'];
		} else {
			$pieChart = ChartUtils::getPieChart($groupbyFields, $yaxisArray, '', '350', '300', $charttype, false, $targerLinks, $font_color);
			return $pieChart;
		}
	}

}

?>
