<?php
$tableRows = ''; // Variable to store HTML table rows
$rowsperpage = 25; // Number of rows to display per page
// Check if the current page is set in the request
if (isset($_REQUEST['currentpage'])) {
    $currentpage = (int)$_REQUEST['currentpage'];
    $offset = ($currentpage - 1) * $rowsperpage; // Calculate the offset for pagination
} else {
    $currentpage = 1; // Set the current page to 1 if not set
    $offset = (int)($currentpage - 1) * $rowsperpage; // Calculate the offset for pagination
}

$originalSearch = ''; // Variable to store the original search query

// Check if the search query is set in the request
if (isset($_REQUEST['search'])) {
    $originalSearch = $_REQUEST['search'];
    $search = "%" . $originalSearch . "%"; // Creating  wildcard search pattern
    $searchStr = "&search=" . $originalSearch; // Creating the search string for URL parameters
} else {
    $search = '%'; // Set the default search pattern
    $searchStr = ''; // Set the default search string for URL parameters
}

// SQL Prepared statement to count the number of rows matching the search criteria
$numrows_stmt = mysqli_prepare($link, "SELECT COUNT(DISTINCT s.siteAddress) FROM `sites` as s INNER JOIN `site_qr_codes` as qr ON s.site_id = qr.site_id WHERE s.active = 1 AND ((s.client LIKE ?) OR (s.siteAddress LIKE ?) OR (qr.qr_code LIKE ?))");
mysqli_stmt_bind_param($numrows_stmt, 'sss', $search, $search, $search); // Bind the search pattern to the statement
mysqli_stmt_execute($numrows_stmt); // Execute the statement
$numrows_result = mysqli_stmt_get_result($numrows_stmt); // Get the result of the query
$numrows = mysqli_fetch_array($numrows_result)[0]; // Assign the row count to the variable

// Prepare a SQL statement to retrieve the rows of data for the current page
$rows_stmt = mysqli_prepare($link, "SELECT s.*, qr.* FROM `sites` as s INNER JOIN `site_qr_codes` as qr ON s.site_id = qr.site_id WHERE s.active = 1 AND ((s.client LIKE ?) OR (s.siteAddress LIKE ?) OR (qr.qr_code LIKE ?)) GROUP BY s.siteAddress ORDER BY qr.qr_code DESC LIMIT ?, ?");
mysqli_stmt_bind_param($rows_stmt, 'sssii', $search, $search, $search, $offset, $rowsperpage); // Bind the search pattern, offset, and limit to the statement
mysqli_stmt_execute($rows_stmt); // Execute the statement
$rows_result = mysqli_stmt_get_result($rows_stmt); // Get the result of the query

$totalpages = ceil($numrows / $rowsperpage); // Calculate the total number of pages

// Check if there are rows in the result set
if (mysqli_num_rows($rows_result) > 0) {
    // Build the HTML table rows for the result data
    $tableRows = '<thead><tr><th style="border-top-left-radius:5px">QR Code</th><th>Client</th><th style="border-top-right-radius:5px">Address</th></tr></thead>';
    $tableRows .= '<tbody>';
    while ($data = mysqli_fetch_array($rows_result)) {
        $tableRows .= '<tr data-siteid="' . htmlspecialchars($data['site_id']) . '" class="row"><td class="quote_id">' . htmlspecialchars($data['qr_code']) . '</td><td>' . htmlspecialchars($data['client']) . '</td><td>' . htmlspecialchars($data['siteAddress']) . '</td></tr>';
    }
    $tableRows .= "</tbody>";
}
?>

<main>
    <section id="home">
        <!-- Link to add a new site -->
        <a href="dashboard.php?page=addSDC_site" class="home-btns">
            <span class="material-icons orange">add</span>
            <h2>Add Site</h2>
        </a>

        <div style="grid-column: span 3">
            <h2 style="text-align:center;">Search Sites</h2>
            <div class="searchBar">
                <input class="fc" placholder="Search..." value="<?php echo htmlspecialchars($originalSearch); ?>">
                <button type="button" class="search">
                    <span class="material-icons orange">search</span>
                </button>
            </div>
        </div>
        <div style="grid-column: span 3;width:90%;" class="table siteTable" data-template="">
            <table data-role="<?php echo htmlspecialchars($usrRole); ?>" data-option="" cellpadding="10px" cellspacing="0" style="width:100%;">
                <colgroup>
                    <col colspan="1" style="width:12%">
                    <col colspan="1" style="width:30%">
                    <col colspan="1" style="width:58%">
                </colgroup>
                <?php echo $tableRows; ?> <!-- Output the HTML table rows -->
            </table>
            <div class="pagination">
                <?php
                // Pagination links
                if (isset($currentpage) && is_numeric($currentpage)) {
                    $currentpage = (int)$currentpage;
                } else {
                    $currentpage = 1;
                }
                if ($currentpage > $totalpages) {
                    $currentpage = $totalpages;
                }
                if ($currentpage < 1) {
                    $currentpage = 1;
                }
                $range = 5;
                if ($currentpage > 1) {
                    echo " <a href='dashboard.php?page=admin_SDC&currentpage=1" . htmlspecialchars($searchStr) . "'>First</a> ";
                    $prevpage = $currentpage - 1;
                    echo " <a href='dashboard.php?page=admin_SDC&currentpage=" . $prevpage . htmlspecialchars($searchStr) . "'><</a> ";
                }
                for ($x = ($currentpage - $range); $x < (($currentpage + $range) + 1); $x++) {
                    if (($x > 0) && ($x <= $totalpages)) {
                        if ($x == $currentpage) {
                            echo "<span class='p_box'>" . htmlspecialchars($x) . "</span>";
                        } else {
                            echo " <a href='dashboard.php?page=admin_SDC&currentpage=$x" . htmlspecialchars($searchStr) . "'>" . htmlspecialchars($x) . "</a> ";
                        }
                    }
                }
                if ($currentpage != $totalpages) {
                    $nextpage = $currentpage + 1;
                    echo " <a href='dashboard.php?page=admin_SDC&currentpage=$nextpage" . htmlspecialchars($searchStr) . "'>></a> ";
                    echo " <a href='dashboard.php?page=admin_SDC&currentpage=$totalpages" . htmlspecialchars($searchStr) . "'>Last</a> ";
                }
                ?>
            </div>
        </div>
    </section>
</main>
/*The updated code I used  htmlspecialchars() to properly sanitize the user input, thus preventing any potential XSS attacks. Prepared statements are used to prevent SQL injection by binding the user input to the SQL statements. Additionally, the code includes appropriate escaping of special characters in URL parameters to avoid any security issues.*/
