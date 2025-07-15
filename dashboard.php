<?php
session_start();

// Debugging - output current session
error_log("Dashboard session: " . print_r($_SESSION, true));

if (!isset($_SESSION['customer_id'])) {
    // Debugging - no customer_id in session
    error_log("No customer_id in session, redirecting to login");
    header("Location: login.html");
    exit();
}

// Fetch customer data from database if not in session
include 'db.php';
if (!isset($_SESSION['email']) || !isset($_SESSION['phone']) || !isset($_SESSION['address'])) {
    $customer_id = $_SESSION['customer_id'];
    $query = "SELECT Email, Phone, Address FROM customers WHERE Customer_ID = '$customer_id'";
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        $customer_data = $result->fetch_assoc();
        $_SESSION['email'] = $customer_data['Email'];
        $_SESSION['phone'] = $customer_data['Phone'];
        $_SESSION['address'] = $customer_data['Address'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Account Dashboard - Banking System</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    body {
      background-color: #f5f7fa;
      color: #333;
    }
    
    header {
      background: linear-gradient(135deg,rgb(126, 30, 153) 0%,rgb(157, 32, 202) 100%);
      color: white;
      padding: 1.5rem;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    
    .container {
      max-width: 1200px;
      margin: 2rem auto;
      padding: 0 1rem;
    }
    
    .welcome-section {
      background: white;
      padding: 2rem;
      border-radius: 8px;
      box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
      margin-bottom: 2rem;
    }
    
    .welcome-section h1 {
      color:rgb(137, 30, 153);
      margin-bottom: 1rem;
    }
    
    .customer-info {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      gap: 1rem;
      margin-top: 1.5rem;
    }
    
    .info-card {
      background: #f9f9f9;
      padding: 1rem;
      border-radius: 6px;
      border-left: 4px solid #1e5799;
    }
    
    .info-card h3 {
      color: #555;
      font-size: 0.9rem;
      margin-bottom: 0.5rem;
    }
    
    .info-card p {
      font-size: 1.1rem;
      font-weight: 500;
    }
    
    .logout-btn {
      display: inline-block;
      background: #e74c3c;
      color: white;
      padding: 0.6rem 1.2rem;
      border-radius: 4px;
      text-decoration: none;
      font-weight: 600;
      margin-top: 1rem;
      transition: background 0.3s ease;
    }
    
    .logout-btn:hover {
      background: #c0392b;
    }
  </style>
</head>
<body>
  <header>
    <div class="container">
      <h1>SecureBank</h1>
    </div>
  </header>
  
  <div class="container">
    <section class="welcome-section">
      <h1>Welcome, <?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?>!</h1>
      <p>Here's your account information.</p>
      
      <div class="customer-info">
        <div class="info-card">
          <h3>Customer ID</h3>
          <p><?php echo htmlspecialchars($_SESSION['customer_id'] ?? 'N/A'); ?></p>
        </div>
        <div class="info-card">
          <h3>Email Address</h3>
          <p><?php echo htmlspecialchars($_SESSION['email'] ?? 'Not available'); ?></p>
        </div>
        <div class="info-card">
          <h3>Phone Number</h3>
          <p><?php echo htmlspecialchars($_SESSION['phone'] ?? 'Not available'); ?></p>
        </div>
        <div class="info-card">
          <h3>Address</h3>
          <p><?php echo htmlspecialchars($_SESSION['address'] ?? 'Not available'); ?></p>
        </div>
      </div>
    </section>
    
    <a href="index.html" class="logout-btn">Logout</a>
  </div>
</body>
</html>