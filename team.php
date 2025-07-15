<?php
session_start();
require 'db.php';

// Fetch employees data
$sql = "SELECT Name, Position FROM employees ORDER BY Position, Name";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Meet Our Team</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f9f9f9;
      margin: 0; padding: 0;
    }
    .team-container {
      max-width: 800px;
      border: 2px solid #9c27b0;
      margin: 3rem auto;
      background: #fff;
      padding: 2rem;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    h2 {
      text-align: center;
      margin-bottom: 2rem;
      color: #333;
    }
    table {
      width: 100%;
      border-collapse: collapse;
    }
    th, td {
      padding: 0.75rem 1rem;
      border-bottom: 1px solid #ccc;
      text-align: left;
    }
    th {
      background-color: #8a56ac;
      color: white;
    }
    .back-link {
      display: inline-block;
      margin-bottom: 1rem;
      color: #5e2e91;
      text-decoration: none;
    }
    .back-link:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

<div class="team-container">
  <a class="back-link" href="index.html">&larr; Back to Home</a>
  <h2>Meet Our Team</h2>

  <?php
  if ($result && $result->num_rows > 0) {
      $positions = [];

      while ($row = $result->fetch_assoc()) {
          $position = $row['Position'];
          $name = htmlspecialchars($row['Name']);

          if (!isset($positions[$position])) {
              $positions[$position] = [];
          }
          $positions[$position][] = $name;
      }

      echo "<table>
              <tr>
                <th>Position</th>
                <th>Names</th>
              </tr>";

      foreach ($positions as $position => $names) {
          echo "<tr>
                  <td><strong>" . htmlspecialchars($position) . "</strong></td>
                  <td>" . implode(", ", $names) . "</td>
                </tr>";
      }

      echo "</table>";
  } else {
      echo "<p>No employees found.</p>";
  }
  $conn->close();
  ?>

</div>

</body>
</html>
