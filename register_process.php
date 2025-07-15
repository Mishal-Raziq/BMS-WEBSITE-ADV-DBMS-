<?php
session_start();
require 'db.php';

// Collect and sanitize input
$name = mysqli_real_escape_string($conn, $_POST['name']);
$email = mysqli_real_escape_string($conn, $_POST['email']);
$phone = mysqli_real_escape_string($conn, $_POST['phone']);
$address = mysqli_real_escape_string($conn, $_POST['address']);
$password = $_POST['password'];

// Validate inputs
$errors = [];

if (empty($name)) $errors[] = "Name is required";
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
if (strlen($password) < 8) $errors[] = "Password must be at least 8 characters";

// Check if email exists
$check_email = "SELECT Email FROM customers WHERE Email='$email'";
$result = $conn->query($check_email);

if ($result->num_rows > 0) {
    $errors[] = "Email already registered";
}

// Process registration if no errors
if (empty($errors)) {
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Insert customer
        $sql = "INSERT INTO customers (Name, Email, Phone, Address, Password, Registration_Date, Branch_ID) 
                VALUES ('$name', '$email', '$phone', '$address', '$hashed_password', NOW(), 1)";
        
        if ($conn->query($sql)) {
            $customer_id = $conn->insert_id;
            
            // Generate account number
            $account_no = 'ACC' . mt_rand(100000, 999999);
            
            // Create account automatically
            $account_sql = "INSERT INTO accounts (Account_No, Customer_ID, Account_Type, Balance, Branch_ID)
                            VALUES ('$account_no', '$customer_id', 'Savings', 0.00, 1)";
            
            if ($conn->query($account_sql)) {
                // Commit transaction if both queries succeed
                $conn->commit();
                
                // Set session variables
                $_SESSION['customer_id'] = $customer_id;
                $_SESSION['name'] = $name;
                $_SESSION['account_no'] = $account_no;
                
                header("Location: dashboard.php");
                exit();
            } else {
                throw new Exception("Account creation failed: " . $conn->error);
            }
        } else {
            throw new Exception("Registration failed: " . $conn->error);
        }
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $errors[] = $e->getMessage();
    }
}

// If errors occurred
$_SESSION['errors'] = $errors;
header("Location: register.html");
exit();
?>