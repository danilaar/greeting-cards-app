<?php
require_once '../config/config.php';
requireAdmin();

$pdo = getDBConnection();
$error = '';
$success = '';

function generateTemplateHTML($data) {
    $html = '<div class="generated-card">';
    
    if (!empty($data['title'])) {
        $html .= '<h1 class="card-title">' . htmlspecialchars($data['title']) . '</h1>';
    }
    
    if (!empty($data['subtitle'])) {
        $html .= '<h2 class="card-subtitle">' . htmlspecialchars($data['subtitle']) . '</h2>';
    }
    
    if (!empty($data['message'])) {
        $html .= '<p class="card-message">' . htmlspecialchars($data['message']) . '</p>';
    }
    
    if (!empty($data['additional_text'])) {
        $html .= '<p class="card-additional">' . htmlspecialchars($data['additional_text']) . '</p>';
    }
    
    if (!empty($data['signature'])) {
        $html .= '<p class="card-signature">' . htmlspecialchars($data['signature']) . '</p>';
    }
    
    if ($data['type'] === 'invitation') {
        if (!empty($data['date'])) {
            $html .= '<p class="card-date">Дата: ' . htmlspecialchars($data['date']) . '</p>';
        }
        if (!empty($data['time'])) {
            $html .= '<p class="card-time">Время: ' . htmlspecialchars($data['time']) . '</p>';
        }
        if (!empty($data['place'])) {
            $html .= '<p class="card-place">Место: ' . htmlspecialchars($data['place']) . '</p>';
        }
    }
    
    $html .= '</div>';
    
    return $html;
}

function generateTemplateCSS($data) {
    $gradients = [
        'purple' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
        'pink' => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
        'blue' => 'linear-gradient(135deg, #1e3c72 0%, #2a5298 100%)',
        'green' => 'linear-gradient(135deg, #11998e 0%, #38ef7d 100%)',
        'orange' => 'linear-gradient(135deg, #f12711 0%, #f5af19 100%)',
        'dark' => 'linear-gradient(135deg, #2c3e50 0%, #34495e 100%)',
        'sunset' => 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
        'ocean' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'
    ];
    
    $gradient = $gradients[$data['color_scheme']] ?? $gradients['purple'];
    $textColor = $data['text_color'] ?? '#ffffff';
    $borderRadius = $data['border_radius'] ?? '10';
    $padding = $data['padding'] ?? '40';
    
    $css = ".generated-card {
    text-align: center;
    padding: {$padding}px;
    background: {$gradient};
    color: {$textColor};
    border-radius: {$borderRadius}px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    min-height: 400px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.generated-card .card-title {
    font-size: " . ($data['title_size'] ?? '2.5') . "em;
    margin-bottom: 20px;
    font-weight: bold;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

.generated-card .card-subtitle {
    font-size: " . ($data['subtitle_size'] ?? '1.8') . "em;
    margin: 15px 0;
    font-weight: 600;
}

.generated-card .card-message {
    font-size: " . ($data['message_size'] ?? '1.3') . "em;
    margin: 25px 0;
    line-height: 1.6;
}

.generated-card .card-additional {
    font-size: 1.1em;
    margin: 15px 0;
}

.generated-card .card-signature {
    font-size: " . ($data['signature_size'] ?? '1.1') . "em;
    margin-top: 30px;
    font-style: italic;
}

.generated-card .card-date,
.generated-card .card-time,
.generated-card .card-place {
    font-size: 1.2em;
    margin: 10px 0;
}";
    
    return $css;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_template'])) {
    $name = $_POST['name'] ?? '';
    $type = $_POST['type'] ?? '';
    
    if ($name && $type) {
        $html_content = generateTemplateHTML($_POST);
        $css_content = generateTemplateCSS($_POST);
        
        $stmt = $pdo->prepare("INSERT INTO templates (name, type, html_content, css_content, created_by) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$name, $type, $html_content, $css_content, $_SESSION['user_id']])) {
            $success = 'Шаблон успешно создан!';
            $_POST = [];
        } else {
            $error = 'Ошибка при создании шаблона';
        }
    } else {
        $error = 'Заполните название и тип шаблона';
    }
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("UPDATE templates SET is_active = FALSE WHERE id = ?");
    $stmt->execute([$id]);
    $success = 'Шаблон удален';
}

$stmt = $pdo->query("SELECT * FROM templates ORDER BY created_at DESC");
$templates = $stmt->fetchAll();

$pageTitle = 'Управление шаблонами';
include '../includes/header.php';
?>

<h1>Управление шаблонами</h1>

<?php if ($error): ?>
    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<div class="admin-section">
    <h2>Создать новый шаблон</h2>
    <div class="template-builder">
        <form method="POST" class="template-form" id="templateForm">
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Название шаблона:</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="type">Тип:</label>
                    <select id="type" name="type" required>
                        <option value="card" <?php echo (($_POST['type'] ?? '') === 'card') ? 'selected' : ''; ?>>Открытка</option>
                        <option value="invitation" <?php echo (($_POST['type'] ?? '') === 'invitation') ? 'selected' : ''; ?>>Приглашение</option>
                    </select>
                </div>
            </div>

            <h3>Текстовое содержимое</h3>
            <div class="form-group">
                <label for="title">Заголовок:</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" placeholder="Например: С Днем Рождения!">
            </div>
            <div class="form-group">
                <label for="subtitle">Подзаголовок (необязательно):</label>
                <input type="text" id="subtitle" name="subtitle" value="<?php echo htmlspecialchars($_POST['subtitle'] ?? ''); ?>" placeholder="Например: Дорогой друг!">
            </div>
            <div class="form-group">
                <label for="message">Основное сообщение:</label>
                <textarea id="message" name="message" rows="3" placeholder="Введите текст поздравления или приглашения"><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label for="additional_text">Дополнительный текст (необязательно):</label>
                <textarea id="additional_text" name="additional_text" rows="2" placeholder="Дополнительная информация"><?php echo htmlspecialchars($_POST['additional_text'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label for="signature">Подпись (необязательно):</label>
                <input type="text" id="signature" name="signature" value="<?php echo htmlspecialchars($_POST['signature'] ?? ''); ?>" placeholder="Например: С уважением, Ваше имя">
            </div>

            <div id="invitationFields" style="display: none;">
                <h3>Дополнительные поля для приглашения</h3>
                <div class="form-group">
                    <label for="date">Дата:</label>
                    <input type="text" id="date" name="date" value="<?php echo htmlspecialchars($_POST['date'] ?? ''); ?>" placeholder="Например: 25 декабря 2024">
                </div>
                <div class="form-group">
                    <label for="time">Время:</label>
                    <input type="text" id="time" name="time" value="<?php echo htmlspecialchars($_POST['time'] ?? ''); ?>" placeholder="Например: 18:00">
                </div>
                <div class="form-group">
                    <label for="place">Место:</label>
                    <input type="text" id="place" name="place" value="<?php echo htmlspecialchars($_POST['place'] ?? ''); ?>" placeholder="Например: Ресторан 'Восток'">
                </div>
            </div>

            <h3>Внешний вид</h3>
            <div class="form-row">
                <div class="form-group">
                    <label for="color_scheme">Цветовая схема:</label>
                    <select id="color_scheme" name="color_scheme">
                        <option value="purple" <?php echo (($_POST['color_scheme'] ?? 'purple') === 'purple') ? 'selected' : ''; ?>>Фиолетовая</option>
                        <option value="pink" <?php echo (($_POST['color_scheme'] ?? '') === 'pink') ? 'selected' : ''; ?>>Розовая</option>
                        <option value="blue" <?php echo (($_POST['color_scheme'] ?? '') === 'blue') ? 'selected' : ''; ?>>Синяя</option>
                        <option value="green" <?php echo (($_POST['color_scheme'] ?? '') === 'green') ? 'selected' : ''; ?>>Зеленая</option>
                        <option value="orange" <?php echo (($_POST['color_scheme'] ?? '') === 'orange') ? 'selected' : ''; ?>>Оранжевая</option>
                        <option value="dark" <?php echo (($_POST['color_scheme'] ?? '') === 'dark') ? 'selected' : ''; ?>>Темная</option>
                        <option value="sunset" <?php echo (($_POST['color_scheme'] ?? '') === 'sunset') ? 'selected' : ''; ?>>Закат</option>
                        <option value="ocean" <?php echo (($_POST['color_scheme'] ?? '') === 'ocean') ? 'selected' : ''; ?>>Океан</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="text_color">Цвет текста:</label>
                    <input type="color" id="text_color" name="text_color" value="<?php echo htmlspecialchars($_POST['text_color'] ?? '#ffffff'); ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="border_radius">Скругление углов (px):</label>
                    <input type="number" id="border_radius" name="border_radius" value="<?php echo htmlspecialchars($_POST['border_radius'] ?? '10'); ?>" min="0" max="50">
                </div>
                <div class="form-group">
                    <label for="padding">Отступы (px):</label>
                    <input type="number" id="padding" name="padding" value="<?php echo htmlspecialchars($_POST['padding'] ?? '40'); ?>" min="20" max="100">
                </div>
            </div>

            <h3>Размеры текста (em)</h3>
            <div class="form-row">
                <div class="form-group">
                    <label for="title_size">Размер заголовка:</label>
                    <input type="number" id="title_size" name="title_size" value="<?php echo htmlspecialchars($_POST['title_size'] ?? '2.5'); ?>" step="0.1" min="1" max="5">
                </div>
                <div class="form-group">
                    <label for="subtitle_size">Размер подзаголовка:</label>
                    <input type="number" id="subtitle_size" name="subtitle_size" value="<?php echo htmlspecialchars($_POST['subtitle_size'] ?? '1.8'); ?>" step="0.1" min="1" max="5">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="message_size">Размер сообщения:</label>
                    <input type="number" id="message_size" name="message_size" value="<?php echo htmlspecialchars($_POST['message_size'] ?? '1.3'); ?>" step="0.1" min="1" max="5">
                </div>
                <div class="form-group">
                    <label for="signature_size">Размер подписи:</label>
                    <input type="number" id="signature_size" name="signature_size" value="<?php echo htmlspecialchars($_POST['signature_size'] ?? '1.1'); ?>" step="0.1" min="1" max="5">
                </div>
            </div>

            <div class="preview-section">
                <h3>Предпросмотр</h3>
                <div id="templatePreview" class="template-preview"></div>
            </div>

            <button type="submit" name="create_template" class="btn btn-primary">Создать шаблон</button>
        </form>
    </div>
</div>

<div class="admin-section">
    <h2>Существующие шаблоны</h2>
    <div class="templates-list">
        <?php foreach ($templates as $template): ?>
            <div class="template-item">
                <h3><?php echo htmlspecialchars($template['name']); ?></h3>
                <p>Тип: <?php echo $template['type'] === 'card' ? 'Открытка' : 'Приглашение'; ?></p>
                <p>Статус: <?php echo $template['is_active'] ? 'Активен' : 'Неактивен'; ?></p>
                <a href="?delete=<?php echo $template['id']; ?>" class="btn btn-danger" onclick="return confirm('Удалить шаблон?')">Удалить</a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('templateForm');
    const typeSelect = document.getElementById('type');
    const invitationFields = document.getElementById('invitationFields');
    const preview = document.getElementById('templatePreview');
    
    const gradients = {
        purple: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
        pink: 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
        blue: 'linear-gradient(135deg, #1e3c72 0%, #2a5298 100%)',
        green: 'linear-gradient(135deg, #11998e 0%, #38ef7d 100%)',
        orange: 'linear-gradient(135deg, #f12711 0%, #f5af19 100%)',
        dark: 'linear-gradient(135deg, #2c3e50 0%, #34495e 100%)',
        sunset: 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
        ocean: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'
    };
    
    function toggleInvitationFields() {
        if (typeSelect.value === 'invitation') {
            invitationFields.style.display = 'block';
        } else {
            invitationFields.style.display = 'none';
        }
        updatePreview();
    }
    
    typeSelect.addEventListener('change', toggleInvitationFields);
    toggleInvitationFields();
    
    function updatePreview() {
        const formData = new FormData(form);
        const data = {};
        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }
        
        // Генерация HTML
        let html = '<div class="generated-card">';
        
        if (data.title) {
            html += `<h1 class="card-title">${escapeHtml(data.title)}</h1>`;
        }
        
        if (data.subtitle) {
            html += `<h2 class="card-subtitle">${escapeHtml(data.subtitle)}</h2>`;
        }
        
        if (data.message) {
            html += `<p class="card-message">${escapeHtml(data.message).replace(/\n/g, '<br>')}</p>`;
        }
        
        if (data.additional_text) {
            html += `<p class="card-additional">${escapeHtml(data.additional_text).replace(/\n/g, '<br>')}</p>`;
        }
        
        if (data.signature) {
            html += `<p class="card-signature">${escapeHtml(data.signature)}</p>`;
        }
        
        if (data.type === 'invitation') {
            if (data.date) {
                html += `<p class="card-date">Дата: ${escapeHtml(data.date)}</p>`;
            }
            if (data.time) {
                html += `<p class="card-time">Время: ${escapeHtml(data.time)}</p>`;
            }
            if (data.place) {
                html += `<p class="card-place">Место: ${escapeHtml(data.place)}</p>`;
            }
        }
        
        html += '</div>';
        
        // Генерация CSS
        const gradient = gradients[data.color_scheme] || gradients.purple;
        const textColor = data.text_color || '#ffffff';
        const borderRadius = data.border_radius || '10';
        const padding = data.padding || '40';
        const titleSize = data.title_size || '2.5';
        const subtitleSize = data.subtitle_size || '1.8';
        const messageSize = data.message_size || '1.3';
        const signatureSize = data.signature_size || '1.1';
        
        const css = `
            .generated-card {
                text-align: center;
                padding: ${padding}px;
                background: ${gradient};
                color: ${textColor};
                border-radius: ${borderRadius}px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.3);
                min-height: 400px;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
            }
            .generated-card .card-title {
                font-size: ${titleSize}em;
                margin-bottom: 20px;
                font-weight: bold;
                text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            }
            .generated-card .card-subtitle {
                font-size: ${subtitleSize}em;
                margin: 15px 0;
                font-weight: 600;
            }
            .generated-card .card-message {
                font-size: ${messageSize}em;
                margin: 25px 0;
                line-height: 1.6;
            }
            .generated-card .card-additional {
                font-size: 1.1em;
                margin: 15px 0;
            }
            .generated-card .card-signature {
                font-size: ${signatureSize}em;
                margin-top: 30px;
                font-style: italic;
            }
            .generated-card .card-date,
            .generated-card .card-time,
            .generated-card .card-place {
                font-size: 1.2em;
                margin: 10px 0;
            }
        `;
        
        // Обновление предпросмотра
        preview.innerHTML = html;
        
        // Добавление стилей
        let styleElement = document.getElementById('preview-style');
        if (!styleElement) {
            styleElement = document.createElement('style');
            styleElement.id = 'preview-style';
            document.head.appendChild(styleElement);
        }
        styleElement.textContent = css;
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Обновление предпросмотра при изменении полей
    const inputs = form.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        input.addEventListener('input', updatePreview);
        input.addEventListener('change', updatePreview);
    });
    
    // Инициализация предпросмотра
    updatePreview();
});
</script>

<?php include '../includes/footer.php'; ?>

