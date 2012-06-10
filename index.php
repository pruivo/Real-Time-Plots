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

		if (isset($_REQUEST['app'])) {
			$folder = $_REQUEST['app'];
		}   	
   	
   	echo '<script type="text/javascript">';
   	echo 'var folder = "' . $folder . '";';
   	echo '</script>';
   ?>
</head>
<body>
<h1>Workload Monitor - Real Time plots</h1>
<table style="border:1px solid black;">
   <tr><td>Throughput (transaction per second)</td><td>Abort Rate (percentage)</td></tr>
   <tr><td><div id="throughput" style="width:800px;height:300px"></div></td><td><div id="abortRate" style="width:800px;height:300px"></div></td></tr>
   
   <tr><td>Cpu Usage (percentage)</td><td></td></tr>
   <tr><td><div id="cpu" style="width:800px;height:300px"></div></td><td></td></tr>
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
      var options = {
         series: { shadowSize: 0 }, // drawing is faster without shadows
      };
   
      function updatePlot(div, param, avg) {
         $.ajax({
         url: "get-data.php?param=" + param + "&avg=" + avg + "&folder=" + folder,
         method: 'GET',
         dataType: 'text',
         success: function(text) {
            var lines = text.split("\n");
            var data = [];
            for(var i = 0, j = 0; i < lines.length; i++) {
               var keyValue = lines[i].split("|");
               if (keyValue[0] == "" || keyValue[1] == "") continue;
               data[j++] = new Array(keyValue[0],keyValue[1]);
            }
            $.plot($("#" + div), [ data ], options);
         }
         });
      }
   
      function update() {
         updatePlot("throughput", "Throughput", "false");
         updatePlot("abortRate", "AbortRate", "true");
         updatePlot("cpu", "CPU", "true");
         setTimeout(update, updateInterval);
      }
   
      update();
   });
</script>
</body>
</html>
