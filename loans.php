<?php
include 'db.php';
// AJAX handler for loan addition
if (isset($_POST['ajax']) && $_POST['ajax'] === 'add_loan') {
    $loan_type = $_POST['loan_type'];
    $amount = (float)$_POST['amount'];
    $interest_rate = (float)$_POST['interest_rate'];
    $status = 'Pending';

    $stmt = $conn->prepare("INSERT INTO loans (Loan_Type, Amount, Interest_Rate, Status) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sdds", $loan_type, $amount, $interest_rate, $status);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Your loan has been added... wait for its approval.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error adding loan. Please try again.']);
    }
    exit;
}

// Loan eligibility criteria
$ELIGIBILITY_RULES = [
    'Personal' => ['min_income' => 25000, 'min_age' => 21, 'max_age' => 60],
    'Home' => ['min_income' => 50000, 'min_age' => 25, 'max_age' => 65],
    'Auto' => ['min_income' => 35000, 'min_age' => 21, 'max_age' => 65],
    'Business' => ['min_income' => 75000, 'min_age' => 25, 'max_age' => 60]
];

$eligibility_result = '';
$add_loan_message = '';

// Check eligibility form submit
if(isset($_POST['check_eligibility'])) {
    $loan_type = $_POST['eligibility_loan_type'];
    $monthly_income = (float)$_POST['monthly_income'];
    $age = (int)$_POST['age'];
    $employment = $_POST['employment_type'];

    $rules = $ELIGIBILITY_RULES[$loan_type];

    $pass = true;
    $reasons = [];

    if($monthly_income < $rules['min_income']) {
        $pass = false;
        $reasons[] = "Minimum income requirement not met (Required: Rs. ".number_format($rules['min_income']).")";
    }

    if($age < $rules['min_age'] || $age > $rules['max_age']) {
        $pass = false;
        $reasons[] = "Age must be between {$rules['min_age']}-{$rules['max_age']} years";
    }

    if($pass) {
        $eligibility_result = '<div class="alert success">
            <i class="fas fa-check-circle"></i> Congratulations! You are eligible for '.$loan_type.' Loan
        </div>';
    } else {
        $eligibility_result = '<div class="alert error">
            <i class="fas fa-times-circle"></i> Not Eligible for '.$loan_type.' Loan:<br>'.implode('<br>', $reasons).'
        </div>';
    }
}

// Add loan form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_loan'])) {
    $loan_type = $_POST['loan_type'];
    $amount = (float)$_POST['amount'];
    $interest_rate = (float)$_POST['interest_rate'];
    $status = 'Pending';

    $stmt = $conn->prepare("INSERT INTO loans (Loan_Type, Amount, Interest_Rate, Status) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sdds", $loan_type, $amount, $interest_rate, $status);
    if($stmt->execute()) {
        $add_loan_message = '<div class="alert success">Your loan has been added... wait for its approval.</div>';
    } else {
        $add_loan_message = '<div class="alert error">Error adding loan. Please try again.</div>';
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM loans WHERE Loan_ID=$id");
}

$loans = $conn->query("SELECT * FROM loans");

$branch_loans = $conn->query("SELECT b.branch_name, SUM(l.Amount) AS total_loan_amount
    FROM branches b
    JOIN loans l ON b.branch_id = l.branch_id
    GROUP BY b.branch_name");

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Loan Management | Banking System</title>
  <style>
    body { font-family: Arial, sans-serif; background:#fff; margin: 0; padding: 0; }
    .container {
      background:url(loan.jpg);
      width: 90%;
      max-width: 1200px;
      margin: 2rem auto;
      padding: 2rem;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(81, 7, 87, 0.94);
    }
    h2, h3 {
      text-align: center;
      margin-bottom: 1rem;
      color:rgb(250, 250, 250);
    }
    .form-group {
      margin-bottom: 1rem;
    }
    label {
      display: block;
      margin-bottom: 0.5rem;
    }
    input, select {
      width: 100%;
      padding: 0.5rem;
      border-radius: 5px;
      border: 1px solid #ccc;
    }
    button {
      background:rgb(160, 0, 204);
      color: white;
      border: none;
      padding: 0.6rem 1.2rem;
      border-radius: 5px;
      cursor: pointer;
      font-size: 1rem;
      margin-top: 1rem;
    }
    button:hover {
      background:rgb(99, 0, 153);
    }
    .alert {
      margin-top: 1rem;
      padding: 1rem;
      border-radius: 5px;
    }
    .success {
      background-color:#d4edda;
      color:#155724;
    }
    .error {
      background-color: #f8d7da;
      color: #721c24;
    }
    .card {
      margin-top: 2rem;
      padding: 1rem;
      background:rgb(205, 179, 223);
      border: 1px solid #ddd;
      border-radius: 10px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 1rem;
    }
    th, td {
      border: 1px solid #ccc;
      padding: 0.5rem;
      text-align: center;
    }
    .toggle-btn {
      margin-bottom: 1rem;
      background:rgb(45, 5, 81);
    }
    .toggle-btn:hover {
      background:rgb(61, 5, 107);
    }
    #eligibilitySection, #loansTableSection, #branchLoanSection {
      display: none;
    }
    .button-group {
      display: flex;
      justify-content: center;
      gap: 1rem;
      margin-bottom: 1.5rem;
      flex-wrap: wrap;
    }
    @media (max-width: 768px) {
      .button-group {
        flex-direction: column;
      }
      table {
        font-size: 0.9rem;
      }
    }
  </style>
</head>
<body>

<div class="container">
  <h2>Loan Management System</h2>

  <div class="button-group">
    <button class="toggle-btn" onclick="toggleSection('branchLoanSection')">Show Loan Amounts by Branch</button>
    <button class="toggle-btn" onclick="toggleSection('eligibilitySection')">Check Loan Eligibility</button>
    <button class="toggle-btn" onclick="toggleSection('loansTableSection')">Show Current Loans</button>
  </div>

  <div id="branchLoanSection" class="card">
    <h3>Total Loan Amount per Branch</h3>
    <?php if ($branch_loans && $branch_loans->num_rows > 0): ?>
      <table>
        <thead>
          <tr>
            <th>Branch Name</th>
            <th>Total Loan Amount (Rs.)</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $branch_loans->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($row['branch_name']) ?></td>
              <td><?= number_format($row['total_loan_amount'], 2) ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p>No loan data found for branches.</p>
    <?php endif; ?>
  </div>

  <div id="eligibilitySection" class="card">
    <div><?= $add_loan_message ?></div>

    <h3>Check Loan Eligibility</h3>
    <div id="loanMessageBox"></div>

   <form method="post" id="loanForm">
      <div class="form-group">
        <label>Loan Type:</label>
        <select name="eligibility_loan_type" required>
          <option value="Personal">Personal Loan</option>
          <option value="Home">Home Loan</option>
          <option value="Auto">Auto Loan</option>
          <option value="Business">Business Loan</option>
        </select>
      </div>
      <div class="form-group">
        <label>Monthly Income:</label>
        <input type="number" name="monthly_income" required>
      </div>
      <div class="form-group">
        <label>Age:</label>
        <input type="number" name="age" required>
      </div>
      <div class="form-group">
        <label>Employment Type:</label>
        <select name="employment_type" required>
          <option value="Salaried">Salaried</option>
          <option value="Self-employed">Self-employed</option>
        </select>
      </div>
      <button type="submit" name="check_eligibility">Check Eligibility</button>
    </form>

    <div class="eligibility-result"><?= $eligibility_result ?></div>

    <?php if (!empty($eligibility_result) && strpos($eligibility_result, 'Congratulations') !== false): ?>
      <div class="card" style="margin-top: 2rem;">
        <h3>Add New Loan</h3>
        <form method="post">
          <div class="form-group">
            <label>Loan Type:</label>
            <select name="loan_type" required>
              <option value="Personal">Personal Loan</option>
              <option value="Home">Home Loan</option>
              <option value="Auto">Auto Loan</option>
              <option value="Business">Business Loan</option>
            </select>
          </div>
          <div class="form-group">
            <label>Amount:</label>
            <input type="number" name="amount" id="loanAmount" step="0.01" required>
          </div>
          <div class="form-group">
            <label>Interest Rate (%):</label>
            <select name="interest_rate" id="interestRate" onchange="updateEstimate()" required>
              <option value="7.5">7.5%</option>
              <option value="8.0">8.0%</option>
              <option value="8.5">8.5%</option>
              <option value="9.0">9.0%</option>
              <option value="9.5">9.5%</option>
              <option value="10.0">10.0%</option>
            </select>
          </div>

          <p><strong>Estimated Repayment (1 year):</strong> Rs. <span id="repayment">0.00</span></p>

          <button type="submit" name="add_loan">Add Loan</button>
        </form>
        <?= $add_loan_message ?>
      </div>
    <?php endif; ?>
  </div>

  <div id="loansTableSection" class="card">
    <h3>Current Loans</h3>
    <?php if ($loans && $loans->num_rows > 0): ?>
      <table>
        <thead>
          <tr>
            <th>Loan ID</th>
            <th>Type</th>
            <th>Amount</th>
            <th>Interest (%)</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php while($loan = $loans->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($loan['loan_id']) ?></td>
              <td><?= htmlspecialchars($loan['Loan_Type']) ?></td>
              <td><?= number_format($loan['Amount'], 2) ?></td>
              <td><?= htmlspecialchars($loan['Interest_Rate']) ?></td>
              <td><?= htmlspecialchars($loan['Status']) ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p>No loans found.</p>
    <?php endif; ?>
  </div>
</div>

<script>
  function toggleSection(id) {
    const el = document.getElementById(id);
    ['branchLoanSection','eligibilitySection','loansTableSection'].forEach(function(sectionId) {
      document.getElementById(sectionId).style.display = 'none';
    });
    el.style.display = 'block';
    el.scrollIntoView({ behavior: 'smooth' });
  }

  function updateEstimate() {
    const amount = parseFloat(document.getElementById("loanAmount").value || 0);
    const rate = parseFloat(document.getElementById("interestRate").value || 0);
    const repayment = amount + (amount * rate / 100);
    document.getElementById("repayment").textContent = repayment.toFixed(2);
  }

  document.getElementById("loanAmount")?.addEventListener("input", updateEstimate);
  document.getElementById("loanForm")?.addEventListener("submit", function(e) {
  e.preventDefault();

  const loan_type = this.loan_type.value;
  const amount = this.amount.value;
  const interest_rate = this.interest_rate.value;

  fetch("", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded"
    },
    body: new URLSearchParams({
      ajax: 'add_loan',
      loan_type,
      amount,
      interest_rate
    })
  })
  .then(res => res.json())
  .then(data => {
    const box = document.getElementById("loanMessageBox");
    box.innerHTML = `<div class="alert ${data.status}">${data.message}</div>`;
    box.scrollIntoView({ behavior: 'smooth' });

    if(data.status === 'success') {
      document.getElementById("loanForm").reset();
      document.getElementById("repayment").textContent = '0.00';
    }
  })
  .catch(() => {
    document.getElementById("loanMessageBox").innerHTML = '<div class="alert error">Unexpected error occurred.</div>';
  });
});

</script>

</body>
</html>
