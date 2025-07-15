<?php
include 'db.php'; // assumes a proper db connection

// Query 1: Top 5 customers by balance
$topAccounts = $conn->query("
  SELECT c.Customer_ID, c.Name, a.Balance
  FROM customers c
  JOIN accounts a ON c.Customer_ID = a.Customer_ID
  ORDER BY a.Balance DESC
  LIMIT 5
");

// Query 2: Stats (total, sum, average)
$statsResult = $conn->query("
  SELECT 
    COUNT(*) AS total_accounts,
    SUM(balance) AS total_balance,
    AVG(balance) AS avg_balance
  FROM accounts
");
$stats = $statsResult->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Accounts | Banking System</title>
  <style>
    body {
      background: url(accounts.jpg);
      font-family: Arial, sans-serif;
      background-size: cover;
      margin: 0;
      padding: 0;
    }

    .container {
      max-width: 700px;
      margin: 3rem auto;
      border: 2px solid #9c27b0;
      background: #ffffff;
      padding: 2rem;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    h2 {
      text-align: center;
      margin-bottom: 1.5rem;
    }

    .toggle-btn {
      background: #480063;
      color: white;
      padding: 0.6rem 1.5rem;
      font-size: 1rem;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      display: block;
      margin: 0 auto 1rem;
    }

    .toggle-btn:hover {
      background: #75009c;
    }

    .card {
      background: #e5d4f1;
      padding: 1.5rem;
      border-radius: 10px;
      margin-top: 1rem;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 1rem;
    }

    th, td {
      padding: 0.75rem;
      border: 1px solid #ccc;
      text-align: center;
    }

    th {
      background-color: #c8aee0;
    }

    #topAccountsSection,
    #accountStatsSection {
      display: none;
    }
  </style>
</head>
<body>

<div class="container">
  <h2>Account Overview for Admin</h2>

  <button class="toggle-btn" onclick="toggleSection('topAccountsSection')">Show Top Balances</button>
  <button class="toggle-btn" onclick="toggleSection('accountStatsSection')">Show Account Stats</button>

  <!-- Top 5 Balances Section -->
  <div id="topAccountsSection" class="card">
    <h3>Top 5 Customers by Balance</h3>
    <?php if ($topAccounts && $topAccounts->num_rows > 0): ?>
      <table>
        <thead>
          <tr>
            <th>Customer ID</th>
            <th>Name</th>
            <th>Balance (Rs.)</th>
          </tr>
        </thead>
        <tbody>
          <?php while($row = $topAccounts->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($row['Customer_ID']) ?></td>
              <td><?= htmlspecialchars($row['Name']) ?></td>
              <td><?= number_format($row['Balance'], 2) ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p>No accounts found.</p>
    <?php endif; ?>
  </div>

  <!-- Account Stats Section -->
  <div id="accountStatsSection" class="card">
    <h3>Account Statistics</h3>
    <table>
      <tr>
        <th>Total Accounts</th>
        <td><?= $stats['total_accounts'] ?></td>
      </tr>
      <tr>
        <th>Total Balance (Rs.)</th>
        <td><?= number_format($stats['total_balance'], 2) ?></td>
      </tr>
      <tr>
        <th>Average Balance (Rs.)</th>
        <td><?= number_format($stats['avg_balance'], 2) ?></td>
      </tr>
    </table>
  </div>
</div>

<script>
  function toggleSection(id) {
    const el = document.getElementById(id);
    el.style.display = el.style.display === 'block' ? 'none' : 'block';
    if (el.style.display === 'block') el.scrollIntoView({ behavior: 'smooth' });
  }
</script>

</body>
</html>
