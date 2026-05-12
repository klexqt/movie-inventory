<?php
require_once 'includes/auth.php';
require_once 'db.php';
requireLogin();

$movieId = intval($_GET['id'] ?? 0);
$userId  = currentUserId();

$conn = getConnection();
$stmt = $conn->prepare("SELECT * FROM movies WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $movieId, $userId);
$stmt->execute();
$movie = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

if (!$movie) {
    header('Location: dashboard.php');
    exit();
}

$genreColors = [
    'Action'=>'#E24B4A','Drama'=>'#378ADD','Comedy'=>'#EF9F27','Horror'=>'#7F77DD',
    'Sci-Fi'=>'#1D9E75','Romance'=>'#D4537E','Thriller'=>'#5F5E5A',
    'Animation'=>'#639922','Documentary'=>'#185FA5',
];
$genreEmojis = [
    'Action'=>'💥','Drama'=>'🎭','Comedy'=>'😂','Horror'=>'👻','Sci-Fi'=>'🚀',
    'Romance'=>'❤️','Thriller'=>'🔪','Animation'=>'✨','Documentary'=>'🎞️',
];
$statusColors = ['Watched'=>'#0F6E56','Watching'=>'#185FA5','Want to Watch'=>'#884F0B'];

$genreColor  = $genreColors[$movie['genre']] ?? '#888';
$genreEmoji  = $genreEmojis[$movie['genre']] ?? '🎬';
$statusColor = $statusColors[$movie['status']] ?? '#888';

$pageTitle = htmlspecialchars($movie['title']) . ' — MovieHub';
include 'includes/header.php';
?>

<div class="page-wrapper">
    <?php include 'includes/navbar.php'; ?>
    <main class="main-content">

        <div class="form-page-header">
            <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
        </div>

        <div class="view-card">
            <div class="view-poster-col">
                <?php if (!empty($movie['poster_url'])): ?>
                    <img src="<?= htmlspecialchars($movie['poster_url']) ?>"
                         alt="<?= htmlspecialchars($movie['title']) ?>"
                         class="view-poster"
                         onerror="this.style.display='none';document.getElementById('poster-fb').style.display='flex'">
                    <div id="poster-fb" class="view-poster-fallback" style="display:none"><?= $genreEmoji ?></div>
                <?php else: ?>
                    <div class="view-poster-fallback"><?= $genreEmoji ?></div>
                <?php endif; ?>
            </div>

            <div class="view-info-col">
                <div class="view-badges">
                    <?php if ($movie['genre']): ?>
                        <span class="badge" style="background:<?= $genreColor ?>;color:#fff"><?= htmlspecialchars($movie['genre']) ?></span>
                    <?php endif; ?>
                    <span class="badge" style="background:<?= $statusColor ?>;color:#fff"><?= htmlspecialchars($movie['status']) ?></span>
                </div>

                <h1 class="view-title"><?= htmlspecialchars($movie['title']) ?></h1>

                <div class="view-meta-row">
                    <?php if ($movie['director']): ?>
                        <span>🎬 <?= htmlspecialchars($movie['director']) ?></span>
                    <?php endif; ?>
                    <?php if ($movie['year']): ?>
                        <span>📅 <?= $movie['year'] ?></span>
                    <?php endif; ?>
                    <?php if ($movie['rating']): ?>
                        <span class="view-rating">★ <?= number_format($movie['rating'], 1) ?> / 10</span>
                    <?php endif; ?>
                </div>

                <?php if ($movie['description']): ?>
                    <p class="view-desc"><?= nl2br(htmlspecialchars($movie['description'])) ?></p>
                <?php endif; ?>

                <p class="view-dates">
                    Added: <?= date('M d, Y', strtotime($movie['created_at'])) ?>
                    <?php if ($movie['updated_at'] !== $movie['created_at']): ?>
                        · Updated: <?= date('M d, Y', strtotime($movie['updated_at'])) ?>
                    <?php endif; ?>
                </p>

                <div class="view-actions">
                    <a href="edit_movie.php?id=<?= $movie['id'] ?>" class="btn-primary">Edit Movie</a>
                    <button class="btn-danger"
                            onclick="confirmDelete(<?= $movie['id'] ?>, '<?= htmlspecialchars(addslashes($movie['title'])) ?>')">
                        Delete Movie
                    </button>
                </div>
            </div>
        </div>

    </main>
</div>

<?php include 'includes/footer.php'; ?>