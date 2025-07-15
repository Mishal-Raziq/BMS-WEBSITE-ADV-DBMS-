<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.html");
    exit();
}

$input = $_POST['name'];
$password = $_POST['password'];

// Fetch user by email or username
$sql = "SELECT * FROM customers WHERE Email = ? OR Name = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $input, $input);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    
    // Verify password
    if (password_verify($password, $user['Password'])) {
        // Store user data in session
        $_SESSION['customer_id'] = $user['Customer_ID'];
        $_SESSION['name'] = $user['Name'];
        $last_login = $user['Last_Login'];
// Update last_login timestamp to current time
$updateLogin = $conn->prepare("UPDATE customers SET Last_Login = NOW() WHERE Customer_ID = ?");
$updateLogin->bind_param("i", $customer_id);
$updateLogin->execute();
        
        // Fetch account information with customer details
        $customer_id = $user['Customer_ID'];
        $sql2 = "SELECT a.Account_No, a.Account_Type, a.Balance, 
                        c.Address, c.Phone, c.Registration_Date
                 FROM accounts a
                 JOIN customers c ON a.Customer_ID = c.Customer_ID
                 WHERE a.Customer_ID = ?";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param("i", $customer_id);
        $stmt2->execute();
        $accounts = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
        
        if ($accounts) {
            // Display the account information table directly
            ?>
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <title>Account Information</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                    th { background-color:rgb(96, 3, 108); color: white; }
                    tr:nth-child(even) { background-color: #f2f2f2; }
                    h2 { color:rgb(77, 0, 128); }
                    .logout { color: white; background: #d9534f; padding: 8px 15px; text-decoration: none; border-radius: 4px; float: right; }
                    .logout:hover { background: #c9302c; }
                </style>
            </head>
            <body>
                <a href="index.html" class="logout">Logout</a>
                
                <h2>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></h2>
<p><strong>Last Login:</strong> <?php echo htmlspecialchars($last_login ? $last_login : 'First time login'); ?></p>

                <h3>Your Account Information</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Account No</th>
                            <th>Account Type</th>
                            <th>Balance</th>
                            <th>Phone</th>
                            <th>Registration Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($accounts as $account): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($account['Account_No']); ?></td>
                            <td><?php echo htmlspecialchars($account['Account_Type']); ?></td>
                            <td>$<?php echo number_format($account['Balance'], 2); ?></td>
                            <td><?php echo htmlspecialchars($account['Phone']); ?></td>
                            <td><?php echo htmlspecialchars($account['Registration_Date']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p><strong>Address:</strong> <?php echo htmlspecialchars($account['Address']); ?></p>
            </body>
            </html>
            <?php
            exit();
        } else {
            header("Location: login.html?error=No+accounts+found");
            exit();
        }
    }
}

header("Location: login.html?error=Invalid+username/email+or+password");
exit();
?>
