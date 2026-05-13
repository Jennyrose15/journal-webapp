<?php

date_default_timezone_set('Asia/Manila');

define('DB_FILE', __DIR__ . '/journal.json');

/**
 * Load all entries from the JSON file.
 * Returns an array of entry associative arrays.
 */
function db_load(): array {
    if (!file_exists(DB_FILE)) {
        file_put_contents(DB_FILE, json_encode(['entries' => [], 'next_id' => 1], JSON_PRETTY_PRINT));
    }
    $data = json_decode(file_get_contents(DB_FILE), true);
    return $data ?? ['entries' => [], 'next_id' => 1];
}

/**
 * Save the full data array back to the JSON file.
 */
function db_save(array $data): void {
    file_put_contents(DB_FILE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

/**
 * Get all entries, sorted by created_at descending.
 */
function db_get_all(string $search = ''): array {
    $data = db_load();
    $entries = $data['entries'];

    if ($search !== '') {
        $search_lower = strtolower($search);
        $entries = array_filter($entries, function($e) use ($search_lower) {
            return str_contains(strtolower($e['title']), $search_lower)
                || str_contains(strtolower($e['content']), $search_lower);
        });
    }

    // Sort by created_at descending
    usort($entries, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));

    return array_values($entries);
}

/**
 * Get a single entry by ID. Returns null if not found.
 */
function db_get_by_id(int $id): ?array {
    $data = db_load();
    foreach ($data['entries'] as $entry) {
        if ((int)$entry['id'] === $id) {
            return $entry;
        }
    }
    return null;
}

/**
 * Insert a new entry. Returns the new entry's ID.
 */
function db_insert(string $title, string $content, string $mood): int {
    $data = db_load();
    $id = (int)($data['next_id'] ?? 1);

    $data['entries'][] = [
        'id'         => $id,
        'title'      => $title,
        'content'    => $content,
        'mood'       => $mood,
        'created_at' => date('Y-m-d H:i:s'),
    ];

    $data['next_id'] = $id + 1;
    db_save($data);
    return $id;
}

/**
 * Update an existing entry by ID. Returns true on success.
 */
function db_update(int $id, string $title, string $content, string $mood): bool {
    $data = db_load();
    foreach ($data['entries'] as &$entry) {
        if ((int)$entry['id'] === $id) {
            $entry['title']   = $title;
            $entry['content'] = $content;
            $entry['mood']    = $mood;
            db_save($data);
            return true;
        }
    }
    return false;
}

/**
 * Delete an entry by ID. Returns true on success.
 */
function db_delete(int $id): bool {
    $data = db_load();
    $original_count = count($data['entries']);
    $data['entries'] = array_values(
        array_filter($data['entries'], fn($e) => (int)$e['id'] !== $id)
    );
    if (count($data['entries']) < $original_count) {
        db_save($data);
        return true;
    }
    return false;
}