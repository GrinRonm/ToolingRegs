<?php
/**
 * ToolRegistry — Auto-discovers and registers tools from manifest.json files
 */

class ToolRegistry
{
    private static ?array $tools = null;

    /**
     * Get all registered tools
     */
    public static function getAll(): array
    {
        if (self::$tools === null) {
            self::scan();
        }
        return self::$tools;
    }

    /**
     * Get tool by ID
     */
    public static function get(string $id): ?array
    {
        $tools = self::getAll();
        return $tools[$id] ?? null;
    }

    /**
     * Get tools by category
     */
    public static function getByCategory(string $category): array
    {
        return array_filter(self::getAll(), function ($tool) use ($category) {
            return $tool['category'] === $category;
        });
    }

    /**
     * Get all categories that have tools
     */
    public static function getCategories(): array
    {
        $config = require __DIR__ . '/../config/app.php';
        $allCategories = $config['categories'];
        $usedCategories = [];

        foreach (self::getAll() as $tool) {
            $cat = $tool['category'];
            if (isset($allCategories[$cat]) && !isset($usedCategories[$cat])) {
                $usedCategories[$cat] = $allCategories[$cat];
            }
        }

        return $usedCategories;
    }

    /**
     * Search tools by query
     */
    public static function search(string $query): array
    {
        $query = mb_strtolower(trim($query));
        if (empty($query)) {
            return self::getAll();
        }

        $results = [];
        foreach (self::getAll() as $id => $tool) {
            $score = 0;

            // Search in name (highest weight)
            if (mb_stripos($tool['name'], $query) !== false) {
                $score += 10;
            }

            // Search in description
            if (mb_stripos($tool['description'], $query) !== false) {
                $score += 5;
            }

            // Search in tags
            foreach ($tool['tags'] ?? [] as $tag) {
                if (mb_stripos($tag, $query) !== false) {
                    $score += 3;
                }
            }

            // Search in category
            if (mb_stripos($tool['category'], $query) !== false) {
                $score += 2;
            }

            if ($score > 0) {
                $tool['_score'] = $score;
                $results[$id] = $tool;
            }
        }

        // Sort by relevance
        uasort($results, function ($a, $b) {
            return $b['_score'] - $a['_score'];
        });

        return $results;
    }

    /**
     * Scan tools directory for manifest.json files
     */
    private static function scan(): void
    {
        self::$tools = [];
        $config = require __DIR__ . '/../config/app.php';
        $toolsPath = $config['tools_path'];

        if (!is_dir($toolsPath)) {
            return;
        }

        // Scan category directories
        $categories = scandir($toolsPath);
        foreach ($categories as $category) {
            if ($category[0] === '.') continue;
            $categoryPath = $toolsPath . '/' . $category;
            if (!is_dir($categoryPath)) continue;

            // Scan tool directories within category
            $tools = scandir($categoryPath);
            foreach ($tools as $toolDir) {
                if ($toolDir[0] === '.') continue;
                $toolPath = $categoryPath . '/' . $toolDir;
                $manifestPath = $toolPath . '/manifest.json';

                if (!is_file($manifestPath)) continue;

                $manifest = json_decode(file_get_contents($manifestPath), true);
                if (!$manifest || empty($manifest['id'])) continue;

                // Add path info
                $manifest['_path'] = $toolPath;
                $manifest['_category_dir'] = $category;
                $manifest['_tool_dir'] = $toolDir;
                $manifest['_url'] = "/tool/{$category}/{$toolDir}";

                self::$tools[$manifest['id']] = $manifest;
            }
        }

        // Sort by order (if specified), then alphabetically
        uasort(self::$tools, function ($a, $b) {
            $orderA = $a['order'] ?? 100;
            $orderB = $b['order'] ?? 100;
            if ($orderA !== $orderB) return $orderA - $orderB;
            return strcmp($a['name'], $b['name']);
        });
    }

    /**
     * Force rescan
     */
    public static function refresh(): void
    {
        self::$tools = null;
    }
}
