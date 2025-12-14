<?php
require_once 'config/config.php';
requireLogin();

$pdo = getDBConnection();
$type = $_GET['type'] ?? 'all';

$query = "SELECT * FROM templates WHERE is_active = TRUE";
$params = [];

if ($type === 'card' || $type === 'invitation') {
    $query .= " AND type = ?";
    $params[] = $type;
}

$query .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$templates = $stmt->fetchAll();

$pageTitle = 'Выбор шаблона';
include 'includes/header.php';
?>

<h1>Выберите шаблон</h1>

<div class="filter-tabs">
    <a href="?type=all" class="tab <?php echo $type === 'all' ? 'active' : ''; ?>">Все</a>
    <a href="?type=card" class="tab <?php echo $type === 'card' ? 'active' : ''; ?>">Открытки</a>
    <a href="?type=invitation" class="tab <?php echo $type === 'invitation' ? 'active' : ''; ?>">Приглашения</a>
</div>

<?php if (empty($templates)): ?>
    <div class="empty-state">
        <p>Шаблоны пока не созданы. <?php if (isAdmin()): ?><a href="<?php echo BASE_URL; ?>/admin/templates.php">Создать шаблон</a><?php endif; ?></p>
    </div>
<?php else: ?>
    <div class="templates-grid">
        <?php foreach ($templates as $template): ?>
            <div class="template-card">
                <div class="template-preview">
                    <?php echo $template['html_content']; ?>
                </div>
                <div class="template-info">
                    <h3><?php echo htmlspecialchars($template['name']); ?></h3>
                    <span class="template-type"><?php echo $template['type'] === 'card' ? 'Открытка' : 'Приглашение'; ?></span>
                    <a href="<?php echo BASE_URL; ?>/editor.php?template_id=<?php echo $template['id']; ?>" class="btn btn-primary">Редактировать</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>


