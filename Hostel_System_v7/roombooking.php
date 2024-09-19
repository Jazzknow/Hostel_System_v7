<?php
include 'php/connection.php';
?>

<!DOCTYPE html>
<html lang="en">
<?php include 'includes/head.php'; ?>

<body>
    <?php include 'includes/sidebar.php'; ?>
    <div id="main-content">
        <?php include 'includes/header.php'; ?>

        <main>
            <div class="four-box-container">
                <h1>Room Booking Information</h1>
                <div class="breadcrumb">
                    <a href="#">Information about the Guest</a>
                    <i class='bx bx-chevron-right'></i>
                    <a class="active" href="#">Room Booking</a>
                    <a href="facilitybooking.php" id="refreshStats" class="btn btn-refresh">Facility Booking</a>
                </div>
            </div>

            <div class="box-info-container">
                <div class="header-container" style="width: 100%; ">
                    <h4>Room Information</h4>
                    <div class="search-bar">
                        <input type="text" id="searchInput" placeholder="Search..." onkeyup="searchTable()">
                    </div>
                </div>
                <div class="horizontal-box" style="width: 100%; margin-top: -15px;">
                    <table id="bookingTable">
                        <thead>
                            <tr>
                                <th>Facility ID <span class="sort-icon" onclick="sortTable(0)">&#x25B2;&#x25BC;</span>
                                </th>
                                <th>Facility Name</th>
                                <th>Name</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Guest</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT room_id, roomnumber, fullname, checkin, checkout, Totalguest, status FROM room_reservation";
                            $result = $conn->query($sql);
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['room_id']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['roomnumber']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['fullname']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['checkin']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['checkout']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['Totalguest']) . "</td>";
                                    echo "<td style='color:grey;'><strong>" . htmlspecialchars($row['status']) . "</strong></td>";
                                    echo "<td>
                                                <a href='roombooking.php?id=" . htmlspecialchars($row['room_id']) . "'>View</a> |
                                                <a href='roombooking.php?id=" . htmlspecialchars($row['room_id']) . "'>Edit</a> |
                                                <a href='roombooking.php?id=" . htmlspecialchars($row['room_id']) . "' onclick='return confirm(\"Are you sure you want to delete this booking?\")'>Delete</a>
                                            </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='8'>No bookings found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                    <div id="noResults">No results found</div>
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.js"
        integrity="sha512-8Z5++K1rB3U+USaLKG6oO8uWWBhdYsM3hmdirnOEWp8h2B1aOikj5zBzlXs8QOrvY9OxEnD2QDkbSKKpfqcIWw=="
        crossorigin="anonymous"></script>
    <script src="assets/js/experiment.js"></script>
    <script src="assets/js/roombooking.js"></script>
    <script>
        function searchTable() {
            var input, filter, table, tr, tdName, tdRoom, i, txtValueName, txtValueRoom;
            input = document.getElementById("searchInput");
            filter = input.value.toUpperCase();
            table = document.getElementById("bookingTable");
            tr = table.getElementsByTagName("tr");
            var noResults = document.getElementById("noResults");
            var resultsFound = false;

            for (i = 1; i < tr.length; i++) { // Start from 1 to skip the header row
                tdRoom = tr[i].getElementsByTagName("td")[1];
                tdName = tr[i].getElementsByTagName("td")[2];
                if (tdRoom && tdName) {
                    txtValueRoom = tdRoom.textContent || tdRoom.innerText;
                    txtValueName = tdName.textContent || tdName.innerText;
                    if (txtValueRoom.toUpperCase().indexOf(filter) > -1 || txtValueName.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                        resultsFound = true;
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }

            if (resultsFound) {
                noResults.style.display = "none";
                table.style.display = "";
            } else {
                noResults.style.display = "block";
                table.style.display = "none";
            }
        }

        let sortColumn = 1;
        let sortDirection = 1;

        function sortTable(columnIndex) {
            const table = document.getElementById("bookingTable");
            const tbody = table.getElementsByTagName("tbody")[0];
            const rows = Array.from(tbody.getElementsByTagName("tr"));

            if (sortColumn === columnIndex) {
                sortDirection *= -1;
            } else {
                sortDirection = 1;
            }
            sortColumn = columnIndex;

            rows.sort((a, b) => {
                const aColText = a.getElementsByTagName("td")[columnIndex].textContent.trim();
                const bColText = b.getElementsByTagName("td")[columnIndex].textContent.trim();

                if (columnIndex === 0) {
                    return sortDirection * (parseInt(aColText) - parseInt(bColText));
                } else {
                    return sortDirection * aColText.localeCompare(bColText);
                }
            });

            rows.forEach(row => tbody.appendChild(row));

            updateSortIcons(columnIndex);
        }

        function updateSortIcons(activeColumn) {
            const sortIcons = document.querySelectorAll(".sort-icon");
            sortIcons.forEach((icon, index) => {
                if (index === activeColumn) {
                    icon.textContent = sortDirection === 1 ? "\u25B2" : "\u25BC";
                } else {
                    icon.textContent = "\u25B2\u25BC";
                }
            });
        }

        // Initial sort on page load
        sortTable(1); // Sort by Facility Name column (index 1)

    </script>
</body>

</html>