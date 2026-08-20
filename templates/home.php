<div class="container">
    <!-- Hero -->
    <section class="hero">
        <h1>Онлайн инструменты</h1>
        <p>Работайте с изображениями, PDF и файлами прямо в браузере — бесплатно и без регистрации</p>
    </section>

    <!-- Category Tabs -->
    <nav class="categories" id="categories">
        <button class="category-tab active" data-category="all">
            <span class="cat-icon">📦</span> Все
        </button>
        <?php foreach ($categories as $catId => $cat): ?>
            <button class="category-tab" data-category="<?= Security::escape($catId) ?>">
                <span class="cat-icon"><?= $cat['icon'] ?></span>
                <?= Security::escape($cat['name']) ?>
            </button>
        <?php endforeach; ?>
    </nav>

    <!-- Tools Grid -->
    <div class="tools-grid" id="tools-grid">
        <?php foreach ($tools as $tool): ?>
            <a href="<?= Security::escape($tool['_url']) ?>"
               class="tool-card"
               data-category="<?= Security::escape($tool['category']) ?>"
               data-name="<?= Security::escape($tool['name']) ?>"
               data-desc="<?= Security::escape($tool['description']) ?>"
               data-tags="<?= Security::escape(implode(',', $tool['tags'] ?? [])) ?>">

                <div class="tool-card-icon">
                    <?= $tool['icon'] ?? '🔧' ?>
                </div>
                <div class="tool-card-title"><?= Security::escape($tool['name']) ?></div>
                <div class="tool-card-desc"><?= Security::escape($tool['description']) ?></div>
                <div class="tool-card-category">
                    <?php
                        $catConfig = $config['categories'][$tool['category']] ?? null;
                        if ($catConfig) {
                            echo $catConfig['icon'] . ' ' . Security::escape($catConfig['name']);
                        }
                    ?>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- No Results -->
    <div class="no-results" id="no-results" style="display:none;">
        <div class="no-results-icon">🔍</div>
        <p>Инструменты не найдены</p>
    </div>
</div>
