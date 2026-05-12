<?php
require_once 'includes/auth.php';
require_once 'db.php';
requireLogin();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title    = trim($_POST['title'] ?? '');
    $director = trim($_POST['director'] ?? '');
    $year     = intval($_POST['year'] ?? 0) ?: null;
    $genre    = $_POST['genre'] ?? '';
    $rating   = isset($_POST['rating']) && $_POST['rating'] !== '' ? floatval($_POST['rating']) : null;
    $status   = $_POST['status'] ?? 'Want to Watch';
    $poster   = trim($_POST['poster_url'] ?? '');
    $desc     = trim($_POST['description'] ?? '');

    if (empty($title)) {
        $error = 'Movie title is required.';
    } else {
        $conn   = getConnection();
        $userId = currentUserId();
        $stmt   = $conn->prepare("
            INSERT INTO movies (user_id, title, director, year, genre, rating, status, poster_url, description)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("issiidsss", $userId, $title, $director, $year, $genre, $rating, $status, $poster, $desc);

        if ($stmt->execute()) {
            header('Location: dashboard.php?msg=added');
            exit();
        } else {
            $error = 'Failed to add movie. Please try again.';
        }
        $stmt->close();
        $conn->close();
    }
}

$pageTitle = 'Add Movie — MovieHub';
$genres    = ['Action','Drama','Comedy','Horror','Sci-Fi','Romance','Thriller','Animation','Documentary'];
include 'includes/header.php';
?>

<div class="page-wrapper">
    <?php include 'includes/navbar.php'; ?>
    <main class="main-content">

        <div class="form-page-header">
            <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
            <h1 class="form-page-title">Add New Movie</h1>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="form-card">
            <form method="POST" action="add_movie.php">
                <div class="form-grid">
                    <div class="form-group span-2">
                        <label for="title">Movie Title *</label>
                        <input type="text" id="title" name="title"
                               value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
                               placeholder="e.g. Oppenheimer" required>
                    </div>
                    <div class="form-group">
                        <label for="director">Director</label>
                        <input type="text" id="director" name="director"
                               value="<?= htmlspecialchars($_POST['director'] ?? '') ?>"
                               placeholder="e.g. Christopher Nolan">
                    </div>
                    <div class="form-group">
                        <label for="year">Year</label>
                        <input type="number" id="year" name="year"
                               value="<?= htmlspecialchars($_POST['year'] ?? '') ?>"
                               placeholder="2024" min="1900" max="2099">
                    </div>
                    <div class="form-group">
                        <label for="genre">Genre</label>
                        <select id="genre" name="genre">
                            <?php foreach ($genres as $g): ?>
                                <option value="<?= $g ?>" <?= (($_POST['genre'] ?? '') === $g) ? 'selected' : '' ?>>
                                    <?= $g ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="rating">Rating (0–10)</label>
                        <input type="number" id="rating" name="rating"
                               value="<?= htmlspecialchars($_POST['rating'] ?? '') ?>"
                               placeholder="8.5" min="0" max="10" step="0.1">
                    </div>
                    <div class="form-group">
                        <label for="status">Watch Status</label>
                        <select id="status" name="status">
                            <option value="Want to Watch">Want to Watch</option>
                            <option value="Watching">Watching</option>
                            <option value="Watched">Watched</option>
                        </select>
                    </div>
                    <div class="form-group span-2">
                        <label for="poster_url"> Image </label>
                        <input type="text" id="poster_url" name="poster_url"
                               value="<?= htmlspecialchars($_POST['poster_url'] ?? '') ?>"
                               placeholder="assets/images/ho.jpeg">
                    </div>
                    <div class="form-group span-2">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4"
                                  placeholder="Brief synopsis..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    </div>
                </div>
                <div class="form-actions">
                    <a href="dashboard.php" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary">Add Movie</button>
                </div>
            </form>
        </div>

    </main>
</div>

<?php include 'includes/footer.php'; ?>