<?php
require_once 'includes/auth.php';
require_once 'db.php';
requireLogin();

$movieId = intval($_GET['id'] ?? 0);
$userId  = currentUserId();
$error   = '';

$conn = getConnection();
$stmt = $conn->prepare("SELECT * FROM movies WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $movieId, $userId);
$stmt->execute();
$movie = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$movie) {
    $conn->close();
    header('Location: dashboard.php');
    exit();
}

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
        $q = $conn->prepare("
            UPDATE movies SET title=?, director=?, year=?, genre=?, rating=?, status=?, poster_url=?, description=?
            WHERE id=? AND user_id=?
        ");
        $q->bind_param("ssidssssii", $title, $director, $year, $genre, $rating, $status, $poster, $desc, $movieId, $userId);
        if ($q->execute()) {
            $conn->close();
            header('Location: dashboard.php?msg=updated');
            exit();
        } else {
            $error = 'Failed to update movie.';
        }
        $q->close();
    }
}

$conn->close();

$genres    = ['Action','Drama','Comedy','Horror','Sci-Fi','Romance','Thriller','Animation','Documentary'];
$pageTitle = 'Edit Movie — MovieHub';
include 'includes/header.php';
?>

<div class="page-wrapper">
    <?php include 'includes/navbar.php'; ?>
    <main class="main-content">

        <div class="form-page-header">
            <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
            <h1 class="form-page-title">Edit Movie</h1>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="form-card">
            <form method="POST" action="edit_movie.php?id=<?= $movieId ?>">
                <div class="form-grid">
                    <div class="form-group span-2">
                        <label for="title">Movie Title *</label>
                        <input type="text" id="title" name="title"
                               value="<?= htmlspecialchars($_POST['title'] ?? $movie['title']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="director">Director</label>
                        <input type="text" id="director" name="director"
                               value="<?= htmlspecialchars($_POST['director'] ?? $movie['director']) ?>">
                    </div>
                    <div class="form-group">
                        <label for="year">Year</label>
                        <input type="number" id="year" name="year"
                               value="<?= htmlspecialchars($_POST['year'] ?? $movie['year']) ?>"
                               min="1900" max="2099">
                    </div>
                    <div class="form-group">
                        <label for="genre">Genre</label>
                        <select id="genre" name="genre">
                            <?php foreach ($genres as $g): ?>
                                <option value="<?= $g ?>"
                                    <?= (($_POST['genre'] ?? $movie['genre']) === $g) ? 'selected' : '' ?>>
                                    <?= $g ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="rating">Rating (0–10)</label>
                        <input type="number" id="rating" name="rating"
                               value="<?= htmlspecialchars($_POST['rating'] ?? $movie['rating']) ?>"
                               min="0" max="10" step="0.1">
                    </div>
                    <div class="form-group">
                        <label for="status">Watch Status</label>
                        <select id="status" name="status">
                            <?php foreach (['Want to Watch','Watching','Watched'] as $s): ?>
                                <option value="<?= $s ?>"
                                    <?= (($_POST['status'] ?? $movie['status']) === $s) ? 'selected' : '' ?>>
                                    <?= $s ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group span-2">
                        <label for="poster_url">Image </label>
                        <input type="text" id="poster_url" name="poster_url"
                               value="<?= htmlspecialchars($_POST['poster_url'] ?? $movie['poster_url']) ?>">
                    </div>
                    <div class="form-group span-2">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4"><?= htmlspecialchars($_POST['description'] ?? $movie['description']) ?></textarea>
                    </div>
                </div>
                <div class="form-actions">
                    <a href="dashboard.php" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary">Save Changes</button>
                </div>
            </form>
        </div>

    </main>
</div>

<?php include 'includes/footer.php'; ?>