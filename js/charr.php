<script>
         // Pie Chart
         var studentsWithoutViolation = <?php echo $students_without_violation; ?>;
         var studentsWithViolation = <?php echo $students_with_violation; ?>;
         
         var ctx = document.getElementById('myPieChart').getContext('2d');
         var myPieChart = new Chart(ctx, {
             type: 'pie',
             data: {
                 labels: ['No Violations', 'Violations'],
                 datasets: [{
                     data: [studentsWithoutViolation, studentsWithViolation],
                     backgroundColor: ['#4e73df', '#1cc88a'],  // Blue for no violations, green for violations
                     hoverBackgroundColor: ['#2e59d9', '#17a673'],
                     hoverBorderColor: "rgba(234, 236, 244, 1)",
                 }],
             },
             options: {
                 maintainAspectRatio: false,
                 tooltips: {
                     backgroundColor: "rgb(255,255,255)",
                     bodyFontColor: "#858796",
                     borderColor: '#dddfeb',
                     borderWidth: 1,
                     xPadding: 15,
                     yPadding: 15,
                     displayColors: false,
                     caretPadding: 10,
                 },
                 legend: {
                     display: false
                 },
                 cutoutPercentage: 80,
             },
         });
         
         // Area Chart (for violations per day)
         document.addEventListener('DOMContentLoaded', function () {
             var days = <?php echo json_encode($days); ?>;
             var violationsCount = <?php echo json_encode($violations_count); ?>;
         
             var ctxArea = document.getElementById('myAreaChart').getContext('2d');
             var myAreaChart = new Chart(ctxArea, {
                 type: 'line',
                 data: {
                     labels: days,
                     datasets: [{
                         //label: "Violations",
                         lineTension: 0.3,
                         backgroundColor: "rgba(78, 115, 223, 0.05)",
                         borderColor: "rgba(78, 115, 223, 1)",
                         pointRadius: 3,
                         pointBackgroundColor: "rgb(78, 0, 255)",
                         pointBorderColor: "rgba(78, 115, 223, 1)",
                         pointHoverRadius: 3,
                         pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
                         pointHoverBorderColor: "rgba(78, 115, 223, 1)",
                         pointHitRadius: 10,
                         pointBorderWidth: 2,
                         data: violationsCount,
                     }],
                 },
                 options: {
                     maintainAspectRatio: false,
                     layout: {
                         padding: {
                             left: 10,
                             right: 25,
                             top: 25,
                             bottom: 0
                         }
                     },
                     scales: {
                         xAxes: [{
                             time: {
                                 unit: 'day'
                             },
                             gridLines: {
                                 display: false,
                                 drawBorder: false
                             },
                             ticks: {
                                 maxTicksLimit: 10
                             }
                         }],
                         yAxes: [{
                             ticks: {
                                 maxTicksLimit: 5,
                                 padding: 10
                             },
                             gridLines: {
                                 color: "rgb(234, 236, 244)",
                                 zeroLineColor: "rgb(234, 236, 244)",
                                 drawBorder: false,
                                 borderDash: [2],
                                 zeroLineBorderDash: [2]
                             }
                         }]
                     }
                 }
             });
         });
      </script>