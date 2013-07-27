!function(){
  // Load the Visualization API and the piechart package.
  google.load('visualization', '1.0', {'packages':['corechart']});

  // Set a callback to run when the Google Visualization API is loaded.
  google.setOnLoadCallback(drawChart);

  var pieData,
      fbCount = 0,
      vkCount = 0,
      twCount = 0,
      gpCount = 0,
      render;

  // Callback that creates and populates a data table,
  // instantiates the pie chart, passes in the data and
  // draws it.
  function drawChart() {

    // Create the data table.
    pieData = new google.visualization.DataTable();
    pieData.addColumn('string', 'Социальная сеть');
    pieData.addColumn('number', 'Процент');
    pieData.addRows([
        ['Vk.com', vkCount],
        ['facebook', fbCount],
        ['twitter', twCount],
        ['Google +', gpCount],
        ]);

    // Set chart options
    var options = {
      'width':160,
      'height':150,
      pieSliceText: 'value',
      backgroundColor: 'transparent',
      'colors': ['#5579a0', '#3c5a99', '#5dd5fc', '#d65140'],
      'legend':'none'};

    // Instantiate and draw our chart, passing in some options.
    var el = document.getElementById('socialPie');
    var chart = new google.visualization.PieChart(el);
    render = function()
    {
      chart.draw(pieData, options);
      updateTotalCounter();
    };

    render();
    updatechart();
  }

  $('#socialPieContainer a').off('click').on('click', function(){
    var $this = $(this);
    if ($this.data('clicked') == undefined) {
      $this.data('clicked', true);
      setTimeout(function () {
        $('#socialPieTotal').text(parseInt($('#socialPieTotal').text())+1);
      }, 500);
    }
    var left = (screen.width/2)-(700/2);
    var top = (screen.height/2)-(400/2);
    window.open($this.attr("href"),'displayWindow', 'width=700,height=400,left='+left+',top='+top+',location=no, directories=no,status=no,toolbar=no,menubar=no');
    return false;
  });

  function updatechart()
  {
    var url = location.protocol + location.hostname + location.pathname; 

    // fb
    $.getJSON('http://api.facebook.com/restserver.php?method=links.getStats&callback=?&urls=' + url + '&format=json', function(data) {
        fbCount = data[0].share_count;
        pieData.setValue(1, 1, fbCount);
        render();
        });

    // vk
    window.VK.Share = {};
    VK.Share.count = function(index, count){
      vkCount = count;
      pieData.setValue(0, 1, vkCount);
      render();
    };
    $.getJSON('http://vkontakte.ru/share.php?act=count&index=1&url=' + url + '&format=json&callback=?');

    // tw
    $.getJSON('http://urls.api.twitter.com/1/urls/count.json?url=' + url + '&callback=?', function(data) {
        twCount = data.count;
        pieData.setValue(2, 1, twCount);
        render();
        });

    // surf
    //$.getJSON('/surfingbird?url=' + url, function (data) {
    //  elem.socials['sb'].count = data;
    //  loadCount();
    //});

    // google
    $.getJSON('/sociality/counter?sn=google&url=' + url, function (data) {
      gpCount = data.count;
      pieData.setValue(3, 1, gpCount);
      render();
    });

  };

  function updateTotalCounter()
  {
    document.getElementById('socialPieTotal').innerHTML = vkCount + fbCount + twCount + gpCount;
  }
}();
