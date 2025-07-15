<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Explore Our Branches</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: url(branches.jpg);
            font-family: Arial, sans-serif;
            background-size: cover;
            margin: 0; padding: 0;
        }
        .branches-section, .profitable-branch-section {
            max-width: 900px;
            margin: 40px auto;
            padding: 30px;
            background-color: rgb(251, 250, 252);
            border: 2px solid #9c27b0;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(156, 39, 176, 0.2);
            text-align: center;
        }
        .branch-form {
            margin-top: 20px;
            margin-bottom: 30px;
        }
        .branch-form input[type="text"] {
            padding: 10px;
            width: 60%;
            max-width: 300px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        .branch-form button {
            padding: 10px 20px;
            background-color: #9c27b0;
            color: #fff;
            border: none;
            border-radius: 6px;
            margin-left: 10px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .branch-form button:hover {
            background-color: #7b1fa2;
        }
        .table-container {
            overflow-x: auto;
        }
        table.styled-table {
            border-collapse: collapse;
            margin: 0 auto;
            font-size: 16px;
            width: 100%;
            max-width: 900px;
        }
        table.styled-table thead tr {
            background-color: #9c27b0;
            color: #ffffff;
            text-align: left;
        }
        table.styled-table th, table.styled-table td {
            padding: 12px 15px;
            border: 1px solid #ddd;
        }
        table.styled-table tbody tr:nth-child(even) {
            background-color: #f3e5f5;
        }
        .no-results {
            color: #9c27b0;
            font-weight: bold;
            margin-top: 20px;
        }
        /* Styling for the Most Profitable Branch box content */
        .profitable-branch-details {
            font-size: 18px;
            color: #4a148c;
            line-height: 1.6;
            margin-top: 15px;
        }
    </style>
</head>
<body>

<div class="branches-section">
    <h2 class="section-title">Explore Our Branches</h2>

    <form method="post" action="" class="branch-form">
        <label for="province">Enter Province Name:</label>
        <input type="text" id="province" name="province" placeholder="e.g. Punjab" required />
        <button type="submit">Search</button>
    </form>

    <?php
    include 'db.php';  // DB connection

    $province = isset($_POST['province']) ? trim($_POST['province']) : '';
    $branches = [];

    if ($province != '') {
        $stmt = $conn->prepare("SELECT Branch_ID as branch_id, Branch_Name as branch_name, 
                                Location as location, Manager as Manager FROM branches 
                                WHERE Location LIKE ?");
        $searchTerm = "%$province%";
        $stmt->bind_param("s", $searchTerm);
        $stmt->execute();

        $result = $stmt->get_result();
        $branches = $result->fetch_all(MYSQLI_ASSOC);
    }
    ?>

    <?php if ($province): ?>
        <h3 class="section-subtitle">Branches in "<?php echo htmlspecialchars($province); ?>"</h3>

        <?php if (count($branches) > 0): ?>
            <div class="table-container">
                <table class="styled-table">
                    <thead>
                        <tr>
                            <th>Branch ID</th>
                            <th>Branch Name</th>
                            <th>Location</th>
                            <th>Manager</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($branches as $branch): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($branch['branch_id']); ?></td>
                            <td><?php echo htmlspecialchars($branch['branch_name']); ?></td>
                            <td><?php echo htmlspecialchars($branch['location']); ?></td>
                            <td><?php echo htmlspecialchars($branch['Manager']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="no-results">No branches found in "<?php echo htmlspecialchars($province); ?>"</p>
            <p class="no-results">NOTE: THE PROVINCE YOU ENTERED "<?php echo htmlspecialchars($province); ?>" IS NOT VALID</p>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php
// Query for Most Profitable Branch (highest sum of approved loans)
$mostProfitableBranch = null;

$query = "
    SELECT b.Branch_ID, b.Branch_Name, SUM(l.Amount) AS total_loans_issued
    FROM Loans l
    JOIN Branches b ON l.Branch_ID = b.Branch_ID
    WHERE l.Status = 'Approved'
    GROUP BY b.Branch_ID, b.Branch_Name
    ORDER BY total_loans_issued DESC
    LIMIT 1
";

$result = $conn->query($query);
if ($result && $result->num_rows > 0) {
    $mostProfitableBranch = $result->fetch_assoc();
}
?>

<?php if ($mostProfitableBranch): ?>
<div class="profitable-branch-section">
    <h2 class="section-title">Most Profitable Branch</h2>
    <div class="profitable-branch-details">
        <p><strong>Branch ID:</strong> <?php echo htmlspecialchars($mostProfitableBranch['Branch_ID']); ?></p>
        <p><strong>Branch Name:</strong> <?php echo htmlspecialchars($mostProfitableBranch['Branch_Name']); ?></p>
        <p><strong>Total Approved Loans Issued:</strong> ₹<?php echo number_format($mostProfitableBranch['total_loans_issued'], 2); ?></p>
    </div>
</div>
<?php else: ?>
<div class="profitable-branch-section">
    <h2 class="section-title">Most Profitable Branch</h2>
    <p class="no-results">No approved loans found to calculate the most profitable branch.</p>
</div>
<?php endif; ?>

</body>
</html>
