<?php
include 'php/connection.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <!-- ICONS -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.2/main.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.2/main.min.js'></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- STYLESHEET -->
    <link rel="stylesheet" href="assets/css/sidebarr.css" />
    <link rel="stylesheet" href="assets/css/style.css" />
    <link rel="stylesheet" href="assets/css/style2.css" />

    <title>Sidebar</title>

</head>

<body>
    <?php include 'includes/sidebar.php'; ?>
    <div id="main-content">
        <?php include 'includes/header.php'; ?>

        <main>
            <div class="four-box-container">
                <h1>Dashboard</h1>
                <div class="breadcrumb">
                    <a href="#">Dashboard</a>
                    <i class='bx bx-chevron-right'></i>
                    <a class="active" href="#">Home</a>
                </div>
            </div>

            <div class="box-info-container">
                <div class="box-info">
                    <i class='bx bxs-calendar-check'></i>
                    <span class="text">
                        <?php
                        $today_date = date('Y-m-d');

                        $totalGuestsQuery1 = "SELECT SUM(Totalguest) as total_guests FROM room_reservation WHERE status = 'Reserved' AND checkin = '$today_date'";
                        $totalGuestsResult1 = $conn->query($totalGuestsQuery1);
                        $totalGuestsRow1 = $totalGuestsResult1->fetch_assoc();
                        $totalGuests1 = $totalGuestsRow1['total_guests'] ?? 0;

                        $totalGuestsQuery2 = "SELECT SUM(numguest) as total_guests FROM facility_reservation WHERE status = 'Reserved' AND checkin = '$today_date'";
                        $totalGuestsResult2 = $conn->query($totalGuestsQuery2);
                        $totalGuestsRow2 = $totalGuestsResult2->fetch_assoc();
                        $totalGuests2 = $totalGuestsRow2['total_guests'] ?? 0;

                        $totalGuests = $totalGuests1 + $totalGuests2;
                        ?>
                        <h3><?php echo $totalGuests; ?></h3>
                        <p>Check-in</p>
                    </span>
                </div>

                <div class="box-info">
                    <i class='bx bxs-group'></i>
                    <span class="text">
                        <?php
                        $today_date = date('Y-m-d');
                        $occupied_room_query = "SELECT * FROM room_reservation WHERE status = 'Reserved' AND checkin = '$today_date'";
                        $result = mysqli_query($conn, $occupied_room_query);

                        if ($result) {
                            $occupied_rooms_today = mysqli_num_rows($result);
                        } else {
                            $occupied_rooms_today = 0;
                        }
                        ?>
                        <h3><?php echo htmlspecialchars($occupied_rooms_today); ?></h3>
                        <p>Occupied Rooms</p>
                    </span>
                </div>
                <div class="box-info">
                    <i class='bx bxs-dollar-circle'></i>
                    <span class="text">
                        <?php
                        $today_date = date('Y-m-d');
                        $occupied_facility_query = "SELECT * FROM facility_reservation WHERE status = 'Reserved' AND checkin = '$today_date'";
                        $result2 = mysqli_query($conn, $occupied_facility_query);

                        if ($result2) {
                            $occupied_facility_today = mysqli_num_rows($result2);
                        } else {
                            $occupied_facility_today = 0;
                        }
                        ?>
                        <h3><?php echo htmlspecialchars($occupied_facility_today); ?></h3>
                        <p>Occupied facilities</p>
                    </span>
                </div>
                <div class="box-info">
                    <i class='bx bxs-dollar-circle'></i>
                    <span class="text">
                        <h3>?</h3>
                        <p>?</p>
                    </span>
                </div>
            </div>


            <div class="box-info-container">
                <div class="box-info">
                    <div class="room_statistics">
                        <div class="head">
                            <h4>Room Statistics</h4>
                        </div>
                        <canvas id="roomStatsChart"></canvas>
                        <?php
                        $today = date('Y-m-d');
                        $weekAgo = date('Y-m-d', strtotime('-6 days'));

                        $roomStatsQuery = "SELECT 
                            DATE(checkin) as date,
                            DAYNAME(checkin) as day_of_week,
                            COUNT(*) as occupied_rooms,
                            SUM(Totalguest) as total_guests
                        FROM room_reservation
                        WHERE checkin BETWEEN '$weekAgo' AND '$today'
                        AND status = 'Reserved'
                        GROUP BY DATE(checkin)
                        ORDER BY DATE(checkin)";

                        $roomStatsResult = $conn->query($roomStatsQuery);
                        $daysOfWeek = [];
                        $occupiedRooms = [];
                        $totalGuests = [];

                        while ($row = $roomStatsResult->fetch_assoc()) {
                            $daysOfWeek[] = $row['day_of_week'];
                            $occupiedRooms[] = $row['occupied_rooms'];
                            $totalGuests[] = $row['total_guests'];
                        }
                        ?>
                    </div>
                </div>


                <div class="box-info">
                    <div class="calendar">
                        <div class="head">
                            <h4>Calendar</h4>
                        </div>
                        <div id="calendar"></div>
                        <?php
                        $checkInDatesQuery = "SELECT DISTINCT checkin FROM (
                            SELECT checkin FROM room_reservation WHERE status = 'Reserved'
                            UNION
                            SELECT checkin FROM facility_reservation WHERE status = 'Reserved'
                        ) AS combined_checkins";
                        $checkInDatesResult = $conn->query($checkInDatesQuery);
                        $checkInDates = [];
                        while ($row = $checkInDatesResult->fetch_assoc()) {
                            $checkInDates[] = $row['checkin'];
                        }
                        ?>
                        <script>
                            const checkInDates = <?php echo json_encode($checkInDates); ?>;
                        </script>
                    </div>
                </div>
            </div>

            <div class="box-info-container">
                <div class="box-info">
                    <div class="facility_statistics">
                        <div class="head">
                            <h4>Facility Statistics</h4>
                        </div>
                        <canvas id="facilityStatsChart"></canvas>
                        <?php
                        $today = date('Y-m-d');
                        $weekAgo = date('Y-m-d', strtotime('-6 days'));

                        $facilityStatsQuery = "SELECT 
                            DATE(checkin) as date,
                            DAYNAME(checkin) as day_of_week,
                            COUNT(*) as reserved_facilities,
                            SUM(numguest) as total_guests
                        FROM facility_reservation
                        WHERE checkin BETWEEN '$weekAgo' AND '$today'
                        AND status = 'Reserved'
                        GROUP BY DATE(checkin)
                        ORDER BY DATE(checkin)";

                        $facilityStatsResult = $conn->query($facilityStatsQuery);
                        $facilityDaysOfWeek = [];
                        $reservedFacilities = [];
                        $facilityTotalGuests = [];

                        while ($row = $facilityStatsResult->fetch_assoc()) {
                            $facilityDaysOfWeek[] = $row['day_of_week'];
                            $reservedFacilities[] = $row['reserved_facilities'];
                            $facilityTotalGuests[] = $row['total_guests'];
                        }
                        ?>
                    </div>
                </div>

                <div class="box-info">
                    <div class="Summary">
                        <div class="head">
                            <h4>Summary</h4>
                        </div>
                        <!-- Content for Todos -->
                    </div>
                </div>
            </div>

        </main>
        <!-- MAIN -->
        </section>
        <!-- CONTENT -->


        <script src="assets/js/roombooking.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const calendar = document.getElementById('calendar');
                const currentDate = new Date();
                const currentMonth = currentDate.getMonth();
                const currentYear = currentDate.getFullYear();

                function generateCalendar(month, year) {
                    const firstDay = new Date(year, month, 1);
                    const lastDay = new Date(year, month + 1, 0);
                    const daysInMonth = lastDay.getDate();
                    const startingDay = firstDay.getDay();

                    let calendarHTML = `
                <div class="calendar-header">
                    <button onclick="changeMonth(-1)">&lt;</button>
                    <h3>${new Date(year, month).toLocaleString('default', { month: 'long' })} ${year}</h3>
                    <button onclick="changeMonth(1)">&gt;</button>
                </div>
                <table>
                    <tr>
                        <th>Sun</th>
                        <th>Mon</th>
                        <th>Tue</th>
                        <th>Wed</th>
                        <th>Thu</th>
                        <th>Fri</th>
                        <th>Sat</th>
                    </tr>
                    <tr>
            `;

                    let day = 1;
                    for (let i = 0; i < 6; i++) {
                        for (let j = 0; j < 7; j++) {
                            if (i === 0 && j < startingDay) {
                                calendarHTML += '<td></td>';
                            } else if (day > daysInMonth) {
                                break;
                            } else {
                                const date = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                                const hasCheckIn = checkInDates.includes(date) ? 'has-check-in' : '';
                                calendarHTML += `<td class="${hasCheckIn}">${day}</td>`;
                                day++;
                            }
                        }
                        if (day > daysInMonth) {
                            break;
                        }
                        calendarHTML += '</tr><tr>';
                    }

                    calendarHTML += '</tr></table>';
                    calendar.innerHTML = calendarHTML;
                }

                generateCalendar(currentMonth, currentYear);

                window.changeMonth = function (delta) {
                    currentDate.setMonth(currentDate.getMonth() + delta);
                    generateCalendar(currentDate.getMonth(), currentDate.getFullYear());
                };
            });
        </script>

        <!----Start of room statistic----->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // ... existing calendar code ...

                // Room Statistics Chart
                const ctx = document.getElementById('roomStatsChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: <?php echo json_encode($daysOfWeek); ?>,
                    datasets: [{
                        label: 'Occupied Rooms',
                        data: <?php echo json_encode($occupiedRooms); ?>,
                        backgroundColor: 'rgba(75, 192, 192, 0.6)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                }, {
                    label: 'Total Check-ins',
                    data: <?php echo json_encode($totalGuests); ?>,
                    backgroundColor: 'rgba(255, 159, 64, 0.6)',
                    borderColor: 'rgba(255, 159, 64, 1)',
                    borderWidth: 1
                }]
            },
                options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Count'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Day of Week'
                        }
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Room Reservations - Past Week'
                    }
                }
            }
        });
    });
        </script>
        <!------End of room statistic----->
        <!----Start of facility statistic----->
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // ... existing calendar and room statistics code ...

                // Facility Statistics Chart
                const facilityCtx = document.getElementById('facilityStatsChart').getContext('2d');
                new Chart(facilityCtx, {
                    type: 'bar',
                    data: {
                        labels: <?php echo json_encode($facilityDaysOfWeek); ?>,
                    datasets: [{
                        label: 'Reserved Facilities',
                        data: <?php echo json_encode($reservedFacilities); ?>,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }, {
                    label: 'Total Guests',
                    data: <?php echo json_encode($facilityTotalGuests); ?>,
                    backgroundColor: 'rgba(255, 99, 132, 0.6)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                    }]
                },
                options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Count'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Day of Week'
                        }
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Facility Reservations - Past Week'
                    }
                }
            }
            });
        });
        </script>
        <!----End of facility statistic----->


        <style>
            .calendar {
                display: flex;
                flex-direction: column;
                height: 100%;
                width: 100%;
            }

            .head {
                margin-bottom: 10px;
            }

            #calendar {
                font-family: Arial, sans-serif;
                flex-grow: 1;
                display: flex;
                flex-direction: column;
            }

            .calendar-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 10px;
            }

            .calendar-header button {
                background: none;
                border: none;
                font-size: 18px;
                cursor: pointer;
                padding: 5px 10px;
            }

            .calendar-header h3 {
                margin: 0;
                white-space: nowrap;
            }

            table {
                width: 100%;
                height: 100%;
                border-collapse: collapse;
                flex-grow: 1;
            }

            th,
            td {
                text-align: center;
                padding: 10px 5px;
                border: 1px solid #ddd;
            }

            th {
                background-color: #f2f2f2;
            }

            .has-check-in {
                background-color: #ffeb3b;
                font-weight: bold;
            }

            @media (max-width: 600px) {

                th,
                td {
                    padding: 5px 2px;
                    font-size: 0.9em;
                }
            }

            /***Room/facility statistic */
            .room_statistics,
            .facility_statistics {
                width: 100%;
                height: 300px;
                /* Adjust as needed */
            }

            #roomStatsChart,
            #facilityStatsChart {
                width: 100%;
                height: 100%;
            }
        </style>

</body>

</html>