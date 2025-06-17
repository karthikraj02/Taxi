// === config/db.php ===
<?php
$conn = new mysqli("localhost", "root", "", "taxi_service");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>


// === register.php ===
<?php
include("config/db.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $name = $_POST['name'];
  $email = $_POST['email'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

  $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
  $stmt->bind_param("sss", $name, $email, $password);
  $stmt->execute();

  echo "✅ Registered! <a href='login.php'>Login Now</a>";
}
?>
<form method="POST">
  <h2>Register</h2>
  <input type="text" name="name" placeholder="Name" required><br>
  <input type="email" name="email" placeholder="Email" required><br>
  <input type="password" name="password" placeholder="Password" required><br>
  <button type="submit">Register</button>
</form>


// === login.php ===
<?php
session_start();
include("config/db.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $email = $_POST['email'];
  $password = $_POST['password'];

  $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($row = $result->fetch_assoc()) {
    if (password_verify($password, $row['password'])) {
      $_SESSION['user_id'] = $row['id'];
      $_SESSION['name'] = $row['name'];
      $_SESSION['role'] = $row['role'];

      if ($row['role'] === 'admin') {
        header("Location: admin-dashboard.php");
      } else {
        header("Location: dashboard.php");
      }
      exit;
    } else {
      echo "❌ Incorrect password!";
    }
  } else {
    echo "❌ User not found!";
  }
}
?>
<form method="POST">
  <h2>Login</h2>
  <input type="email" name="email" placeholder="Email" required><br>
  <input type="password" name="password" placeholder="Password" required><br>
  <button type="submit">Login</button>
</form>


// === dashboard.php ===
<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
  header("Location: login.php");
  exit;
}
echo "<h2>Welcome, " . $_SESSION['name'] . "!</h2>";
echo "<a href='book-ride.php'>🚖 Book a Ride</a><br>";
echo "<a href='my-bookings.php'>📋 My Bookings</a><br>";
echo "<a href='logout.php'>Logout</a>";
?>


// === book-ride.php ===
<?php
session_start();
include("config/db.php");

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $pickup = $_POST['pickup'];
  $drop = $_POST['drop'];
  $ride_time = $_POST['ride_time'];
  $user_id = $_SESSION['user_id'];

  $stmt = $conn->prepare("INSERT INTO bookings (user_id, pickup, dropoff, ride_time) VALUES (?, ?, ?, ?)");
  $stmt->bind_param("isss", $user_id, $pickup, $drop, $ride_time);
  $stmt->execute();

  echo "<p style='color:green;'>✅ Ride booked successfully!</p>";
}
?>
<h2>Book a Ride</h2>
<form method="POST">
  <input type="text" name="pickup" placeholder="Pickup Location" required><br>
  <input type="text" name="drop" placeholder="Drop Location" required><br>
  <input type="datetime-local" name="ride_time" required><br>
  <button type="submit">Confirm Booking</button>
</form>
<a href="dashboard.php">Back to Dashboard</a>


// === my-bookings.php ===
<?php
session_start();
include("config/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
  header("Location: login.php");
  exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT id, pickup, dropoff, ride_time, status FROM bookings WHERE user_id = ? ORDER BY ride_time DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<h2>🚖 My Bookings</h2>
<table border="1" cellpadding="10">
  <tr>
    <th>Pickup</th>
    <th>Dropoff</th>
    <th>Date & Time</th>
    <th>Status</th>
    <th>Invoice</th>
  </tr>
  <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
      <td><?= htmlspecialchars($row['pickup']) ?></td>
      <td><?= htmlspecialchars($row['dropoff']) ?></td>
      <td><?= date('d M Y, h:i A', strtotime($row['ride_time'])) ?></td>
      <td><?= htmlspecialchars($row['status']) ?></td>
      <td><a href="invoice.php?id=<?= $row['id'] ?>">🧾 View Invoice</a></td>
    </tr>
  <?php endwhile; ?>
</table>
<a href="dashboard.php">⬅ Back to Dashboard</a>


// === invoice.php ===
<?php
session_start();
include("config/db.php");

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
  die("Unauthorized access or missing ID");
}

$booking_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM bookings WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
?>
<div style="max-width:600px;margin:auto;padding:20px;border:1px solid #ccc;">
  <h2 style="text-align:center;">Taxi Ride Invoice</h2>
  <hr>
  <p><strong>Name:</strong> <?= $_SESSION['name'] ?></p>
  <p><strong>Booking ID:</strong> <?= $row['id'] ?></p>
  <p><strong>Pickup:</strong> <?= $row['pickup'] ?></p>
  <p><strong>Dropoff:</strong> <?= $row['dropoff'] ?></p>
  <p><strong>Date & Time:</strong> <?= date('d M Y, h:i A', strtotime($row['ride_time'])) ?></p>
  <p><strong>Status:</strong> <?= $row['status'] ?></p>
  <p><strong>Fare:</strong> ₹<?= number_format($row['fare'], 2) ?></p>
  <hr>
  <button onclick="window.print()">🖨️ Print Invoice</button>
  <a href="my-bookings.php">⬅ Back to My Bookings</a>
</div>
<?php
} else {
  echo "❌ No booking found.";
}
?>


// === logout.php ===
<?php
session_start();
session_destroy();
header("Location: login.php");
?>
