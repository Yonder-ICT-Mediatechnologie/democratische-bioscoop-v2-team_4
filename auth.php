<?php
// Start or resume the current session for user authentication
session_start();

// Database connection configuration
// These credentials are used to connect to the MySQL database
$host = "localhost";
$dbname = "project_bioscoop";
$username = "root";
$password = "";

// Initialize variables to store error and success messages
$error = "";
$success = "";
// Determine the current mode (login or register) from the URL parameter
// Defaults to 'login' if no mode is specified
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'login';

// Check if the form was submitted using POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Establish database connection using PDO (PHP Data Objects)
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        // Set error mode to throw exceptions for better error handling
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Check if this is a registration request (determined by hidden field)
        if (isset($_POST["register"])) {
            // Handle user registration
            // Retrieve submitted form values
            $naam = $_POST["naam"];
            $email = $_POST["email"];
            $wachtwoord = $_POST["wachtwoord"];

            // Validate that all required fields are filled in
            if (empty($naam) || empty($email) || empty($wachtwoord)) {
                $error = "Alle velden zijn verplicht";
                // Keep the form in register mode to show the registration form again
                $mode = 'register';
            } else {
                // Check if the email address is already registered in the database
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
                $stmt->execute([':email' => $email]);

                // If email already exists, show error and stay in register mode
                if ($stmt->rowCount() > 0) {
                    $error = "Email bestaat al";
                    $mode = 'register';
                } else {
                    // Create new user account
                    // Hash the password using a secure one-way hashing algorithm
                    $hashed = password_hash($wachtwoord, PASSWORD_DEFAULT);
                    // Prepare and execute INSERT query with parameterized values to prevent SQL injection
                    $stmt = $pdo->prepare("INSERT INTO users (naam, wachtwoord, email) VALUES (:naam, :wachtwoord, :email)");
                    $stmt->execute([':naam' => $naam, ':wachtwoord' => $hashed, ':email' => $email]);

                    // Registration successful - show success message and switch to login mode
                    $success = "Registratie succesvol! Log in.";
                    $mode = 'login';
                }
            }
        } else {
            // Handle user login (when register field is not set)
            // Retrieve submitted login credentials
            $naam = $_POST["naam"];
            $wachtwoord = $_POST["wachtwoord"];

            // Validate that both username and password fields are filled
            if (empty($naam) || empty($wachtwoord)) {
                $error = "Alle velden zijn verplicht";
            } else {
                // Look up the user in the database by username
                $stmt = $pdo->prepare("SELECT id, naam, wachtwoord FROM users WHERE naam = :naam");
                $stmt->execute([':naam' => $naam]);

                // Check if a user with this username exists
                if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    // Verify the submitted password against the hashed password in the database
                    if (password_verify($wachtwoord, $user["wachtwoord"])) {
                        // Password is correct - create session variables to log the user in
                        $_SESSION["user_id"] = $user["id"];
                        $_SESSION["user_naam"] = $user["naam"];
                        // Redirect to the home page after successful login
                        header("Location: Index.html");
                        exit();
                    } else {
                        // Password is incorrect
                        $error = "Ongeldige inloggegevens";
                    }
                } else {
                    // Username not found in database
                    $error = "Ongeldige inloggegevens";
                }
            }
        }
    } catch (PDOException $e) {
        // Catch any database connection or query errors
        $error = "Database fout: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>

<body>
    <!-- Display page title based on current mode (Login or Register) -->
    <h1><?php echo $mode == 'login' ? 'Login' : 'Registreer'; ?></h1>

    <?php if ($error): ?>
        <!-- Display error message in red if one exists -->
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
        <!-- Display success message in green if one exists -->
        <p style="color: green;"><?php echo $success; ?></p>
    <?php endif; ?>

    <?php if ($mode == 'login'): ?>
        <!-- Login form - submitted to login.php via POST -->
        <form method="POST" action="auth.php">
            <!-- Username input field (required) -->
            <input type="text" name="naam" placeholder="naam" required><br>
            <!-- Password input field (required) -->
            <input type="password" name="wachtwoord" placeholder="Wachtwoord" required><br>
            <button type="submit">Inloggen</button>
        </form>
        <!-- Link to switch to registration mode -->
        <p><a href="auth.php?mode=register">Geen account? Registreer hier</a></p>
    <?php else: ?>
        <!-- Registration form - submitted to login.php via POST -->
        <form method="POST" action="auth.php">
            <!-- Username input field (required) -->
            <input type="text" name="naam" placeholder="Naam" required><br>
            <!-- Email input field with validation (required) -->
            <input type="email" name="email" placeholder="Email" required><br>
            <!-- Password input field (required) -->
            <input type="password" name="wachtwoord" placeholder="Wachtwoord" required><br>
            <!-- Hidden field to identify this as a registration request -->
            <input type="hidden" name="register" value="1">
            <button type="submit">Registreren</button>
        </form>
        <!-- Link to switch back to login mode -->
        <p><a href="auth.php?mode=login">Al een account? Log in</a></p>
    <?php endif; ?>
</body>

</html>