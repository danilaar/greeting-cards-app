<?php
require_once 'config/config.php';
requireLogin();

$template_id = $_GET['template_id'] ?? 0;
$pdo = getDBConnection();

// Получение шаблона
$stmt = $pdo->prepare("SELECT * FROM templates WHERE id = ? AND is_active = TRUE");
$stmt->execute([$template_id]);
$template = $stmt->fetch();

if (!$template) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// Сохранение открытки
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_card'])) {
    $title = $_POST['title'] ?? 'Без названия';
    $html_content = $_POST['html_content'] ?? '';
    $css_content = $_POST['css_content'] ?? '';
    
    $stmt = $pdo->prepare("INSERT INTO user_cards (user_id, template_id, title, html_content, css_content) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $template_id, $title, $html_content, $css_content]);
    
    header('Location: ' . BASE_URL . '/my_cards.php');
    exit;
}

$pageTitle = 'Редактор';
include 'includes/header.php';
?>

<h1>Редактор: <?php echo htmlspecialchars($template['name']); ?></h1>

<div class="editor-container">
    <div class="editor-controls">
        <form method="POST" id="saveForm">
            <div class="form-group">
                <label for="title">Название:</label>
                <input type="text" id="title" name="title" value="Моя открытка" required>
            </div>
            <div class="form-group">
                <label>Редактируйте текст прямо на открытке:</label>
                <p class="help-text">Кликните на текст, чтобы редактировать</p>
            </div>
            <input type="hidden" name="html_content" id="html_content">
            <input type="hidden" name="css_content" id="css_content">
            <button type="submit" name="save_card" class="btn btn-primary">Сохранить открытку</button>
            <a href="<?php echo BASE_URL; ?>/index.php" class="btn btn-secondary">Отмена</a>
        </form>
    </div>
    
    <div class="editor-preview">
        <div id="card-preview" class="card-preview">
            <?php echo $template['html_content']; ?>
        </div>
        <style id="template-style">
            <?php echo $template['css_content'] ?? ''; ?>
        </style>
    </div>
</div>

<script>
// Делаем текст редактируемым
document.addEventListener('DOMContentLoaded', function() {
    const preview = document.getElementById('card-preview');
    const htmlInput = document.getElementById('html_content');
    const cssInput = document.getElementById('css_content');
    const styleElement = document.getElementById('template-style');
    
    // Делаем все элементы с текстом редактируемыми
    function makeEditable(element) {
        if (element.children.length === 0 && element.textContent.trim()) {
            element.contentEditable = true;
            element.addEventListener('blur', updateContent);
        } else {
            Array.from(element.children).forEach(child => makeEditable(child));
        }
    }
    
    function updateContent() {
        htmlInput.value = preview.innerHTML;
        cssInput.value = styleElement.textContent;
    }
    
    makeEditable(preview);
    
    // Сохранение при отправке формы
    document.getElementById('saveForm').addEventListener('submit', function() {
        updateContent();
    });
    
    // Инициализация значений
    updateContent();
});
</script>

<?php include 'includes/footer.php'; ?>


