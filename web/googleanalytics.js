// moment.locale(locale);

gapi.analytics.ready(function() {

  $('#adblocker-notice').hide();

  gapi.analytics.auth.authorize({
    serverAuth: {
      access_token: access_token.access_token
    }
  });

  var activeUsers = new gapi.analytics.ext.ActiveUsers({
    container: 'active-users-container',
    pollingInterval: 5
  });

  activeUsers.once('success', function() {
    var element = this.container.firstChild;
    var timeout;

    this.on('change', function(data) {
      var element = this.container.firstChild;
      var animationClass = data.delta > 0 ? 'is-increasing' : 'is-decreasing';
      element.className += (' ' + animationClass);

      clearTimeout(timeout);
      timeout = setTimeout(function() {
        element.className =
        element.className.replace(/ is-(increasing|decreasing)/g, '');
      }, 3000);
    });
  });

  // Start tracking active users for this view.
  activeUsers.set(data).execute();

  // Render all the of charts for this view.
  renderLastWeekChart(data.ids);
  renderWeekOverWeekChart(data.ids);
  renderYearOverYearChart(data.ids);
  renderTopCitiesChart(data.ids);
  renderNewReturningChart(data.ids);
  renderMobileChart(data.ids);


  /**
  * Draw the a chart.js line chart with data from the specified view that
  * overlays session data for the current week over session data for the
  * previous week.
  */
  function renderLastWeekChart(ids) {

    // Adjust `now` to experiment with different days, for testing only...
    var now = moment(); // .subtract(3, 'day');

    var thisWeek = query({
      'ids': ids,
      'dimensions': 'ga:date,ga:nthDay',
      'metrics': 'ga:sessions,ga:pageviews',
      'start-date': moment(now).subtract(6, 'day').format('YYYY-MM-DD'),
      'end-date': moment(now).format('YYYY-MM-DD')
    });

    var lastWeek = query({
      'ids': ids,
      'dimensions': 'ga:date,ga:nthDay',
      'metrics': 'ga:sessions,ga:pageviews',
      'start-date': moment(now).subtract(13, 'day').format('YYYY-MM-DD'),
      'end-date': moment(now).subtract(6, 'day').subtract(1, 'day')
      .format('YYYY-MM-DD')
    });

    // console.log(thisWeek);
    // console.log(lastWeek);

    Promise.all([thisWeek, lastWeek]).then(function(results) {
      // console.log(results);
      var data1 = results[0].rows.map(function(row) { return +row[2]; });
      var data2 = results[1].rows.map(function(row) { return +row[2]; });
      var data1a = results[0].rows.map(function(row) { return +row[3]; });
      var data2a = results[1].rows.map(function(row) { return +row[3]; });
      var labels = results[0].rows.map(function(row) { return +row[0]; });

      // console.log(data1);
      // console.log(data1a);

      labels = labels.map(function(label) {
        return moment(label, 'YYYYMMDD').format('Do');
      });

      var data = {
        labels : labels,
        datasets : [
        {
          label: GAtranslations.pageviewslastweek,
          fillColor : "rgba(121,222,130,0.2)",
          strokeColor : "rgba(121,222,130,0.4)",
          pointColor : "rgba(121,222,130,0.6)",
          pointStrokeColor : "#ccc",
          data : data2a
        },

        {
          label: GAtranslations.pageviewsthisweek,
          fillColor : "rgba(121,222,130,0.5)",
          strokeColor : "rgba(121,222,130,0.8)",
          pointColor : "rgba(121,222,130,1)",
          pointStrokeColor : "rgba(0,0,0,0)",
          data : data1a
        },
        {
          label: GAtranslations.lastweek,
          fillColor : "rgba(101,120,225,0.2)",
          strokeColor : "rgba(101,120,225,0.4)",
          pointColor : "rgba(101,120,225,0.6)",
          data : data2
        },
        {
          label: GAtranslations.thisweek,
          fillColor : "rgba(101,120,225,0.5)",
          strokeColor : "rgba(101,120,225,0.8)",
          pointColor : "rgba(101,120,225,1)",
          pointStrokeColor : "rgba(0,0,0,0)",
          data : data1
        }
        ]
      };

      new Chart(makeCanvas('chart-0-container')).Line(data);
      generateLegend('legend-0-container', data.datasets);
    });

  }

  /**
  * Draw the a chart.js line chart with data from the specified view that
  * overlays session data for the current week over session data for the
  * previous week.
  */
  function renderWeekOverWeekChart(ids) {

    // Adjust `now` to experiment with different days, for testing only...
    var now = moment(); // .subtract(3, 'day');

    var thisWeek = query({
      'ids': ids,
      'dimensions': 'ga:date,ga:nthDay',
      'metrics': 'ga:sessions',
      'start-date': moment(now).date(1).format('YYYY-MM-DD'),
      'end-date': moment(now).format('YYYY-MM-DD')
    });

    var lastWeek = query({
      'ids': ids,
      'dimensions': 'ga:date,ga:nthDay',
      'metrics': 'ga:sessions',
      'start-date': moment(now).date(1).subtract(1, 'day').date(1)
      .format('YYYY-MM-DD'),
      'end-date': moment(now).date(1).subtract(1, 'day')
      .format('YYYY-MM-DD')
    });

    Promise.all([thisWeek, lastWeek]).then(function(results) {
      var data1 = results[0].rows.map(function(row) { return +row[2]; });
      var data2 = results[1].rows.map(function(row) { return +row[2]; });
      var labels = results[0].rows.map(function(row) { return +row[0]; });
      var labels2 = results[1].rows.map(function(row) { return +row[0]; });
      if(labels2.length > labels.length){
        labels = labels2;
      }
      labels = labels.map(function(label) {
        return moment(label, 'YYYYMMDD').format('Do');
      });

      var data = {
        labels : labels,
        datasets : [
        {
          label: GAtranslations.lastmonth,
          fillColor : "rgba(220,220,220,0.5)",
          strokeColor : "rgba(220,220,220,1)",
          pointColor : "rgba(220,220,220,1)",
          pointStrokeColor : "#fff",
          data : data2
        },
        {
          label: GAtranslations.thismonth,
          fillColor : "rgba(151,187,205,0.5)",
          strokeColor : "rgba(151,187,205,1)",
          pointColor : "rgba(151,187,205,1)",
          pointStrokeColor : "#fff",
          data : data1
        }
        ]
      };

      new Chart(makeCanvas('chart-1-container')).Line(data);
      generateLegend('legend-1-container', data.datasets);
    });

  }


  /**
  * Draw the a chart.js bar chart with data from the specified view that
  * overlays session data for the current year over session data for the
  * previous year, grouped by month.
  */
  function renderYearOverYearChart(ids) {

    // Adjust `now` to experiment with different days, for testing only...
    var now = moment(); // .subtract(3, 'day');

    var thisYear = query({
      'ids': ids,
      'dimensions': 'ga:month,ga:nthMonth',
      'metrics': 'ga:users',
      'start-date': moment(now).date(1).month(0).format('YYYY-MM-DD'),
      'end-date': moment(now).format('YYYY-MM-DD')
    });

    var lastYear = query({
      'ids': ids,
      'dimensions': 'ga:month,ga:nthMonth',
      'metrics': 'ga:users',
      'start-date': moment(now).subtract(1, 'year').date(1).month(0)
      .format('YYYY-MM-DD'),
      'end-date': moment(now).date(1).month(0).subtract(1, 'day')
      .format('YYYY-MM-DD')
    });

    Promise.all([thisYear, lastYear]).then(function(results) {
      var data1 = results[0].rows.map(function(row) { return +row[2]; });
      var data2 = results[1].rows.map(function(row) { return +row[2]; });
      var labels = ['Jan','Feb','Mar','Apr','Maj','Jun',
      'Jul','Aug','Sep','Oct','Nov','Dec'];

    // Ensure the data arrays are at least as long as the labels array.
    // Chart.js bar charts don't (yet) accept sparse datasets.
    for (var i = 0, len = labels.length; i < len; i++) {
      if (data1[i] === undefined) data1[i] = null;
      if (data2[i] === undefined) data2[i] = null;
    }

    var data = {
      labels : labels,
      datasets : [
      {
        label: GAtranslations.lastyear,
        fillColor : "rgba(220,220,220,0.5)",
        strokeColor : "rgba(220,220,220,1)",
        data : data2
      },
      {
        label: GAtranslations.thisyear,
        fillColor : "rgba(151,187,205,0.5)",
        strokeColor : "rgba(151,187,205,1)",
        data : data1
      }
      ]
    };

    new Chart(makeCanvas('chart-2-container')).Bar(data);
    generateLegend('legend-2-container', data.datasets);
    })
    .catch(function(err) {
      console.error(err.stack);
    });

  }



  /**
  * Draw the a chart.js doughnut chart with data from the specified view that
  * compares sessions from mobile, desktop, and tablet over the past seven
  * days.
  */
  function renderTopCitiesChart(ids) {

    query({
      'ids': ids,
      'dimensions': 'ga:city',
      'metrics': 'ga:sessions',
      'sort': '-ga:sessions',
      'filters': 'ga:city!=(not set)',
      'max-results': 5
    })
    .then(function(response) {

      var data = [];
      var colors = ['rgba(65, 168, 95, 0.5)', 'rgba(0, 168, 133, 0.5)', 'rgba(61, 142, 185, 0.5)', 'rgba(41, 105, 176, 0.5)', 'rgba(85, 57, 130, 0.5)', 'rgba(40, 50, 78, 0.5)', 'rgba(250, 197, 28, 0.5)', 'rgba(243, 121, 52, 0.5)', 'rgba(209, 72, 65, 0.5)', 'rgba(184, 49, 47, 0.5)', 'rgba(247, 218, 100, 0.5)', 'rgb(235, 107, 86, 0.5)'];

      response.rows.forEach(function(row, i) {
        data.push({
          label: row[0],
          value: +row[1],
          color: colors[i]
        });
      });

      new Chart(makeCanvas('chart-3-container')).Doughnut(data);
      generateLegend('legend-3-container', data);
    });

  }

  function renderNewReturningChart(ids) {
    query({
      'ids': ids,
      'dimensions': 'ga:userType',
      'metrics': 'ga:sessions'
    })
    .then(function(response) {

      var data = [];
      var colors = ['rgba(65, 168, 95, 0.5)', 'rgba(0, 168, 133, 0.5)', 'rgba(61, 142, 185, 0.5)', 'rgba(41, 105, 176, 0.5)', 'rgba(85, 57, 130, 0.5)', 'rgba(40, 50, 78, 0.5)', 'rgba(250, 197, 28, 0.5)', 'rgba(243, 121, 52, 0.5)', 'rgba(209, 72, 65, 0.5)', 'rgba(184, 49, 47, 0.5)', 'rgba(247, 218, 100, 0.5)', 'rgb(235, 107, 86, 0.5)'];

      response.rows.forEach(function(row, i) {
        data.push({
          label: row[0],
          value: +row[1],
          color: colors[i]
        });
      });

      new Chart(makeCanvas('chart-4-container')).Doughnut(data);
      generateLegend('legend-4-container', data);
    });
  }

  function renderMobileChart(ids) {
    query({
      'ids': ids,
      'dimensions': 'ga:deviceCategory',
      'metrics': 'ga:sessions'
    })
    .then(function(response) {

      var data = [];
      var colors = ['rgba(65, 168, 95, 0.5)', 'rgba(0, 168, 133, 0.5)', 'rgba(61, 142, 185, 0.5)', 'rgba(41, 105, 176, 0.5)', 'rgba(85, 57, 130, 0.5)', 'rgba(40, 50, 78, 0.5)', 'rgba(250, 197, 28, 0.5)', 'rgba(243, 121, 52, 0.5)', 'rgba(209, 72, 65, 0.5)', 'rgba(184, 49, 47, 0.5)', 'rgba(247, 218, 100, 0.5)', 'rgb(235, 107, 86, 0.5)'];

      response.rows.forEach(function(row, i) {
        data.push({
          label: row[0],
          value: +row[1],
          color: colors[i]
        });
      });

      new Chart(makeCanvas('chart-5-container')).Doughnut(data);
      generateLegend('legend-5-container', data);
    });

  }

  /**
  * Extend the Embed APIs `gapi.analytics.report.Data` component to
  * return a promise the is fulfilled with the value returned by the API.
  * @param {Object} params The request parameters.
  * @return {Promise} A promise.
  */
  function query(params) {
    return new Promise(function(resolve, reject) {
      var data = new gapi.analytics.report.Data({query: params});
      data.once('success', function(response) { resolve(response); })
      .once('error', function(response) { reject(response); })
      .execute();
    });
  }

  /**
  * Create a new canvas inside the specified element. Set it to be the width
  * and height of its container.
  * @param {string} id The id attribute of the element to host the canvas.
  * @return {RenderingContext} The 2D canvas context.
  */
  function makeCanvas(id) {
    var container = document.getElementById(id);
    var canvas = document.createElement('canvas');
    var ctx = canvas.getContext('2d');

    container.innerHTML = '';
    canvas.width = container.offsetWidth;
    canvas.height = container.offsetHeight;
    container.appendChild(canvas);

    return ctx;
  }

  /**
  * Create a visual legend inside the specified element based off of a
  * Chart.js dataset.
  * @param {string} id The id attribute of the element to host the legend.
  * @param {Array.<Object>} items A list of labels and colors for the legend.
  */
  function generateLegend(id, items) {
    var legend = document.getElementById(id);
    legend.innerHTML = items.map(function(item) {
      var color = item.color || item.fillColor;
      var label = item.label;
      switch(label) {
        case "Returning Visitor":
        label = GAtranslations.returningvisitors;
        break;
        case "New Visitor":
        label = GAtranslations.newvisitors;
        break;
        case "mobile":
        label = GAtranslations.phone;
        break;
        case "tablet":
        label = GAtranslations.tablet;
        break;
        case "desktop":
        label = GAtranslations.computer;
        break;
      }
      return '<li><i style="background:' + color + '"></i>' + label + '</li>';
    }).join('');
  }

  // Set some global Chart.js defaults.
  Chart.defaults.global.animation = false;
  Chart.defaults.global.responsive = false;
  Chart.defaults.global.maintainAspectRatio = false;

});
