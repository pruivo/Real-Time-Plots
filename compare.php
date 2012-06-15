<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
   <script src="js/excanvas.js" type="text/javascript"></script>
   <script src="js/excanvas.min.js" type="text/javascript"></script>
   <script src="js/jquery.js" type="text/javascript"></script>
   <script src="js/jquery.flot.js" type="text/javascript"></script>
   <!--script src="js/jquery.colorhelpers.js" type="text/javascript"></script>
   <script src="js/jquery.flot.categories.js" type="text/javascript"></script>
   <script src="js/jquery.flot.crosshair.js" type="text/javascript"></script>
   <script src="js/jquery.flot.fillbetween.js" type="text/javascript"></script>
   <script src="js/jquery.flot.image.js" type="text/javascript"></script>   
   <script src="js/jquery.flot.navigate.js" type="text/javascript"></script>
   <script src="js/jquery.flot.pie.js" type="text/javascript"></script>
   <script src="js/jquery.flot.resize.js" type="text/javascript"></script>
   <script src="js/jquery.flot.selection.js" type="text/javascript"></script>
   <script src="js/jquery.flot.stack.js" type="text/javascript"></script>
   <script src="js/jquery.flot.symbol.js" type="text/javascript"></script>
   <script src="js/jquery.flot.threshold.js" type="text/javascript"></script>
   <script src="js/jquery.flot.time.js" type="text/javascript"></script-->
   <title>Workload Monitor [Real Time plots]</title>
   <?php
      $folder = "files";

      if (isset($_REQUEST['apps'])) {
         $folder = $_REQUEST['apps'];
      }   	
      
      echo '<script type="text/javascript">';
      echo 'var folder = "' . $folder . '";';
      echo 'var folderArray = [';
      $array = split(",", $folder);
      echo '"'.$array[0].'"';      
      for ($idx = 1; $idx < count($array); ++$idx) {
         echo ',"'.$array[$idx].'"'; 
      }
      echo '];';
      echo '</script>';
   ?>
</head>
<body>
<h1>Workload Monitor - Real Time plots</h1>
<table style="border:1px solid black;">
   <tr>
      <td>Throughput (transaction per second)</td>
      <td>Abort Rate (percentage)</td>
      <td>Cpu Usage (percentage)</td>
   </tr>
   <tr>
      <td><div id="throughput" style="width:500px;height:300px"></div></td>
      <td><div id="abortRate" style="width:500px;height:300px"></div></td>
      <td><div id="cpu" style="width:500px;height:300px"></div></td>
   </tr>      
   <tr>
      <td>Write Percentage</td>
      <td>Commit Latency (microseconds)</td>
      <td>Memory Usage (GigaByte)</td>
   </tr>
   <tr>
      <td><div id="wrtPer" style="width:500px;height:300px"></div></td>
      <td><div id="commitLatency" style="width:500px;height:300px"></div></td>
      <td><div id="memory" style="width:500px;height:300px"></div></td>
   </tr>
</table>
   
<p>Time between updates:
   <input id="updateInterval" type="text" value="" style="text-align: right; width:5em">
   milliseconds</p>

<script type="text/javascript">
   $(function () {
      // setup control widget
      var updateInterval = 1000;
      
      $("#updateInterval").val(updateInterval).change(function () {
         var v = $(this).val();
         if (v && !isNaN(+v)) {
            updateInterval = +v;
         if (updateInterval < 500)
            updateInterval = 500;
         if (updateInterval > 20000)
            updateInterval = 20000;
         $(this).val("" + updateInterval);
         }
      });
      
      // setup plot
      var default_options = {
         series: { shadowSize: 0 }, // drawing is faster without shadows
         yaxis: { min: 0 },
         xaxis: { min: 0 }
      };
      
      var percentage_options = {
         series: { shadowSize: 0 }, // drawing is faster without shadows
         yaxis: { min: 0, max: 1.1},
         xaxis: { min: 0 }
      };
      
      var throughput_options = {
         series: { shadowSize: 0 }, // drawing is faster without shadows
         yaxis: { min: 0 },
         xaxis: { min: 0 }
      };      
          
      var commit_latency_options = {
         series: { shadowSize: 0 }, // drawing is faster without shadows
         yaxis: { transform: function (v) { if (v == 0) return 0; return Math.log(v); }, 
                  inverseTransform: function (v) { if (v == 0) return 0; return Math.exp(v);}, 
                  ticks: function logTickGenerator(axis) {
                        var res = [], v = 100;                  
                        do {
                         v = v * 10;
                         res.push(v);                   
                        } while (v < axis.max);
                        
                        return res;
                  }},
         xaxis: { min: 0 }
      };
      
      function updatePlot(div, param, avg, options) {
         $.ajax({
         url: "get-multiple-data.php?param=" + param + "&avg=" + avg + "&folder=" + folder,
         method: 'GET',
         dataType: 'text',
         success: function(text) {                     
            var lines = text.split("\n");            
            var allData = [];
            var dataIdx = 0;
            var dataObj = { data: [], color: dataIdx, label: folderArray[dataIdx++]}            
            for(var i = 0, j = 0; i < lines.length; i++) {
               if (lines[i] == ".") {
                  allData.push(dataObj);               
                  dataObj = { data: [] , color: dataIdx, label: folderArray[dataIdx++]};                  
                  j = 0;
                  continue;
               }
               var keyValue = lines[i].split("|");
               if (keyValue[0] == "" || keyValue[1] == "") continue;
               dataObj.data[j++] = new Array(keyValue[0],keyValue[1]);
            }            
            $.plot($("#" + div), allData, options);
         }
         });
      }
      
      function update() {
         updatePlot("throughput", "Throughput", "false", throughput_options);
         updatePlot("abortRate", "AbortRate", "true", percentage_options);
         updatePlot("cpu", "CPU", "true", percentage_options);
         updatePlot("memory", "Memory.Usage", "true", default_options);
         updatePlot("wrtPer", "PercentageWriteTransactions", "true", percentage_options);
         updatePlot("commitLatency", "CommitLatency", "true", commit_latency_options);
         setTimeout(update, updateInterval);
      }
      
      update();
   });
</script>
</body>
</html>
