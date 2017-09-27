<?php
$options = get_post_meta( $chart_id, 'dw_chart_options', true );
$options['title'] = get_post_field( 'post_title', $chart_id );
if ( $options['chartType'] == 'trendlines' ) {
	$options['trendlines'] = array(
		0 => array(
			'type' => $options['trendlines_type'],
			'visibleInLegend' => true
		)
	);
}

if ( $options['chartType'] == 'waterfall' ) {
	$options['candlestick'] = array(
		'fallingColor' => array(
			'strokeWidth' => 0,
			'fill' => $options['waterfall_falling_color']
		),
		'risingColor' => array(
			'strokeWidth' => 0,
			'fill' => $options['waterfall_rising_color']
		)
	);
}
$options = apply_filters( 'dw_chart_pre_options', $options, $chart_id );
$callback = 'dw_chart_draw_' . $chart_id;
$data = array();
$rows = preg_split( '/[\r\n]+/', $options['input_data'] );
$container = 'dw-chart-' . $chart_id;

for ( $i = 0; $i < count( $rows ); $i++ ) {
	$row = explode( ',', str_replace( ';', ',', $rows[ $i ] ) );
	$tmp = array();
	if ( $i == 0 ) {
		//title
		foreach ( $row as $cell ) {
			$tmp[] = (string) trim( $cell );
		}
	} else {
		for( $j=0; $j < count( $row ); $j++ ) {
			if($j == 0) {
				$tmp[] = (string) ( trim( $row[ $j ] ) );//horizontal axis - item title
			} else {
				$tmp[] = (float) ( trim( $row[ $j ] ) );
			}
		}
	}
	
	if ( count( $tmp ) > 1 ) // check if the data row is acceptable.
		$data[] = $tmp;
}

switch ( $options['chartType'] ) {
	case 'geo':
		$fchart = 'GeoChart';
		break;
	case 'pie':
		$fchart = 'PieChart';
		break;
	case 'bar':
		$fchart = 'BarChart';
		break;
	case 'column':
		$fchart = 'ColumnChart';
		break;
	case 'line':
		$fchart = 'LineChart';
		break;
	case 'combo':
		$fchart = 'ComboChart';
		break;
	case 'steppedarea':
		$fchart = 'SteppedAreaChart';
		break;
	case 'waterfall':
		$fchart = 'CandlestickChart';
		break;
	case 'scatter':
		$fchart = 'ScatterChart';
		break;
	case 'trendlines':
		$fchart = 'ScatterChart';
		break;
	case 'area':
	default:
		$fchart = 'AreaChart';
		break;
}

$js = "
<div id=\"{$container}\"></div>
<script type=\"text/javascript\">
google.charts.setOnLoadCallback({$callback});
function {$callback}() {
	var textData = ".json_encode($data).";
	var dataArr = ".json_encode($data).";
	var data = new google.visualization.DataTable();
	var arrData = [];
	
	var cols;
	for (var i=0; i<textData.length; i++) {
		var row = textData[i];
		if (i == 0 ) {
			cols = row;
		} else {
			for (var j=0; j<row.length; j++) {
				if (i == 1) {
					data.addColumn (isNaN(row[j]) ? 'string' : 'number', cols[j]);
				}
				if (!isNaN(row[j])) row[j] = parseFloat(row[j]);				
			}
			arrData.push(row);
		}
	}
	data.addRows (arrData);
	var options = ".json_encode($options).";
	var chart = new google.visualization.{$fchart}(document.getElementById('{$container}'));
	chart.draw(data, options);
}
document.onready = function(){
	window.addEventListener('resize',function(e){
		{$callback}();
	});
}
</script>";

echo $js;
?>
