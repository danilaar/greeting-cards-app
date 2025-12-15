<?php
require_once 'config/config.php';
requireLogin();

$card_id = $_GET['id'] ?? 0;
$pdo = getDBConnection();

$stmt = $pdo->prepare("SELECT * FROM user_cards WHERE id = ? AND user_id = ?");
$stmt->execute([$card_id, $_SESSION['user_id']]);
$card = $stmt->fetch();

if (!$card) {
    header('Location: ' . BASE_URL . '/my_cards.php');
    exit;
}

$pageTitle = htmlspecialchars($card['title']);
include 'includes/header.php';
?>

<div class="card-view">
    <h1><?php echo htmlspecialchars($card['title']); ?></h1>
    <div class="card-display">
        <?php echo $card['html_content']; ?>
        <style><?php echo $card['css_content'] ?? ''; ?></style>
    </div>
    <div class="card-actions">
        <button onclick="window.print()" class="btn btn-primary">Печать</button>
        <a href="<?php echo BASE_URL; ?>/my_cards.php" class="btn btn-secondary">Назад</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>




