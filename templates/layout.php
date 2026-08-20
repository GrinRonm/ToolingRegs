<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Security::escape($pageTitle ?? 'Instyment') ?></title>
    <meta name="description" content="<?= Security::escape($pageDescription ?? 'Бесплатные онлайн инструменты') ?>">
    <meta name="csrf-token" content="<?= Security::getToken() ?>">

    <!-- Open Graph -->
    <meta property="og:title" content="<?= Security::escape($pageTitle ?? 'Instyment') ?>">
    <meta property="og:description" content="<?= Security::escape($pageDescription ?? '') ?>">
    <meta property="og:type" content="website">

    <!-- Favicon -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🔧</text></svg>">

    <!-- Styles -->
    <link rel="stylesheet" href="/assets/css/main.css">
    <?php if (isset($currentTool) && $currentTool): ?>
        <?php
            $toolStylePath = $currentTool['_path'] . '/style.css';
            if (file_exists($toolStylePath)):
                $toolStyleUrl = '/tools/' . $currentTool['_category_dir'] . '/' . $currentTool['_tool_dir'] . '/style.css';
        ?>
            <link rel="stylesheet" href="<?= $toolStyleUrl ?>">
        <?php endif; ?>
    <?php endif; ?>
</head>
<body>

    <!-- Header -->
    <header class="header">
        <div class="container header-inner">
            <a href="/" class="logo">
                <div class="logo-icon">⚡</div>
                Instyment
            </a>

            <div class="search-wrap">
                <span class="search-icon">🔍</span>
                <input type="text"
                       id="search-input"
                       class="search-input"
                       placeholder="Найти инструмент... (нажмите /)"
                       autocomplete="off">
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        <?php if (isset($currentTool) && $currentTool): ?>
            <!-- Tool Page -->
            <?php require __DIR__ . '/tool.php'; ?>
        <?php else: ?>
            <!-- Home Page -->
            <?php require __DIR__ . '/home.php'; ?>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            Instyment &copy; <?= date('Y') ?> — Бесплатные онлайн инструменты
        </div>
    </footer>

    <!-- Scripts -->
    <script src="/assets/js/app.js"></script>
    <?php if (isset($currentTool) && $currentTool): ?>
        <script src="/assets/js/uploader.js"></script>
        <?php
            $toolScriptPath = $currentTool['_path'] . '/script.js';
            if (file_exists($toolScriptPath)):
                $toolScriptUrl = '/tools/' . $currentTool['_category_dir'] . '/' . $currentTool['_tool_dir'] . '/script.js';
        ?>
            <script src="<?= $toolScriptUrl ?>"></script>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>
