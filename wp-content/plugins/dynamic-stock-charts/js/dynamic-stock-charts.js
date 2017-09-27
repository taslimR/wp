/**
 * Dynamic Stock Charts (jQuery plugin)
 * Version 1.2.0, built on Mon Apr 25 2016
 * Copyright WebTheGap <mail@webthegap.com>
 * http://webthegap.com/widgets/dynamic-stock-charts/
 */
(function($) {

  google.load('visualization', '1', {packages:['corechart','controls']});
  google.setOnLoadCallback(buildCharts);

  var yqlBaseUri = 'https://query.yahooapis.com/v1/public/yql?q={0}&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys';
  var yqlQuery = 'select * from {0} where {1}';
  var yqlMapTableToKeyPropertyName = {'yahoo.finance.quotes':'Symbol', 'yahoo.finance.xchange':'id', 'feed':'pubDate'};
  var yqlMapTableToWidgetType = {'yahoo.finance.quotes':'quote', 'yahoo.finance.xchange':'currency', 'feed':'news', 'html':'stats'};
  var yqlNumberOfDaysPerQuery = 500; 
  var DEBUG = false;
  
  // making dscBuildChart() available in the global scope
  window.dscBuildChart = buildChart;
  
  // rebuild charts on resize event
  $(window).resize(function() {
    buildCharts();
  });
  
  function buildCharts() {    
    $('.dschart').each(function(i, chartDiv) {
      buildChart(chartDiv);
    });    
  }
  
  function buildChart(chartDiv) {       
    var $chartDiv = $(chartDiv);
    var chartId = $chartDiv.attr('id'); 
    var chartSymbol = $chartDiv.data('symbol');
    var chartStartDate = $chartDiv.data('start-date');
    var chartEndDate = $chartDiv.data('end-date') ? $chartDiv.data('end-date') : (new Date()).format('Y-m-d');      
    var chartPeriods = buildTimeIntervals(chartStartDate, chartEndDate, yqlNumberOfDaysPerQuery);
    var chartPeriodsNumber = chartPeriods.length;
    
    // if symbol is empty continue to next one
    if (chartSymbol=='') return true;    
    
    // remove all children if any, clear necessary data attributes
    $chartDiv.empty();
    $chartDiv.removeData('chart-periods');
    $chartDiv.removeData('chart-data');
         
    // add spinning loader
    if ($chartDiv.find('.dsc-loader').length == 0) {
      $chartDiv.prepend('<div class="active dsc-loader"></div>');
    }
    
    // split time frame into subperiods (due to YF query limits) and receive data from YF for each subperiod
    $.each(chartPeriods, function(i, period) {
      log(String.format('{0} FROM {1} TO {2}',chartSymbol, period.start, period.end));  
      yqlRunQuery(
          'yahoo.finance.historicaldata', 
          String.format('symbol = "{0}" and startDate = "{1}" and endDate = "{2}"', chartSymbol, period.start, period.end), 
          {periodId: i, totalPeriods: chartPeriodsNumber, chartId: chartId, symbol: chartSymbol}
      );
    });
  }
  
   
  /**
   * Index data by date, convert string to numbers
   * @param arrayOfObjects
   * @returns object of objects
   */
  function formatYqlData(arrayOfObjects) {
    var result = {};
    
    // if only one object is received then transform it into array first
    if (!$.isArray(arrayOfObjects)) {
      arrayOfObjects = [arrayOfObjects];
    }
    
    $.each(arrayOfObjects, function(i, object) {
      var date = new Date(object.Date);
      var dateProperty = date.getTime();
      result[dateProperty] = {};
      result[dateProperty].DateString = object.Date;
      result[dateProperty].Date = date;
      result[dateProperty].Open = Math.round(parseFloat(object.Open) * 100) / 100;
      result[dateProperty].High = Math.round(parseFloat(object.High) * 100) / 100;
      result[dateProperty].Low = Math.round(parseFloat(object.Low) * 100) / 100;
      result[dateProperty].Close = Math.round(parseFloat(object.Close) * 100) / 100;
      result[dateProperty].AdjClose = Math.round(parseFloat(object.Adj_Close) * 100) / 100;
      result[dateProperty].Volume = parseInt(object.Volume);
    });
    
    return result;
  }
  
  /**
   * Build & display chart
   */
  function displayChart($chart, chartData) {

    var chartId = $chart.attr('id');
    var symbol = $chart.data('symbol');
    var navigationChartEnabled = $chart.data('navigation')===true ? true : false;
    var zoomEnabled = $chart.data('zoom')===true ? true : false;
    var datePropertyName = navigationChartEnabled ? 'Date' : 'DateString';    
    var navigationChartDays = $chart.data('navigation-days');    
    var chartWidth = $chart.data('width') ? $chart.data('width') : '100%';
    var chartHeight = $chart.data('height');
    var chartType = $chart.data('type');
    var chartDataField = $chart.data('field');
    var chartEndDate = $chart.data('end-date') ? new Date($chart.data('end-date')) : new Date();
    var indicators = $chart.data('indicators') ? $chart.data('indicators') : [];
    var indicatorsLength = indicators.length;
    var indicatorData = [];
    var chartDataLength = chartData.length;
    var i, k;

    var gChartOptions = $chart.data('chart-options');
    var gNavigationChartOptions = $chart.data('navigation-options');

    /* BEGIN MAIN PART */
    
    // set width and height of chart container
    $chart.width(chartWidth);
    $chart.height(chartHeight);
    
    log('Chart options', gChartOptions);
    
    // Fill in DataTable based on the chart type
    var data = [];
    // Candlestick chart
    if (chartType=='CandlestickChart') {      
      data.push([
         {type: navigationChartEnabled || zoomEnabled ? 'date' : 'string', label: 'Date'}, // navigation and zoom work only with continious axis, i.e. date, number
         {type: 'number', label: 'Low'},
         {type: 'number', label: 'Open'},
         {type: 'number', label: 'Close'},
         {type: 'number', label: 'High'},
         {type: 'string', role: 'tooltip', p: {html: true}}
      ]);
      
      // need to iterate through sorted dates, because object properties are not sortable
      for (i=0; i<chartDataLength; i++) {
        data.push([navigationChartEnabled || zoomEnabled ? chartData[i].Date : chartData[i].DateString, chartData[i].Low, chartData[i].Open, chartData[i].Close, chartData[i].High, getTooltip(symbol, chartData[i])]);      
      }      
      
    // Line chart, Area chart
    } else {     
      data.push([
         {type: navigationChartEnabled || zoomEnabled ? 'date' : 'string', label: 'Date'}, // navigation and zoom work only with continious axis, i.e. date, number
         {type: 'number', label: chartDataField},
         {type: 'string', role: 'tooltip', p: {html: true}}
      ]);      
      
      // need to iterate through sorted dates, because object properties are not sortable
      for (i=0; i<chartDataLength; i++) {
        data.push([navigationChartEnabled || zoomEnabled ? chartData[i].Date : chartData[i].DateString, chartData[i][chartDataField], getTooltip(symbol, chartData[i])]);
      }
    }    
    
    for (i=0; i<indicatorsLength; i++) {
      indicatorData = getIndicatorData(indicators[i], chartData);
      for (k=0; k<chartDataLength+1; k++) {
        data[k].push(indicatorData[k]);
        // adding tooltip series
        if (k==0) {
          data[k].push({type: 'string', role: 'tooltip', p: {html: true}});
        } else {
          data[k].push(getIndicatorTooltip(indicators[i].title, indicatorData[k]));
        }
      }
      log('indicatorData', indicatorData);
    }
    
    var gData = google.visualization.arrayToDataTable(data);
    
    // specify chart type, color for indicator series (this is the same for charts with and w/o navigation)
    gChartOptions['series'] = {};
    for (i=0; i<indicatorsLength; i++) {            
      gChartOptions['series'][(i+1)] = {type: 'line', color: indicators[i].color};
    }    
    
    // Draw dashboard or chart depending on options
    if (navigationChartEnabled) {
      
      $chart.append('<div id="'+chartId+'-chart" style="height: '+(chartHeight-80)+'px;"></div>');
      $chart.append('<div id="'+chartId+'-control" style="height: 80px; margin-top: -20px;"></div>');
      
      var dashboard = new google.visualization.Dashboard(document.getElementById(chartId));

      var control = new google.visualization.ControlWrapper({
        controlType: 'ChartRangeFilter',
        containerId: chartId+'-control',
        options: gNavigationChartOptions,
        state: {
          range: {
            start: new Date(chartEndDate.getTime()-1000*60*60*24*navigationChartDays), 
            end: chartEndDate
          }
        }
      });

      var chart = new google.visualization.ChartWrapper({
        chartType: chartType,
        containerId: chartId+'-chart',
        options: gChartOptions          
      });

      dashboard.bind(control, chart);
      dashboard.draw(gData);        
      
    // chart without history navigation
    } else {
      var gChart;
      switch(chartType) {
        case 'LineChart':
          gChartOptions['seriesType'] = 'line';
          break;
        case 'AreaChart':
          gChartOptions['seriesType'] = 'area';
          
          break;
        case 'CandlestickChart':
          gChartOptions['seriesType'] = 'candlesticks';
          break;
      }   
      gChart = new google.visualization.ComboChart(document.getElementById(chartId));
      gChart.draw(gData, gChartOptions);
    }      
  }
  
  /**
   * Round number to 2 decimal places, because there is no native JS equivalent of this function
   */
  function round(num) {
    return Math.round(parseFloat(num) * 100) / 100;
  }
  
  /**
   * Return array of indicator data
   */
  function getIndicatorData(indicator, chartData) {
    log('Indicator settings', indicator);
    var result = [];// = [{type: 'number', label: indicator.title}];
    var chartDataLength = chartData.length;
    var i,k, indicatorValue;
    
    if (indicator.type == 'simpleMovingAverage') {
      var numDays = parseInt(indicator.numberOfDays);
      for (i=0; i<chartDataLength; i++) {
        if (i<numDays-1) {
          indicatorValue = null;
        } else {
          /*indicatorValue = average(chartData.slice(i+1-numDays, i+1));
          log(chartData.slice(i+1-numDays, i+1));*/
          indicatorValue = 0;
          for (k=numDays-1; k>=0; k--) {
            indicatorValue += chartData[i-k].AdjClose;
          }
          indicatorValue = round(indicatorValue / numDays);
        }
        result.push(indicatorValue);
      }
    } else if (indicator.type == 'exponentialMovingAverage') {
      var numDays = parseInt(indicator.numberOfDays);
      for (i=0; i<chartDataLength; i++) {
        if (i<numDays-1) {
          indicatorValue = null;
        // EMA for the first day = SMA
        } else if(i == numDays-1) {
          indicatorValue = 0;
          for (k=numDays-1; k>=0; k--) {
            indicatorValue += chartData[i-k].AdjClose;
          }
          indicatorValue = round(indicatorValue / numDays);
        // EMA = {Close - EMA(previous day)} x multiplier + EMA(previous day)
        } else {
          indicatorValue = round((chartData[i].AdjClose - result[i-1])*(2/(1+numDays)) + result[i-1]);
        }
        result.push(indicatorValue);
      }      
    } else if (indicator.type == 'weightedMovingAverage') {
      var numDays = parseInt(indicator.numberOfDays);
      var weights = getWeights(numDays);
      var sumWeights = sum(weights);
      for (i=0; i<chartDataLength; i++) {
        if (i<numDays-1) {
          indicatorValue = null;
        } else {
          indicatorValue = 0;
          for (k=numDays-1; k>=0; k--) {
            indicatorValue += chartData[i-k].AdjClose*weights[k];
          }
          indicatorValue = round(indicatorValue / sumWeights);
        }
        result.push(indicatorValue);
      }      
    }
    
    result.unshift({type: 'number', label: indicator.title});
    return result;
  }
  
  /**
   * Return average number based on given array
   */
  function average(numbers) {
    var result = 0;
    var numbersLength = numbers.length;
    for (var k=0; k<numbersLength; k++) {
      result += numbers[k];
    }
    return result / numbersLength;
  }
  
  /**
   * Return sum of array elements numbers
   */
  function sum(numbers) {
    var result = 0;
    var numbersLength = numbers.length;
    for (var k=0; k<numbersLength; k++) {
      result += numbers[k];
    }
    return result;
  }
  
  /**
   * Return weights array for weighted moving average calculation
   */
  function getWeights(number) {
    result = [];
    for (var k=number; k>=1; k--) {
      result.push(k);
    }
    return result;
  }
  
  /**
   * Sort object properties and return array as a result
   */
  function getSortedObjectProperties(obj) {
    var result = [];
    
    for (k in obj) {
      if (obj.hasOwnProperty(k)) {
        result.push(k);
      }
    }
    return result.sort();
  }
  
  /**
   * Transforms result unsorted object with objects data into sorted array of objects
   */
  function getSortedData(objectData) {
    var sortedDates = [];
    var result = [];
    
    for (dateProperty in objectData) {
      if (objectData) {
        sortedDates.push(dateProperty);
      }
    }
    sortedDates = sortedDates.sort();
    
    var sortedDatesLength = sortedDates.length;
    for (var i=0; i<sortedDatesLength; i++) {
      result.push(objectData[sortedDates[i]]);
    }
    return result;
  }  
  
  /**
   * Format tooltip for a chart
   */
  function getTooltip(caption, dataObj) {
    return '<table class="dsc-tooltip"><caption>'+caption+'</caption><tr><td class="label">Date:</td><td>'+dataObj.DateString+'</td></tr><tr><td class="label">Open:</td><td>'+dataObj.Open+'</td></tr><tr><td class="label">High:</td><td>'+dataObj.High+'</td></tr><tr><td class="label">Low:</td><td>'+dataObj.Low+'</td></tr><tr><td class="label">Close:</td><td>'+dataObj.Close+'</td></tr><tr><td class="label">Adj Close:</td><td>'+dataObj.AdjClose+'</td></tr><tr><td class="label">Volume:</td><td>'+dataObj.Volume+'</td></tr></table>';
  }
  
  /**
   * Format indicator tooltip for a chart
   */
  function getIndicatorTooltip(caption, value) {
    return '<table class="dsc-tooltip"><caption>'+caption+'</caption><tr><td class="label">Value:</td><td>'+value+'</td></tr></table>';
  }  

  /**
   * Execute YQL Query in ASYNC mode
   */
  function yqlRunQuery(yqlTable, yqlConditions, context) {
    $.ajax({
      url: String.format(yqlBaseUri, encodeURIComponent(String.format(yqlQuery, yqlTable, yqlConditions))),
      dataType: 'json',
      async: true,
      context: context,
      success: yqlQuerySuccess,
      error: yqlQueryError
    });
  }  
  
  /**
   * YQL Query Success Callback
   */
  function yqlQuerySuccess(data, textStatus, jqXHR) {       
    var result = null;
    // getting data from the context    
    var chartId = this.chartId.replace(/(:|\^|\.|\[|\]|,|=)/g, "\\$1");
    var periodId = this.periodId;
    var totalPeriods = this.totalPeriods;
    
    var $chart = $('#'+chartId);
    var symbol = $chart.data('symbol');
    
    if (typeof data.query.results !== 'undefined' && data.query.count > 0) {
      // get the first child of data.query.results and transform it to indexed by date array
      result = formatYqlData(data.query.results[Object.keys(data.query.results)[0]]);
      
      // save intermediate result as object in chart data attribute
      var savedPeriods = $chart.data('chart-periods') ? parseInt($chart.data('chart-periods')) : 0;
      var savedData = $chart.data('chart-data') ? $chart.data('chart-data') : {};
      $.extend(savedData, result);
      $chart.data('chart-periods', ++savedPeriods);
      $chart.data('chart-data', savedData);
      
      // when all data is received display chart
      if (savedPeriods==totalPeriods) {
        // transform object to sorted array
        savedData = getSortedData(savedData); 
        log('Data for '+symbol, savedData);
        displayChart($chart, savedData);        
      }
    } else {
      log('No data for '+symbol, data);
      $chart.append('<div class="dsc-err-message">Ooops, no data is provided by Yahoo Finance API for '+symbol+'. Try something else.</div>');
    }
    
    $chart.find('.dsc-loader').removeClass('active');
  }
  
  function yqlQueryError(jqXHR, textStatus, errorThrown) {    
    log('smYqlQueryError', textStatus+'|'+errorThrown);
  }  
  
  function buildTimeIntervals(startDate, endDate, intervalLength) {
    var result = [];

    var currentEndDate = new Date(endDate);
    var currentStartDate = new Date(endDate);
    var startDate = new Date(startDate);
    
    while (currentEndDate.getTime() - intervalLength * 1000 * 3600 * 24 > startDate.getTime()) {
        
        currentStartDate.setDate(currentEndDate.getDate() - intervalLength);
        result.push({start: currentStartDate.format('Y-m-d'), end: currentEndDate.format('Y-m-d')});
        currentEndDate.setDate(currentEndDate.getDate() - intervalLength - 1);
    };
    result.push({start: startDate.format('Y-m-d'), end: currentEndDate.format('Y-m-d')});
    
    return result;
}  
  
  /**
   * Log message to console
   * @param msg
   */
  function log(msg, obj) {
    if (DEBUG) {
      if (typeof obj !== 'undefined') {
        console.log(msg, obj);
      } else {
        console.log(msg);
      }
    }
  }
  
  /**
   * String format function
   * http://stackoverflow.com/questions/610406/javascript-equivalent-to-printf-string-format
   */
  String.format = function(format) {
    var args = Array.prototype.slice.call(arguments, 1);
    return format.replace(/{(\d+)}/g, function(match, number) { 
      return typeof args[number] != 'undefined'
        ? args[number] 
        : match
      ;
    });
  };
  
  Date.prototype.format = function(format) {
    var returnStr = '';
    var replace = Date.replaceChars;
    for (var i = 0; i < format.length; i++) {       var curChar = format.charAt(i);         if (i - 1 >= 0 && format.charAt(i - 1) == "\\") {
            returnStr += curChar;
        }
        else if (replace[curChar]) {
            returnStr += replace[curChar].call(this);
        } else if (curChar != "\\"){
            returnStr += curChar;
        }
    }
    return returnStr;
  };
  
  Date.replaceChars = {
      shortMonths: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
      longMonths: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
      shortDays: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
      longDays: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
  
      // Day
      d: function() { return (this.getDate() < 10 ? '0' : '') + this.getDate(); },
      D: function() { return Date.replaceChars.shortDays[this.getDay()]; },
      j: function() { return this.getDate(); },
      l: function() { return Date.replaceChars.longDays[this.getDay()]; },
      N: function() { return this.getDay() + 1; },
      S: function() { return (this.getDate() % 10 == 1 && this.getDate() != 11 ? 'st' : (this.getDate() % 10 == 2 && this.getDate() != 12 ? 'nd' : (this.getDate() % 10 == 3 && this.getDate() != 13 ? 'rd' : 'th'))); },
      w: function() { return this.getDay(); },
      z: function() { var d = new Date(this.getFullYear(),0,1); return Math.ceil((this - d) / 86400000); }, // Fixed now
      // Week
      W: function() { var d = new Date(this.getFullYear(), 0, 1); return Math.ceil((((this - d) / 86400000) + d.getDay() + 1) / 7); }, // Fixed now
      // Month
      F: function() { return Date.replaceChars.longMonths[this.getMonth()]; },
      m: function() { return (this.getMonth() < 9 ? '0' : '') + (this.getMonth() + 1); },
      M: function() { return Date.replaceChars.shortMonths[this.getMonth()]; },
      n: function() { return this.getMonth() + 1; },
      t: function() { var d = new Date(); return new Date(d.getFullYear(), d.getMonth(), 0).getDate() }, // Fixed now, gets #days of date
      // Year
      L: function() { var year = this.getFullYear(); return (year % 400 == 0 || (year % 100 != 0 && year % 4 == 0)); },   // Fixed now
      o: function() { var d  = new Date(this.valueOf());  d.setDate(d.getDate() - ((this.getDay() + 6) % 7) + 3); return d.getFullYear();}, //Fixed now
      Y: function() { return this.getFullYear(); },
      y: function() { return ('' + this.getFullYear()).substr(2); },
      // Time
      a: function() { return this.getHours() < 12 ? 'am' : 'pm'; },
      A: function() { return this.getHours() < 12 ? 'AM' : 'PM'; },
      B: function() { return Math.floor((((this.getUTCHours() + 1) % 24) + this.getUTCMinutes() / 60 + this.getUTCSeconds() / 3600) * 1000 / 24); }, // Fixed now
      g: function() { return this.getHours() % 12 || 12; },
      G: function() { return this.getHours(); },
      h: function() { return ((this.getHours() % 12 || 12) < 10 ? '0' : '') + (this.getHours() % 12 || 12); },
      H: function() { return (this.getHours() < 10 ? '0' : '') + this.getHours(); },
      i: function() { return (this.getMinutes() < 10 ? '0' : '') + this.getMinutes(); },
      s: function() { return (this.getSeconds() < 10 ? '0' : '') + this.getSeconds(); },
      u: function() { var m = this.getMilliseconds(); return (m < 10 ? '00' : (m < 100 ? '0' : '')) + m; },
      // Timezone
      e: function() { return "Not Yet Supported"; },
      I: function() {
          var DST = null;
              for (var i = 0; i < 12; ++i) {
                      var d = new Date(this.getFullYear(), i, 1);
                      var offset = d.getTimezoneOffset();
  
                      if (DST === null) DST = offset;
                      else if (offset < DST) { DST = offset; break; } else if (offset > DST) break;
              }
              return (this.getTimezoneOffset() == DST) | 0;
          },
      O: function() { return (-this.getTimezoneOffset() < 0 ? '-' : '+') + (Math.abs(this.getTimezoneOffset() / 60) < 10 ? '0' : '') + (Math.abs(this.getTimezoneOffset() / 60)) + '00'; },
      P: function() { return (-this.getTimezoneOffset() < 0 ? '-' : '+') + (Math.abs(this.getTimezoneOffset() / 60) < 10 ? '0' : '') + (Math.abs(this.getTimezoneOffset() / 60)) + ':00'; }, // Fixed now
      T: function() { var m = this.getMonth(); this.setMonth(0); var result = this.toTimeString().replace(/^.+ \(?([^\)]+)\)?$/, '$1'); this.setMonth(m); return result;},
      Z: function() { return -this.getTimezoneOffset() * 60; },
      // Full Date/Time
      c: function() { return this.format("Y-m-d\\TH:i:sP"); }, // Fixed now
      r: function() { return this.toString(); },
      U: function() { return this.getTime() / 1000; }
  };  
  
})(jQuery);