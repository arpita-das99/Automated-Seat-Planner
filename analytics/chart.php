<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports and Analytics</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<?php include('../includes/navbar.php'); ?>
<div class="main-content">
    <div class="container">
    <div>
        <h2>Seat Allocation</h2>
        <canvas id="seatAllocationChart"></canvas>
    </div>
    <div>
        <h2>Student Distribution</h2>
        <canvas id="studentDistributionChart"></canvas>
    </div>
  
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Function to fetch data from the PHP script via AJAX
            function fetchData(url) {
                return fetch(url)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .catch(error => {
                        console.error('Error fetching data:', error);
                    });
            }

            // Function to render seat allocation chart
            function renderSeatAllocationChart(data) {
                const seatAllocationCtx = document.getElementById('seatAllocationChart').getContext('2d');
                new Chart(seatAllocationCtx, {
                    type: 'bar',
                    data: {
                        labels: data.seat_allocation.map(item => `Room ${item.room}, Row ${item.row}, Col ${item.col}`),
                        datasets: [{
                            label: 'Seat Allocation',
                            data: data.seat_allocation.map(item => item.name),
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }

            // Function to render student distribution chart
            function renderStudentDistributionChart(data) {
                const studentDistributionCtx = document.getElementById('studentDistributionChart').getContext('2d');
                new Chart(studentDistributionCtx, {
                    type: 'pie',
                    data: {
                        labels: data.student_distribution.map(item => item.batch),
                        datasets: [{
                            label: 'Student Distribution',
                            data: data.student_distribution.map(item => item.student_count),
                            backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0'],
                            hoverOffset: 4
                        }]
                    }
                });
            }

            // Fetch data for seat allocation and render chart
            fetchData('dashboard.php')
                .then(data => {
                    renderSeatAllocationChart(data);
                    renderStudentDistributionChart(data);
                })
                .catch(error => {
                    console.error('Error fetching or rendering data:', error);
                });
        });
    </script>
    </div>
</div>
</body>
</html>
