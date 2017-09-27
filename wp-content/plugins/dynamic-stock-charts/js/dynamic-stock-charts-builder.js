/**
 * Dynamic Stock Charts (jQuery plugin)
 * Version 1.2.0, built on Mon Apr 25 2016
 * Copyright WebTheGap <mail@webthegap.com>
 * http://webthegap.com/widgets/dynamic-stock-charts/
 */
$(document).ready(function() {

  // semantic checkboxes, dropdowns, modals
  $('.ui.checkbox').checkbox();  
  $('select.dropdown').dropdown();
  $('#i-show-html').on('click', function() {
    $('.ui.modal').modal('show');
  });
  
  $('#i-falling-color').spectrum({color: "#f00"});
  $('#i-falling-border-color').spectrum({color: "#A10000"});  
  $('#i-rising-color').spectrum({color: "#13D420"});
  $('#i-rising-border-color').spectrum({color: "#125C06"});
  $('#i-container-background').spectrum({color: "#fff"});
  $('#i-chart-background').spectrum({color: "#fff"});
  $('#i-title-color').spectrum({color: "#940101"});
  $('#i-x-title-color').spectrum({color: "#082278"});
  $('#i-y-title-color').spectrum({color: "#082278"});
  $('#i-color').spectrum({color: "#7DBEE7"});
  $('#i-grid-color').spectrum({color: "#D1D1D1"});
  $('#i-indicator-color').spectrum({color: "#C5C"});
  
  // JQuery date picker
  $('#i-start-date, #i-end-date').datepicker({
    //defaultDate: "-3m",
    changeMonth: false,
    changeYear: false,
    numberOfMonths: 3,
    dateFormat: 'yy-mm-dd',
    onClose: function( selectedDate ) {
      if (this.id == 'i-start-date') {
        $('#i-end-date').datepicker('option', 'minDate', selectedDate);
      } else {
        $('#i-start-date').datepicker('option', 'maxDate', selectedDate );
      }
    },
    beforeShow: function (input, inst) {
      var offset = $(input).offset();
      var height = $(input).height();
      window.setTimeout(function () {
          inst.dpDiv.css({ top: (offset.top + height + 30) + 'px', left: offset.left + 'px' })
      }, 1);
    }
  });
  
  // react on changing type of chart to display/hide certain options
  $('#i-type').on('change', function() {
    var type = $(this).val();
    if (type=='CandlestickChart') {
      $('.chart-type-options').hide();
      $('#candlestick-options').fadeIn();
    } else if (type=='LineChart' || type=='AreaChart') {
      $('.chart-type-options').hide();
      $('#line-options').fadeIn();
    }
  });  
  
  // react on enabling navigation option
  $('#navigation-chart-checkbox').on('click', function() {
    var checked = $(this).hasClass('checked');
    if (checked) {
      $('#navigation-options').fadeIn();
    } else {
      $('#navigation-options').fadeOut();
    }
  });  
  
  // technical analysis indicators
  $('#i-indicator-type').on('change', function() {
    var type = $(this).val();    
    
    if ($.inArray(type, ['simpleMovingAverage','exponentialMovingAverage','weightedMovingAverage']) !== -1) {
      $('.indicator-settings').fadeIn();
    }
  });
  
  // react on adding technical analysis indicators
  $('#b-add-indicator').on('click', function(e) {
    e.preventDefault();
    var $indicatorType = $('#i-indicator-type');
    var indicatorType = $indicatorType.val();
    var indicatorName = $indicatorType.find('option:selected').text();
    var indicatorParams, indicatorTitle;
    
    if (!indicatorType) return;
    
    if ($.inArray(indicatorType, ['simpleMovingAverage','exponentialMovingAverage','weightedMovingAverage']) !== -1) {
      var numberOfDays = parseInt($('#i-indicator-num-of-days').val());      
      numberOfDays = numberOfDays > 0 ? numberOfDays : 10;
      var color = $('#i-indicator-color').spectrum('get').toHexString();
      indicatorTitle = indicatorName + ' (' + numberOfDays + ')';
      indicatorParams = {type: indicatorType, title: indicatorTitle, numberOfDays: numberOfDays, color: color};      
    }
     
    $('#indicators-container').append('<div class="ui large label"><input type="hidden" class="i-indicator" data-type="'+indicatorType+'" data-params=\''+JSON.stringify(indicatorParams)+'\' value="">'+indicatorTitle+'<i class="delete icon b-remove-indicator"></i></div>');
  });
  
  // remove indicators
  $('#indicators-container').on('click', '.b-remove-indicator', function() {
    $(this).parent().remove();    
  });
  
  $('#b-build-chart').on('click', function(e) {
    e.preventDefault();
    
    // hide Show HTML button
    $('#i-show-html').hide();
    // hide error message
    $('#f-error-msg').hide();
    $('#f-error-msg p').remove();
    
    // get chart settings from input
    var symbol = $('#i-stock-symbol').val();
    var startDate = $('#i-start-date').val();
    var errorMsg = '';
        
    if (!symbol) errorMsg += '<p>Please input Stock Symbol</p>';    
    if (!startDate) errorMsg += '<p>Please input Start Date</p>';
    
    if(errorMsg) {
      $('#f-error-msg').append(errorMsg);
      $('#f-error-msg').fadeIn();
      return false;
    }
    
    var endDate = $('#i-end-date').val();
    var width = $('#i-width').val();
    var height = $('#i-height').val();
    var type = $('#i-type').val();
    // titles
    var title = $('#i-title').val();
    var xAxisTitle = $('#i-x-title').val();
    var yAxisTitle = $('#i-y-title').val();
    var titleColor = $('#i-title-color').spectrum('get').toHexString();
    var xAxisTitleColor = $('#i-x-title-color').spectrum('get').toHexString();
    var yAxisTitleColor = $('#i-y-title-color').spectrum('get').toHexString();
    var titleFontSize = $('#i-title-font-size').val();
    var xTitleFontSize = $('#i-x-title-font-size').val();
    var yTitleFontSize = $('#i-y-title-font-size').val();    
    // falling candle
    var fallingColor = $('#i-falling-color').spectrum('get').toHexString();
    var fallingBorderColor = $('#i-falling-border-color').spectrum('get').toHexString();
    var fallingBorderWidth = $('#i-falling-border-width').val();
    // falling candle    
    var risingColor = $('#i-rising-color').spectrum('get').toHexString();
    var risingBorderColor = $('#i-rising-border-color').spectrum('get').toHexString();
    var risingBorderWidth = $('#i-rising-border-width').val();
    // line chart
    var dataField = $('#i-line-data-field').val();
    
    var color = $('#i-color').spectrum('get').toHexString();
    var gridColor = $('#i-grid-color').spectrum('get').toHexString();
    var containerBackground = $('#i-container-background').spectrum('get').toHexString();
    var chartBackground = $('#i-chart-background').spectrum('get').toHexString();
    
    var zoom = $('#i-zoom').parent().hasClass('checked');
    var navigation = $('#i-navigation-chart').parent().hasClass('checked');
    var navigationChartType = $('#i-navigation-type').val();
    var navigationDays = $('#i-navigation-days').val();
    var navigationChartOptions = {};    
    
    var chartOptions = {
        legend: 'none',
        title: title,
        titleTextStyle: {
          color: titleColor, 
          fontSize: titleFontSize ? titleFontSize : 12 
        },
        tooltip: {
          isHtml: true
        },
        areaOpacity: 0.3, // for Area charts
        aggregationTarget: 'auto',
        backgroundColor : containerBackground,
        colors: [color],
        candlestick: {
          fallingColor: { 
            stroke: fallingBorderColor, 
            strokeWidth: fallingBorderWidth ? fallingBorderWidth : 1, 
            fill: fallingColor
          },
          risingColor: { 
            stroke: risingBorderColor, 
            strokeWidth: risingBorderWidth ? risingBorderWidth : 1, 
            fill: risingColor
          }
        },
        chartArea: {
          //height: height ? height : 500,
          width: '80%',
          backgroundColor: {
            fill: chartBackground
          }
        },
        fontSize: 12,
        hAxis: {
          title: xAxisTitle,
          titleTextStyle: {
            color: xAxisTitleColor,
            fontSize: xTitleFontSize ? xTitleFontSize : 12
          },
          textStyle: {color: xAxisTitleColor},
          gridlines: {color: gridColor}
        },        
        vAxis: {
          title: yAxisTitle,
          titleTextStyle: {
            color: yAxisTitleColor,
            fontSize: yTitleFontSize ? yTitleFontSize : 12
          },
          textStyle: {color: yAxisTitleColor},
          format: 'decimal',
          gridlines: {color: gridColor}
        }
    };

    if (zoom) {
      chartOptions.explorer = {axis: 'horizontal', zoomDelta: 1.2};
    }
    
    if (navigation) {
      var columns = type=='CandlestickChart' ? [0,3] : [0,1]; 
      navigationChartOptions = {          
          filterColumnIndex: 0, // Filter by the date axis.
          ui: {
            chartType: navigationChartType ? navigationChartType : 'LineChart',
            chartOptions: {
              chartArea: {
                width: '80%', 
                height: '100%'
              },
              hAxis: {baselineColor: 'none'},
              vAxis: {baselineColor: 'none'}
            },
            chartView: {
              columns: columns
            },            
            minRangeSize: 86400000 // 1 day in milliseconds = 24 * 60 * 60 * 1000 = 86,400,000
          }
        };
    }
    
    var indicators = [];
    $('.i-indicator').each(function(i, indicator) {
      var $indicator = $(indicator);
      //indicators.push({type: $indicator.data('type'), params: $indicator.data('params').toString().split(',')});
      indicators.push($indicator.data('params'));
    });    
    
    // fill chart settings
    var $chart = $('#custom-stock-chart');      
    $chart.data('symbol', symbol);
    $chart.data('start-date', startDate);
    $chart.data('end-date', endDate);
    $chart.data('type', type ? type : 'CandlestickChart');    
    $chart.data('zoom', zoom);
    $chart.data('navigation', navigation);    
    $chart.data('navigation-days', navigationDays ? navigationDays : 30);
    $chart.data('navigation-options', navigationChartOptions);
    $chart.data('chart-options', chartOptions);
    $chart.data('width', width);
    $chart.data('height', height ? height : 500);    
    $chart.data('field', dataField ? dataField : 'Close');
    $chart.data('indicators', indicators);
    dscBuildChart($chart[0]); // $chart[0] is DOM object
 
    $('#snippet').text(getHtmlSnippet($chart));
    setTimeout(function() {
      $('#i-show-html').fadeIn();
    }, 1500);
  });
  
  function getHtmlSnippet($chart) {
    return '<div id="chart-'+$chart.data('symbol')+'-'+$chart.data('type')+'" class="dschart" data-symbol="'+$chart.data('symbol')+'" data-type="'+$chart.data('type')+'" data-start-date="'+$chart.data('start-date')+'" data-end-date="'+$chart.data('end-date')+'" data-chart-options=\''+JSON.stringify($chart.data('chart-options'))+'\' data-field="'+$chart.data('field')+'" data-zoom="'+$chart.data('zoom')+'" data-navigation="'+$chart.data('navigation')+'" data-navigation-days="'+$chart.data('navigation-days')+'" data-navigation-options=\''+JSON.stringify($chart.data('navigation-options'))+'\' data-width="'+$chart.data('width')+'" data-height="'+$chart.data('height')+'" data-indicators=\''+JSON.stringify($chart.data('indicators'))+'\'></div>';
  }

  $('#i-stock-symbol')
    .bind('keyup', function () {
      $this = $(this);
      setTimeout(function () {
        searchForSymbol($this.val());
      }, 200);
  });

  $('.symbol-search-dropdown').on('click', '.symbol-search-dropdown-item', function() {
    var $this = $(this);
    $('#i-stock-symbol').val($this.data('symbol'));
    $this.parent().hide();
  });

  function searchForSymbol(value) {
    var $searchDropdown = $('.symbol-search-dropdown');
    if (value) {
      var script = document.createElement("script");
      script.src = (window.autocomplete_path?window.autocomplete_path:'autocomplete.php')+'?url=' + encodeURIComponent("https://s.yimg.com/aq/autoc?query=" + value + "&region=US&lang=en-US&callback=displaySearchResults");
      document.documentElement.insertBefore(script, document.documentElement.lastChild);
      document.documentElement.removeChild(script);
    } else {
      $searchDropdown.empty();
      $searchDropdown.hide();
    }

    displaySearchResults = function (result) {
      //clear search dropdown
      $searchDropdown.empty();
      if (typeof result.ResultSet.Result != 'undefined' && result.ResultSet.Result.length>0) {
        var data = result.ResultSet.Result;
        var regex = new RegExp('(' + result.ResultSet.Query + ')', 'gi');
        // loop through search results
        for (var i in data) {
          var symbolAndName = '<span>'+(data[i].symbol+' '+data[i].name).replace(regex, '<strong>$1</strong>')+'</span>';
          var type = '<span class="symbol-type">'+data[i].typeDisp+'</span>';
          var exchange = '<span class="symbol-exchange">'+data[i].exchDisp+'</span>';
          $searchDropdown.append('<div class="symbol-search-dropdown-item" data-symbol="' + data[i].symbol + '">' + symbolAndName+type+exchange+'</div>');
        }
        $searchDropdown.show();
      }
    };
  }
});