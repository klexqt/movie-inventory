<?php
require_once 'includes/auth.php';
require_once 'db.php';
requireLogin();

$conn = getConnection();
$userId = currentUserId();

$statsQuery = $conn->query("
    SELECT
        COUNT(*) AS total,
        SUM(status = 'Watched') AS watched,
        SUM(status = 'Watching') AS watching,
        SUM(status = 'Want to Watch') AS want,
        ROUND(AVG(CASE WHEN rating IS NOT NULL THEN rating END), 1) AS avg_rating
    FROM movies WHERE user_id = $userId
");
$stats = $statsQuery->fetch_assoc();

$genreFilter  = $_GET['genre']  ?? 'All';
$statusFilter = $_GET['status'] ?? 'All';
$search       = trim($_GET['search'] ?? '');

$where  = "WHERE user_id = ?";
$params = [$userId];
$types  = "i";

if ($genreFilter !== 'All') {
    $where   .= " AND genre = ?";
    $params[] = $genreFilter;
    $types   .= "s";
}
if ($statusFilter !== 'All') {
    $where   .= " AND status = ?";
    $params[] = $statusFilter;
    $types   .= "s";
}
if ($search !== '') {
    $where   .= " AND (title LIKE ? OR director LIKE ?)";
    $like     = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $types   .= "ss";
}

$sort    = $_GET['sort'] ?? 'newest';
$orderBy = match($sort) {
    'title'  => 'title ASC',
    'rating' => 'rating DESC',
    'year'   => 'year DESC',
    default  => 'created_at DESC',
};

$stmt = $conn->prepare("SELECT * FROM movies $where ORDER BY $orderBy");
$stmt->bind_param($types, ...$params);
$stmt->execute();
$movies = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$genreResult = $conn->query("SELECT DISTINCT genre FROM movies WHERE user_id = $userId ORDER BY genre");
$genres      = $genreResult->fetch_all(MYSQLI_ASSOC);

$conn->close();

$pageTitle = 'Dashboard — MovieHub';
include 'includes/header.php';
?>

<div class="page-wrapper">
    <?php include 'includes/navbar.php'; ?>
    <main class="main-content">

        <div class="stats-bar">
            <div class="stat-card">
                <span class="stat-num"><?= $stats['total'] ?? 0 ?></span>
                <span class="stat-label">Total</span>
            </div>
            <div class="stat-card">
                <span class="stat-num watched"><?= $stats['watched'] ?? 0 ?></span>
                <span class="stat-label">Watched</span>
            </div>
            <div class="stat-card">
                <span class="stat-num watching"><?= $stats['watching'] ?? 0 ?></span>
                <span class="stat-label">Watching</span>
            </div>
            <div class="stat-card">
                <span class="stat-num want"><?= $stats['want'] ?? 0 ?></span>
                <span class="stat-label">Want to Watch</span>
            </div>
            <div class="stat-card">
                <span class="stat-num rating">★ <?= $stats['avg_rating'] ?? '—' ?></span>
                <span class="stat-label">Avg Rating</span>
            </div>
        </div>

        <div class="toolbar">
            <form method="GET" action="dashboard.php" class="toolbar-form">
                <div class="search-wrap">
                    <span class="search-icon"><svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" viewBox="-0.5 0 41 41">
	<g fill="none">
		<path fill="#d92eec" stroke="#231f20" stroke-miterlimit="10" d="M28.39 5.85c-6.76-6.75-15-7.5-22.53 0s-6.75 15.77 0 22.52c5.79 5.79 12.68 7.15 19.26 2.71c.58.92 1.28 1.82 2.14 2.92c6.25 7.93 8.82 5.46 10.5 3.78S41.9 33.53 34 27.27c-1.08-.86-2-1.55-2.91-2.14c4.45-6.58 3.09-13.48-2.7-19.28Z" stroke-width="1" />
		<path fill="#fff" stroke="#231f20" stroke-miterlimit="10" d="M7.14 17.11a9.99 9.99 0 1 0 19.98 0a9.99 9.99 0 0 0-19.98 0Z" stroke-width="1" />
		<path fill="#fff" d="M14.8 11a3.74 3.74 0 0 1 4.71-.16" />
		<path stroke="#231f20" stroke-linecap="round" stroke-miterlimit="10" d="M14.8 11a3.74 3.74 0 0 1 4.71-.16" stroke-width="1" />
		<path stroke="#fff" stroke-linecap="round" stroke-miterlimit="10" d="M19.73 3.79a7.5 7.5 0 0 1 4.88 2.57" stroke-width="1" />
	</g>
</svg></span>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                           placeholder="Search movies..." onchange="this.form.submit()">
                </div>
                <select name="status" onchange="this.form.submit()">
                    <option value="All"           <?= $statusFilter === 'All'           ? 'selected' : '' ?>>All Status</option>
                    <option value="Want to Watch" <?= $statusFilter === 'Want to Watch' ? 'selected' : '' ?>>Want to Watch</option>
                    <option value="Watching"      <?= $statusFilter === 'Watching'      ? 'selected' : '' ?>>Watching</option>
                    <option value="Watched"       <?= $statusFilter === 'Watched'       ? 'selected' : '' ?>>Watched</option>
                </select>
                <select name="sort" onchange="this.form.submit()">
                    <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest First</option>
                    <option value="title"  <?= $sort === 'title'  ? 'selected' : '' ?>>A–Z Title</option>
                    <option value="rating" <?= $sort === 'rating' ? 'selected' : '' ?>>Top Rated</option>
                    <option value="year"   <?= $sort === 'year'   ? 'selected' : '' ?>>By Year</option>
                </select>
                <input type="hidden" name="genre" value="<?= htmlspecialchars($genreFilter) ?>">
            </form>
            <a href="add_movie.php" class="btn-add">+ Add Movie</a>
        </div>

        <div class="genre-tabs">
            <a href="?genre=All&status=<?= urlencode($statusFilter) ?>&sort=<?= $sort ?>&search=<?= urlencode($search) ?>"
               class="genre-tab <?= $genreFilter === 'All' ? 'active' : '' ?>">All</a>
            <?php foreach ($genres as $g): ?>
                <a href="?genre=<?= urlencode($g['genre']) ?>&status=<?= urlencode($statusFilter) ?>&sort=<?= $sort ?>&search=<?= urlencode($search) ?>"
                   class="genre-tab <?= $genreFilter === $g['genre'] ? 'active' : '' ?>">
                    <?= htmlspecialchars($g['genre']) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <p class="results-count">
            Showing <strong><?= count($movies) ?></strong> movie<?= count($movies) !== 1 ? 's' : '' ?>
        </p>

        <?php if (empty($movies)): ?>
            <div class="empty-state">
                <div class="empty-icon">🎬</div>
                <h3>No movies found</h3>
                <p>Add your first movie to get started!</p>
                <a href="add_movie.php" class="btn-add" style="display:inline-block;margin-top:12px">+ Add Movie</a>
            </div>
        <?php else: ?>
            <div class="movie-grid">
                <?php foreach ($movies as $movie): ?>
                    <?php include 'includes/movie_card.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </main>
</div>

<?php include 'includes/footer.php'; ?>