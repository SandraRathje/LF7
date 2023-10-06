<?php
include('config.php');

// Holen Sie sich den aktuellsten Statusaenderung-Wert aus der Tabelle „Warnungen“.
$warnungenQuery = "SELECT statusaenderung FROM Warnungen ORDER BY id DESC LIMIT 1";
$warnungenResult = mysqli_query($conn, $warnungenQuery);

if (!$warnungenResult) {
   die("Warnungen query failed: " . mysqli_error($conn));
}

// Initialisieren Sie eine Variable, um den abgerufenen „statusaenderung“-Wert zu speichern
$statusaenderung = '';

if ($row = mysqli_fetch_assoc($warnungenResult)) {
   $statusaenderung = $row['statusaenderung'];
}

// Rufen Sie die letzten 15 Zeilen aus der Tabelle „durchschnitt“ ab
$durchschnittQuery = "SELECT timestamp, pegel, id FROM durchschnitt ORDER BY id DESC LIMIT 15";
$durchschnittResult = mysqli_query($conn, $durchschnittQuery);

if (!$durchschnittResult) {
   die("Durchschnitt query failed: " . mysqli_error($conn));
}

// Erstellen Sie ein Array zum Speichern der abgerufenen „Durchschnitt“-Daten
$durchschnittData = array();

while ($row = mysqli_fetch_assoc($durchschnittResult)) {
   $durchschnittData[] = array(
      'id' => $row['id'],
      'timestamp' => $row['timestamp'],
      'pegel' => (int)$row['pegel'],
   );
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="de">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="icon" href="https://lh4.googleusercontent.com/8_7nCrN_TUGVtCE5xfl66xLBKgt3HLOKND5eETjIBeGUKv5cgL-G6CN3ZU_VIaI1po687QEJEufs83tLk-ydhnaBba8A7fCRb50u582YdIu1mKOl"><meta property="og:title" content="DJ Mahir Nation TV">
   <meta name="author" content="DJMahirNationTV">
   <meta name="description" content="Lernfeld 7 mit Lukas, Sandra und Mahir">
   <title>Warnmelder</title>
   <link rel="stylesheet" href="style.css">
   <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
</head>

<body>
   <section>
      <header id="header">
         <h1>
            Warnmeldung
         </h1>
         <div class="links">
            <a href="#beforeHeader">Tabelle</a>
            <a href="#diagrammChart">Diagramm</a>
         </div>
      </header>
      <hr id="beforeHeader">
      <table id="tabled">
         <tr>
            <th>ID</th>
            <th>WarnStatus</th>
            <th>Timestamp</th>
         </tr>
         <?php foreach ($durchschnittData as $row) : ?>
            <tr>
               <td><?php echo $row['id']; ?></td>
               <td><?php echo $row['timestamp']; ?></td>
               <td><?php echo $row['pegel']; ?></td>
            </tr>
         <?php endforeach; ?>
      </table>
      <hr>
      <!--<button id="button" onclick="alarm()" type="button">Play Warning</button>-->
   </section>
   <audio id="warningSound">
      <source src="sound.mp3" type="audio/mpeg">
   </audio>
   <?php if ($statusaenderung === 'AUS') : ?>
      <section class="showOk">
         <h2>Aktuelle Warnungen:</h2>
         <span>OK!</span>
         <p id="allokP">Alles in Ordnung.</p>
      </section>
   <?php elseif ($statusaenderung === 'AN') : ?>
      <section class="showWarning">
         <h2>Aktuelle Warnungen:</h2>
         <span>WARNUNG!</span>
         <p id="warnedP">Warnung! Das Wasser zu hoch</p>
      </section>
      <script>
         const warningSound = document.getElementById('warningSound');
         warningSound.play();
      </script>
   <?php endif; ?>

   <section class="chartsWarn">
      <h2>Wasserstand Diagram:</h2>
      <canvas id="diagrammChart"></canvas>
   </section>
   <script>
      var ctx = document.getElementById('diagrammChart').getContext('2d');
      const DISPLAY = true;
      const BORDER = true;
      const CHART_AREA = true;
      const TICKS = true;
      var myChart;

      function createChart(xValues, difValue) {
         if (myChart) {
            myChart.destroy(); // Zerstören Sie das vorherige Diagramm, um Duplikate zu vermeiden
         }

         myChart = new Chart(ctx, {
            type: 'line',
            data: {
               labels: xValues,
               datasets: [{
                  label: 'Wasserstand',
                  data: difValue,
                  backgroundColor: 'rgba(75, 192, 192, 0.2)',
                  borderColor: 'rgba(75, 192, 192, 1)',
                  borderWidth: 1,
                  lineTension: 0.4,
               }],
            },
            options: {
               responsive: true,
               animation: false,
               scales: {
                  x: {
                     reverse: true,
                     ticks: {
                        padding: 10,
                     },
                     border: {
                        display: BORDER,
                     },
                     grid: {
                        display: DISPLAY,
                        drawOnChartArea: CHART_AREA,
                        drawTicks: TICKS,
                     },
                  },
               },
            },
         });
      }

      // Funktion zum Aktualisieren des Diagramms mit neuen Daten
      function updateChart() {
         // Rufen Sie die neuesten Daten vom Server ab
         const newData = <?php echo json_encode($durchschnittData); ?>;

         // Extrahieren und formatieren Sie die Zeitstempelwerte als x-Achsenbeschriftungen
         const xValues = newData.map(item => {
            // Teilen Sie den Zeitstempel auf, um den Zeitteil zu erhalten (z. B. „12:41:00“).
            const timePart = item.timestamp.split(' ')[1];
            // Extrahieren Sie die Stunde und Minute
            const [hour, minute] = timePart.split(':');
            return `${hour}:${minute}`;
         });

         //xValues.reverse(); // Spielt die x-axis rückwerts

         // Extrahieren Sie die „Pegel“-Werte
         const difValue = newData.map(item => item.pegel);

         createChart(xValues, difValue); // Erstellen oder aktualisieren Sie das Diagramm mit neuen Daten
      }

      // Erster Anruf zur Aktualisierung des Diagramms
      updateChart();

      // Legen Sie das Intervall fest, um das Diagramm und die Daten jede Minute zu aktualisieren (60.000 Millisekunden).
      setInterval(updateChart, 60000);
      setTimeout(function () {
         window.location.reload();
      }, 2000); // 2000 ms = 2 sekunden
   </script>
</body>

</html>