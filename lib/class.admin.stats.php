<?php

// ==============================================================
// Stats functions
// --------------------------------------------------------------

class stats {
	
	public static function optionsDefault() {
		
		global $cfg, $var, $obj;
		
		$options = array(
			'seriesColors' => $cfg['stats']['palette'], 
			'stackSeries' => false, 
			'title' => array('text' => "", 'show' => false), 
			
	  	'animate' => false, // Turns on animatino for all series in this plot.
	    'animateReplot' => false, // Will animate plot on calls to plot1.replot({resetAxes:true})
	    
			'axes' => array(
											'xaxis' => array(
																				
																			), 
											'yaxis' => array(
																				'padMin' => 0, 
																				'pad' => 1.2, 
																				'rendererOptions' => array(
																																		'forceTickAt0' => true, 
																																		'forceTickAt100' => false
																																	)
																			)
											), 
			'seriesDefaults' => array(
															'show' => true, 
															'lineWidth' => 2.5, 
															'shadow' => true, 
															'shadowAngle' => 45, 
															'shadowOffset' => 1.25, 
															'shadowDepth' => 3, 
															'shadowAlpha' => 0.1, 
															'showLine' => true, 
															'showMarker' => true, 
															'fill' => false, 
															'fillAndStroke' => false, 
															'pointLabels' => array(
																											'show' => false, 
																											'location' => "n", 
																											'edgeTolerance' => -15, 
																											'hideZeros' => true
																										), 
															'markerOptions' => array(
																												'show' => true, 
																												'style' => "filledCircle", // circle, diamond, square, filledCircle, filledDiamond or filledSquare
																												'lineWidth' => 2, 
																												'size' => 9, 
																												//'color' => "#666666", 
																												'shadow' => true, 
																												'shadowAngle' => 45, 
																												'shadowOffset' => 1, 
																												'shadowDepth' => 3, 
																												'shadowAlpha' => 0.07
																											)
															), 
			'legend' => array(
															'show' => false, 
															'location' => "n", // compass direction, nw, n, ne, e, se, s, sw, w.
															'xoffset' => 0, 
															'yoffset' => 0
												), 
			'grid' => array(
															'drawGridLines' => true,        // wether to draw lines across the grid or not.
											        'gridLineColor' => "#eeeeee",     // *Color of the grid lines.
											        'background' => "#ffffff",      // CSS color spec for background color of grid.
											        'borderColor' => "#bbbbbb",     // CSS color spec for border around grid.
											        'borderWidth' => 2.0,           // pixel width of border around grid.
											        'shadow' => false,               // draw a shadow for grid.
											        'shadowAngle' => 45,            // angle of the shadow.  Clockwise from x axis.
											        'shadowOffset' => 1.5,          // offset from the line of the shadow.
											        'shadowWidth' => 3,             // width of the stroke for the shadow.
											        'shadowDepth' => 3,             // Number of strokes to make when drawing shadow.
											        'shadowAlpha' => 0.07           // Opacity of the shadow
											), 
			'highlighter' => array(	
															'show' => true, 
															'showMarker' => true, 
															'lineWidthAdjust' => 1.5, 
															'sizeAdjust' => 5, 
															'showTooltip' => true, 
															'tooltipLocation' => "n", 
															'fadeTooltip' => true, 
															'tooltipOffset' => 2, 
															'tooltipAxes' => "xy", 
															'useAxesFormatters' => true, 
															'formatString' => "<div style=\"text-align: center;\">%s<br /><span style=\"font-size: 14px; font-weight: bold;\">%s</span></div>", 
															'bringSeriesToFront' => true
														)
		);
		
		return $options;
		
	}
	
	public static function load($chart, $options) {
		
		global $cfg, $var, $obj;
		
		$data = array();
		
		$dates = array(
			'min' => date::dateDB("-" . ($cfg['stats']['summary-days']+1) . " days") . " 00:00:00", 
			'max' => date::dateDB() . " 23:59:59"
		);
		
		$max = 0;
		
		// Date array
		$dates_array = stats::auxDateArray($dates);
		
		
		switch ($chart) {
			
			// =======================================================================================================================
			case "home-graph-users":
				
				$dates = array(
					'min' => date::dateDB("-" . ($cfg['stats']['summary-days']+1) . " days") . " 00:00:00", 
					'max' => date::dateDB("-1 days") . " 23:59:59"
				);
				
				$max = 0;
				
				// Date array
				$dates_array = stats::auxDateArray($dates);
				
				// Items
				$items = $dates_array;
				// User query permissions
				$user_permissions = core::userPermissionQuery("users");
				$q = "SELECT DATE(" . core::fieldGetName("users", "date_creation") . ") AS date, COUNT(*) AS num 
										FROM " . core::tableGetName("users") . " 
										WHERE 
											" . core::fieldGetName("users", "date_creation") . ">='" . $dates['min'] . "' AND 
											" . core::fieldGetName("users", "date_creation") . "<='" . $dates['max'] . "' 
											" . ($user_permissions != "" ? " AND (" . $user_permissions . ") " : "") . "
										GROUP BY DATE(" . core::fieldGetName("users", "date_creation") . ") 
										ORDER BY DATE(" . core::fieldGetName("users", "date_creation") . ")";
				$r = $obj['db']->query($q);
				while ($i = $obj['db']->assoc($r)) {
					$items[$i['date']] = $i['num'];
					$max = max($max, $i['num']);
				}
				
				// Labels
				$labels = array_keys($dates_array);
				
				// Options
				$graph_options = stats::optionsDefault();
				$graph_options['highlighter']['show'] = false;
				$graph_options['highlighter']['showMarker'] = false;
				$graph_options['highlighter']['showTooltip'] = false;
				$graph_options['seriesDefaults']['lineWidth'] = 1.5;
				$graph_options['seriesDefaults']['markerOptions']['show'] = false;
				$graph_options['axes']['yaxis']['max'] = ceil($max * $cfg['stats']['max-margin']);
				
				// Final data
				$data = array(
					'type' => "graph", 
					'graph_type' => "lines", 
					'width' => "100%", 
					'height' => "200px", 
					'labels' => $labels, 
					'date_min' => date::dateDB($dates['min']." -1 days"), 
					'date_max' => date::dateDB($dates['max']." +1 days"), 
					'nolegend' => true, 
					'series' => array(
														array('title' => "Registros", 'color' => $cfg['stats']['palette'][0], 'data' => stats::auxData2Array($items, $labels))
														), 
					'options' => $graph_options
				);
				
			break;
			
		}
		
		if (!empty($data)) {
			
			core::jsonOK(array(
				'data' => $data
			));
			
		} else {
			
			core::jsonError("No hay datos que mostrar.");
			
		}
		
	}
	
	
	// -------------------------------------
	// AUX
	
	public static function auxDateArray($dates) {
		
		global $cfg, $var, $obj;
		
		$dates_array = array();
		
		$d = date::dateDB($dates['min']);
		$d_max = date::dateDB($dates['max']);
		
		while ($d <= $d_max) {
			$dates_array[$d] = 0;
			$d = date::dateDB($d . " +1 days");
		}
		
		return $dates_array;
		
	}
	
	public static function auxOptions($options) {
		
		global $cfg, $var, $obj;
		
		if (!isset($options) 
				|| !isset($options['item']) 
				|| !isset($options['type']) 
				|| !isset($options['year']) 
				|| !isset($options['month']) 
				|| !isset($options['start']) 
				|| !isset($options['end'])) {
			return array();
		}
		
		$o = array();
		
		$o['item_id'] = inputClean::clean($options['item'], 11);
		$var['item'] = core::getItem("sites", $o['item_id']);
		
		$o['date_type'] = inputClean::clean($options['type'], 32);
		
		$o['dates'] = array();
		
		if ($o['date_type'] == "date") {
			// Year or month
			$o['year'] = inputClean::clean($options['year'], 4);
			$o['month'] = str_pad(inputClean::clean($options['month'], 2), 2, "0", STR_PAD_LEFT);
			if ($o['month'] == 0) {
				// Complete year
				$o['dates']['min'] = $o['year'] . "-01-01 00:00:00";
				$o['dates']['max'] = $o['year'] . "-12-31 23:59:59";
				$o['text'] = "Año <strong>" . number::format($o['year']) . "</strong>";
			} else {
				// Month
				$o['dates']['min'] = $o['year'] . "-" . $o['month'] . "-01 00:00:00";
				$o['dates']['max'] = $o['year'] . "-" . $o['month'] . "-" . date("t", strtotime($o['year'] . "-" . $o['month'] . "-01")) . " 23:59:59";
				$o['text'] = "<strong>" . ucfirst($cfg['texts'][$cfg['lang']['default']]['months'][$o['month']-1]) . "</strong> de <strong>" . number::format($o['year']) . "</strong>"; 
			}
		} else {
			// Date range
			$o['dates']['min'] = date::invert(inputClean::clean($options['start'], 10)) . " 00:00:00";
			$o['dates']['max'] = date::invert(inputClean::clean($options['end'], 10)) . " 23:59:59";
			$o['text'] = "Desde <strong>" . inputClean::clean($options['start'], 10) . "</strong> hasta <strong>" . inputClean::clean($options['end'], 10) . "</strong>";
		}
		
		return $o;
		
	}
	
	public static function auxData2Array($data, $labels=array()) {
		
		global $cfg, $var, $obj;
		
		$t = array();
		
		if (!empty($labels)) {
			
			foreach ($labels as $n => $label) {
				array_push($t, array($label, $data[$label]));
			}
			
		} else {
			
			foreach ($data as $label => $value) {
				array_push($t, $value);
			}
			
		}
		
		return $t;
		
	}
	
}

?>