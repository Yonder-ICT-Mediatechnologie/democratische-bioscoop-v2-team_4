<?php
// Start or resume the current session to access session variables
session_start();

// Security check: Verify that the user is logged in
// If no user_id exists in the session, redirect to the login page
if (!isset($_SESSION["user_id"])) {
    header("Location: auth.php");
    exit();
}

// Handle logout request
if (isset($_GET['logout'])) {
    // Destroy the session to log the user out
    session_destroy();
    // Redirect to login page
    header("Location: auth.php");
    exit();
}

// Database connection configuration
// These credentials are used to connect to the MySQL database
$host = "localhost";
$dbname = "project_bioscoop";
$username = "root";
$password = "";

// Initialize variables to store error and success messages
$error = "";
$success = "";

// Check if the form was submitted using POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Establish database connection using PDO (PHP Data Objects)
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        // Set error mode to throw exceptions for better error handling
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Handle account deletion requests requests
        if (isset($_POST["delete_account"])) {
            // get the user's naam to delete associated tasks
            $stmt = $pdo->prepare("SELECT naam FROM users WHERE id = :id");
            $stmt->execute([':id' => $_SESSION["user_id"]]);
            $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user_data) {
                // Delete all tasks associated with this user from the "taken" table
                // where naam_user matches the user's naam
                // $stmt = $pdo->prepare("DELETE FROM taken WHERE naam_user = :naam_user");
                // $stmt->execute([':naam_user' => $user_data['naam']]);
            }

            // delete the user account
            // Prepare a parameterized query to prevent SQL injection
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
            // Execute the query with the current user's ID
            $stmt->execute([':id' => $_SESSION["user_id"]]);

            // Destroy the session to log the user out
            session_destroy();
            // Redirect to login page after deletion
            header("Location: auth.php");
            exit();
        }

        // Handle profile update requests
        if (isset($_POST["update_profile"])) {
            // Get the submitted form values
            $nieuwe_naam = $_POST["naam"];
            $nieuwe_email = $_POST["email"];
            $nieuw_wachtwoord = $_POST["wachtwoord"];
            $bevestig_wachtwoord = $_POST["bevestig_wachtwoord"];

            // Validate that required fields (name and email) are not empty
            if (empty($nieuwe_naam) || empty($nieuwe_email)) {
                $error = "Naam en email zijn verplicht";
            } else {
                // Check if the new email is already in use by another user
                // Exclude the current user from this check (id != :id)
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email AND id != :id");
                $stmt->execute([':email' => $nieuwe_email, ':id' => $_SESSION["user_id"]]);

                if ($stmt->rowCount() > 0) {
                    $error = "Email is al in gebruik door een ander account";
                } else {
                    // Update the user's name and email in the database
                    $stmt = $pdo->prepare("UPDATE users SET naam = :naam, email = :email WHERE id = :id");
                    $stmt->execute([
                        ':naam' => $nieuwe_naam,
                        ':email' => $nieuwe_email,
                        ':id' => $_SESSION["user_id"]
                    ]);

                    // Update password if a new password was provided (optional field)
                    if (!empty($nieuw_wachtwoord)) {
                        // Verify that the password and confirmation match
                        if ($nieuw_wachtwoord !== $bevestig_wachtwoord) {
                            $error = "Wachtwoorden komen niet overeen";
                        } else {
                            // Hash the new password using a secure one-way hashing algorithm
                            $hashed = password_hash($nieuw_wachtwoord, PASSWORD_DEFAULT);
                            // Update the password in the database
                            $stmt = $pdo->prepare("UPDATE users SET wachtwoord = :wachtwoord WHERE id = :id");
                            $stmt->execute([':wachtwoord' => $hashed, ':id' => $_SESSION["user_id"]]);
                        }
                    }

                    // If no errors occurred, update the session and show success message
                    if (empty($error)) {
                        $_SESSION["user_naam"] = $nieuwe_naam;
                        $success = "Account succesvol bijgewerkt!";
                    }
                }
            }
        }
    } catch (PDOException $e) {
        // Catch any database errors and display them to the user
        $error = "Database fout: " . $e->getMessage();
    }
}

// Fetch the current user's data from the database to populate the form
try {
    // Establish a new database connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Retrieve the user's current name and email
    $stmt = $pdo->prepare("SELECT naam, email FROM users WHERE id = :id");
    $stmt->execute([':id' => $_SESSION["user_id"]]);
    // Fetch the result as an associative array
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Catch any database errors during data retrieval
    $error = "Database fout: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Beheer</title>
</head>

<body>
    <h1>Account Beheer</h1>

    <?php if ($error): ?>
        <!-- Display error message if one exists -->
        <div class="error-message"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <!-- Display success message if one exists -->
        <div class="success-message">✓ <?php echo $success; ?></div>
    <?php endif; ?>

    <h2>Wijzig je gegevens</h2>
    <!-- Form for updating user profile information -->
    <form method="POST" action="account.php">
        <!-- Pre-fill name field with current value, using htmlspecialchars to prevent XSS attacks -->
        <input type="text" name="naam" placeholder="Naam" value="<?php echo htmlspecialchars($user['naam']); ?>"
            required><br>
        <!-- Pre-fill email field with current value -->
        <input type="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($user['email']); ?>"
            required><br>
        <!-- Password fields are optional - only update if user enters a new password -->
        <input type="password" name="wachtwoord" placeholder="Nieuw Wachtwoord (optioneel)"><br>
        <input type="password" name="bevestig_wachtwoord" placeholder="Bevestig Nieuw Wachtwoord"><br>
        <!-- Hidden field to identify this as an update request -->
        <input type="hidden" name="update_profile" value="1">
        <button type="submit">Bijwerken</button>
    </form>

    <hr>

    <h2>Verwijder Account</h2>
    <p>Let op: Dit kan niet ongedaan worden gemaakt!</p>
    <!-- Form for deleting user account - includes JavaScript confirmation dialog -->
    <form method="POST" action="account.php"
        onsubmit="return confirm('Weet je zeker dat je je account wilt verwijderen? Dit kan niet ongedaan worden gemaakt.');">
        <!-- Hidden field to identify this as a delete request -->
        <input type="hidden" name="delete_account" value="1">
        <button type="submit" style="background-color: #dc3545;">Verwijder Account</button>
    </form>

    <!-- Link to return to the home page -->
    <p><a href="index.php">Terug naar home</a></p>

    <!-- Logout button positioned at bottom left of screen -->
    <a href="account.php?logout=1" class="logout-button">Uitloggen</a>
</body>

</html>