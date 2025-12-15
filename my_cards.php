<?php
require_once 'config/config.php';
requireLogin();

$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT * FROM user_cards WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$cards = $stmt->fetchAll();

$pageTitle = 'Мои открытки';
include 'includes/header.php';
?>

<h1>Мои открытки и приглашения</h1>

<?php if (empty($cards)): ?>
    <div class="empty-state">
        <p>У вас пока нет созданных открыток. <a href="<?php echo BASE_URL; ?>/index.php">Создать открытку</a></p>
    </div>
<?php else: ?>
    <div class="cards-grid">
        <?php foreach ($cards as $card): ?>
            <div class="card-item">
                <div class="card-preview-small">
                    <?php echo $card['html_content']; ?>
                    <style><?php echo $card['css_content'] ?? ''; ?></style>
                </div>
                <div class="card-info">
                    <h3><?php echo htmlspecialchars($card['title']); ?></h3>
                    <p class="card-date">Создано: <?php echo date('d.m.Y H:i', strtotime($card['created_at'])); ?></p>
                    <a href="<?php echo BASE_URL; ?>/view_card.php?id=<?php echo $card['id']; ?>" class="btn btn-primary" target="_blank">Просмотреть</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>




