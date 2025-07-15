<?php
session_start();
include 'db.php';

// If user wants to view dashboard and provides an account number
if (isset($_GET['view']) && $_GET['view'] === 'dashboard') {

    // Get account_no from query param (or show form to enter it)
    $account_no = $_GET['account_no'] ?? '';

    if (empty($account_no)) {
        // Show a simple form to enter Account Number for dashboard
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
          <meta charset="UTF-8" />
          <title>Dashboard - Enter Account Number</title>
          <style>
            body { font-family: Arial, sans-serif; background:#f4f4f4; margin:0; padding:0;}
            header { background:#004080; color:#fff; padding:10px 20px; text-align:center;}
            nav a { color:#fff; margin:0 10px; text-decoration:none;}
            .container { max-width: 400px; background:#fff; margin:30px auto; padding:20px; border-radius:5px; box-shadow:0 0 5px rgba(0,0,0,0.3);}
            label, input, button { display:block; width: 100%; margin-top: 10px; }
            button { background:#004080; color:#fff; border:none; padding:10px; cursor:pointer;}
            button:hover { background:#003060;}
          </style>
        </head>
        <body>
          <header>
            <h1>Dashboard</h1>
            <nav>
              <a href="transactions.php">Make a Transaction</a>
              <a href="transactions.php?view=dashboard">Dashboard</a>
              <a href="index.html">Home</a>
            </nav>
          </header>
          <div class="container">
            <form action="transactions.php" method="GET">
              <input type="hidden" name="view" value="dashboard" />
              <label for="account_no">Enter Account Number:</label>
              <input type="text" id="account_no" name="account_no" required />
              <button type="submit">Show Account Summary</button>
            </form>
          </div>
        </body>
        </html>
        <?php
        exit();
    }

    // Now fetch account info by this account_no
    $stmt = $conn->prepare("SELECT Account_No, Account_Type, Balance FROM accounts WHERE Account_No = ?");
    $stmt->bind_param("s", $account_no);
    $stmt->execute();
    $account = $stmt->get_result()->fetch_assoc();

    if (!$account) {
        die("Account not found.");
    }

    // Fetch last 5 transactions for this account
    $stmt = $conn->prepare("SELECT Transaction_Type, Amount, Description, Transaction_Date FROM transactions WHERE Account_No = ? ORDER BY Transaction_Date DESC LIMIT 5");
    $stmt->bind_param("s", $account_no);
    $stmt->execute();
    $transactions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="UTF-8" />
      <title>Dashboard - Account Summary</title>
      <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin:0; padding:0; }
        header { background:#004080; color:white; padding:10px 20px; text-align:center; }
        nav a { color:white; margin:0 10px; text-decoration:none; }
        .container { max-width:800px; background:white; margin:30px auto; padding:20px; border-radius:5px; box-shadow:0 0 5px rgba(0,0,0,0.3);}
        h2 {text-align:center;}
        table {width:100%; border-collapse:collapse; margin-top:20px;}
        th, td {padding:12px; border-bottom:1px solid #ddd; text-align:left;}
        th {background:#004080; color:white;}
        tr:hover {background:#f1f1f1;}
      </style>
    </head>
    <body>
    <header>
      <h1>Dashboard</h1>
      <nav>
        <a href="transactions.php">Make a Transaction</a>
        <a href="transactions.php?view=dashboard">Dashboard</a>
        <a href="index.html">Home</a>
      </nav>
    </header>
    <div class="container">
      <h2>Account Summary for Account No: <?php echo htmlspecialchars($account['Account_No']); ?></h2>

      <p><strong>Account Type:</strong> <?php echo htmlspecialchars($account['Account_Type']); ?></p>
      <p><strong>Balance:</strong> $<?php echo number_format($account['Balance'], 2); ?></p>

      <h3>Last 5 Transactions</h3>

      <?php if (count($transactions) === 0): ?>
        <p>No transactions found.</p>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>Date</th>
              <th>Type</th>
              <th>Amount</th>
              <th>Description</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($transactions as $txn): ?>
            <tr>
              <td><?php echo htmlspecialchars($txn['Transaction_Date']); ?></td>
              <td><?php echo htmlspecialchars($txn['Transaction_Type']); ?></td>
              <td>$<?php echo number_format($txn['Amount'], 2); ?></td>
              <td><?php echo htmlspecialchars($txn['Description']); ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
    </body>
    </html>
    <?php
    exit();
}

// Otherwise, your original POST transaction handling code here:

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transaction_type = $_POST['transaction_type'];
    $source_account = $_POST['source_account'];
    $destination_account = $_POST['destination_account'] ?? null;
    $amount = floatval($_POST['amount']);
    $description = $_POST['description'];

    // Validate amount
    if ($amount <= 0) {
        die("Amount must be greater than zero.");
    }

    // Get source account balance
    $stmt = $conn->prepare("SELECT Balance FROM accounts WHERE Account_No = ?");
    $stmt->bind_param("s", $source_account);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows !== 1) {
        die("Source account not found.");
    }

    $source_balance = $result->fetch_assoc()['Balance'];

    if ($transaction_type === 'Withdraw') {
        if ($source_balance < $amount) {
            die("Insufficient balance.");
        }

        // Update source account
        $stmt = $conn->prepare("UPDATE accounts SET Balance = Balance - ? WHERE Account_No = ?");
        $stmt->bind_param("ds", $amount, $source_account);
        $stmt->execute();

        // Log transaction
        $stmt = $conn->prepare("INSERT INTO transactions (Account_No, Transaction_Type, Amount, Description, Transaction_Date) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssds", $source_account, $transaction_type, $amount, $description);
        $stmt->execute();

        echo "Withdrawal successful.";

    } elseif ($transaction_type === 'Deposit') {
        if (empty($destination_account)) {
            die("Destination account required.");
        }

        // Update destination account
        $stmt = $conn->prepare("UPDATE accounts SET Balance = Balance + ? WHERE Account_No = ?");
        $stmt->bind_param("ds", $amount, $destination_account);
        $stmt->execute();

        // Log transaction
        $stmt = $conn->prepare("INSERT INTO transactions (Account_No, Transaction_Type, Amount, Description, Transaction_Date) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssds", $destination_account, $transaction_type, $amount, $description);
        $stmt->execute();

        echo "Deposit successful.";

    } elseif ($transaction_type === 'Transfer') {
        if (empty($destination_account)) {
            die("Destination account required.");
        }
        if ($source_account == $destination_account) {
            die("Cannot transfer to the same account.");
        }
        if ($source_balance < $amount) {
            die("Insufficient balance.");
        }

        $conn->begin_transaction();

        try {
            // Deduct from source
            $stmt = $conn->prepare("UPDATE accounts SET Balance = Balance - ? WHERE Account_No = ?");
            $stmt->bind_param("ds", $amount, $source_account);
            $stmt->execute();

            // Add to destination
            $stmt = $conn->prepare("UPDATE accounts SET Balance = Balance + ? WHERE Account_No = ?");
            $stmt->bind_param("ds", $amount, $destination_account);
            $stmt->execute();

            // Record both transactions
            $desc_withdraw = $description . " (Transfer Out)";
            $desc_deposit = $description . " (Transfer In)";

            $stmt = $conn->prepare("INSERT INTO transactions (Account_No, Transaction_Type, Amount, Description, Transaction_Date) VALUES (?, 'Withdraw', ?, ?, NOW())");
            $stmt->bind_param("sds", $source_account, $amount, $desc_withdraw);
            $stmt->execute();

            $stmt = $conn->prepare("INSERT INTO transactions (Account_No, Transaction_Type, Amount, Description, Transaction_Date) VALUES (?, 'Deposit', ?, ?, NOW())");
            $stmt->bind_param("sds", $destination_account, $amount, $desc_deposit);
            $stmt->execute();

            $conn->commit();
            echo "Transfer successful.";

        } catch (Exception $e) {
            $conn->rollback();
            die("Transfer failed: " . $e->getMessage());
        }

    } else {
        die("Invalid transaction type.");
    }

    exit();
}