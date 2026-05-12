<?php
$genreColors = [
    'Action'      => '#E24B4A',
    'Drama'       => '#378ADD',
    'Comedy'      => '#EF9F27',
    'Horror'      => '#7F77DD',
    'Sci-Fi'      => '#1D9E75',
    'Romance'     => '#D4537E',
    'Thriller'    => '#5F5E5A',
    'Animation'   => '#639922',
    'Documentary' => '#185FA5',
];
$genreEmojis = [
    'Action' => '💥', 'Drama' => '🎭', 'Comedy' => '😂', 'Horror' => '👻',
    'Sci-Fi' => '🚀', 'Romance' => '❤️', 'Thriller' => '🔪',
    'Animation' => '✨', 'Documentary' => '🎞️',
];
$statusColors = [
    'Watched'       => '#0F6E56',
    'Watching'      => '#185FA5',
    'Want to Watch' => '#884F0B',
];

$genreColor  = $genreColors[$movie['genre']] ?? '#888';
$genreEmoji  = $genreEmojis[$movie['genre']] ?? '🎬';
$statusColor = $statusColors[$movie['status']] ?? '#888';
?>

<div class="movie-card">
    <?php if (!empty($movie['poster_url'])): ?>
        <img class="card-poster"
             src="<?= htmlspecialchars($movie['poster_url']) ?>"
             alt="<?= htmlspecialchars($movie['title']) ?>"
             onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
        <div class="card-poster-fallback" style="display:none"><?= $genreEmoji ?></div>
    <?php else: ?>
        <div class="card-poster-fallback"><?= $genreEmoji ?></div>
    <?php endif; ?>

    <?php if (!empty($movie['genre'])): ?>
        <span class="card-genre-badge" style="background:<?= $genreColor ?>">
            <?= htmlspecialchars($movie['genre']) ?>
        </span>
    <?php endif; ?>

    <?php if (!empty($movie['rating'])): ?>
        <span class="card-rating-badge">★ <?= number_format($movie['rating'], 1) ?></span>
    <?php endif; ?>

    <div class="card-body">
        <h3 class="card-title" title="<?= htmlspecialchars($movie['title']) ?>">
            <?= htmlspecialchars($movie['title']) ?>
        </h3>
        <p class="card-meta">
            <?= $movie['year'] ?? '—' ?>
            <?= !empty($movie['director']) ? ' · ' . htmlspecialchars($movie['director']) : '' ?>
        </p>
        <p class="card-status" style="color:<?= $statusColor ?>">
            <?= htmlspecialchars($movie['status']) ?>
        </p>
        <?php if (!empty($movie['description'])): ?>
            <p class="card-desc"><?= htmlspecialchars(mb_strimwidth($movie['description'], 0, 80, '…')) ?></p>
        <?php endif; ?>

        <div class="card-actions">
            <a href="view_movie.php?id=<?= $movie['id'] ?>" class="btn-card btn-view">View</a>
            <a href="edit_movie.php?id=<?= $movie['id'] ?>" class="btn-card btn-edit">Edit</a>
            <button class="btn-card btn-delete"
                    onclick="confirmDelete(<?= $movie['id'] ?>, '<?= htmlspecialchars(addslashes($movie['title'])) ?>')">
                Delete
            </button>
        </div>
    </div>
</div>