(function($){

	function chartChange(chart_type) {
		var classes = ['area','geo','line','column','bar', 'pie','combo','steppedarea','waterfall','scatter','trendlines'];
		for(var i=0;i<classes.length;i++){
			$('.chart_data_settings').removeClass('dw-chart-'+classes[i]);
		}

		$('.chart_data_settings').addClass('dw-chart-'+chart_type);
	}

	var googleChartData = {};
	function get_val(input) {
		var name = input.attr('name');
		if ( name == 'post_title' ) {
			googleChartData['title'] = $('#title').val();
			return;
		}
		if (name) {
			val_name = name.match(/\[([^\]]*)\]$/);

			googleChartData[val_name[1]] = input.val();
			googleChartData['title'] = $('#title').val();

			if (val_name[1] == 'chartType') {
				if ( input.val() == 'combo' ) {
					googleChartData['seriesType'] = 'bars';
				} else {
					if (googleChartData['seriesType']) delete googleChartData['seriesType'];
					$.each(googleChartData, function(key,value){
						if (key.match(/series_[0-9]*_type/)) {
							delete googleChartData[key];
						}
					});
				}

				googleChartData['fchart'] = function_chart(input.val());
			}

			if (val_name[1] == 'legend_position') {
				googleChartData['legend'] = 'none';
			}

			if (val_name[1] == 'tooltip_trigger' && input.val() == 'yes') {
				googleChartData['tooltip'] = {isHtml: true};
			}

			if (val_name[1] == 'is3D' && input.val() == 'yes') {
				if (googleChartData['is3D']) delete googleChartData['is3D'];
				googleChartData['is3D'] = true;
			}
		}
	}

	function function_chart(type) {
		var $fchart = 'AreaChart';
		switch (type) {
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

		return $fchart;
	}

	function drawchart() {
		if(!googleChartData.fchart) return;
		drawGoogleChart(googleChartData,'dwgc_chart_preview');
	}

	function drawGoogleChart(input, ele) {
		if (!input.input_data) return;
		var textData = input.input_data.split('\n');
		var arrData = [];
		data = new google.visualization.DataTable();
		
		if (input.chartType == 'trendlines' && input.trendlines_type) {
			input.trendlines = {
				0: {
					type: input.trendlines_type,
					visibleInLegend: true
				}
			}
		} else {
			delete input.trendlines;
		}

		if (input.chartType == 'waterfall' ) {
			input.candlestick = {
				fallingColor: {
					strokeWidth: 0,
					fill: input.waterfall_falling_color
				},
				risingColor: {
					strokeWidth: 0,
					fill: input.waterfall_rising_color
				}
			}
		}

		if (textData.length < 2) return;
		var cols;
		for (var i=0; i<textData.length; i++) {
			var row = textData[i].split(/[,;]\s*/);
			if (i == 0) {
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
		// Create our data table.
		chartOptions = input;
		chart = new google.visualization[input.fchart](document.getElementById(ele));
		chart.draw(data, chartOptions);
	}

	$(document).ready(function(){
		var input1 = $('#dw_chart_settings').find('select,textarea,input');
		var input2 = $('#dw_chart_data').find('select,textarea,input');
		var input3 = $('#dw_advanced_settings').find('select,textarea,input');
		input1.each(function(e){
			get_val($(this));
		})
		input2.each(function(e){
			get_val($(this));
		})
		input3.each(function(e){
			get_val($(this));
		})
		
		google.charts.load('current', {'packages':['corechart']});
		google.charts.setOnLoadCallback(drawchart);

		input1.on('change',function(e){
			get_val($(this));
			drawchart();
		})
		input2.on('change',function(e){
			get_val($(this));
			drawchart();
		})
		input3.on('change',function(e){
			get_val($(this));
			drawchart();
		})
		$('#title').on('change',function(e){
			get_val($(this));
			drawchart();
		})

		$(window).on('resize',function(e){
			drawchart();
		})
		// register image picker class
		$('.image-picker').imagepicker({
			show_label: true
		});
		// register colorpicker class
		$('.colorpicker').spectrum({
			showInput: true,
			preferredFormat: "hex"
		});

		$('.chart_type').on('change',function(e){
			var chart_type = $(this).val();
			chartChange(chart_type);
		});
		chartChange($('.chart_type').val());

		$('#input_file').on('change',function(e){
			var fs = new FileReader();
			fs.readAsText(e.target.files[0]);
			fs.onloadend = function(e) {
				$('#input_data').val(e.target.result);
			}
		});

		$('#dw_download_sample_file').on('click', function(e) {
			e.preventDefault();
			if (!googleChartData.chartType) return;
			var link = document.createElement('a');
			link.href = dw_chart.example_uri + googleChartData.chartType + '.csv';
			link.download = googleChartData.chartType + '.csv';
			link.click();
		})
	});
})(jQuery);