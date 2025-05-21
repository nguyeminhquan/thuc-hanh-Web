<?php
require_once 'auth.php';

if (!checkAutoLogin()) {
    header('Location: index.php');
    exit;
}

// Handle theme change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['theme'])) {
    setTheme($_POST['theme']);
    header('Location: dashboard.php');
    exit;
}

// Handle display name change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_name'])) {
    updateDisplayName($_POST['new_name']);
    header('Location: dashboard.php');
    exit;
}

$current_theme = getTheme();
$visit_count = incrementVisitCount();
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $current_theme; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Welcome, <?php echo getDisplayName(); ?>!</h1>
        <p>You are logged in as <?php echo getUserRole(); ?>.</p>
        
        <?php if (getUserRole() === 'admin'): ?>
            <div class="secret-content">
                <h2>Secret Admin Content</h2>
                <p>This content is only visible to administrators.</p>
                <p>Server secrets: The meaning of life is 42.</p>
            </div>
        <?php else: ?>
            <p>Visit count: <?php echo $visit_count; ?></p>
        <?php endif; ?>
        
        <div class="actions">
            <form method="POST" action="" class="theme-form">
                <h3>Select Theme:</h3>
                <div class="theme-options">
                    <label>
                        <input type="radio" name="theme" value="light" <?php echo $current_theme === 'light' ? 'checked' : ''; ?>> Light
                    </label>
                    <label>
                        <input type="radio" name="theme" value="dark" <?php echo $current_theme === 'dark' ? 'checked' : ''; ?>> Dark
                    </label>
                    <button type="submit" class="btn">Apply</button>
                </div>
            </form>
            
            <form method="POST" action="" class="name-form">
                <h3>Change Display Name:</h3>
                <input type="text" name="new_name" placeholder="New display name" required>
                <button type="submit" class="btn">Update</button>
            </form>
            
            <a href="logout.php" class="btn logout">Logout</a>
        </div>
    </div>
</body>
</html>