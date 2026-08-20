<div class="container">
    <div class="tool-page">
        <!-- Breadcrumb -->
        <nav class="breadcrumb">
            <a href="/">Все инструменты</a>
            <span class="sep">›</span>
            <?php
                $catConfig = $config['categories'][$currentTool['category']] ?? null;
                if ($catConfig):
            ?>
                <span><?= $catConfig['icon'] . ' ' . Security::escape($catConfig['name']) ?></span>
                <span class="sep">›</span>
            <?php endif; ?>
            <span><?= Security::escape($currentTool['name']) ?></span>
        </nav>

        <!-- Tool Header -->
        <div class="tool-header">
            <h1><?= Security::escape($currentTool['name']) ?></h1>
            <p><?= Security::escape($currentTool['description']) ?></p>
        </div>

        <!-- Tool Content (loaded from tool's view.php) -->
        <?php
            if (file_exists($viewPath)) {
                require $viewPath;
            } else {
                echo '<p>Инструмент в разработке</p>';
            }
        ?>
    </div>
</div>
