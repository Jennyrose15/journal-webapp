<?php
include 'db.php';

// Search + fetch entries
$search  = isset($_GET['search']) ? trim($_GET['search']) : '';
$entries = db_get_all($search);

// Helper: Mood Emojis
function getMoodEmoji($mood) {
    $moods = [
        'Happy'   => '😊',
        'Sad'     => '😢',
        'Angry'   => '😠',
        'Calm'    => '😌',
        'Excited' => '🤩',
        'happy'   => '😊',
        'sad'     => '😔',
        'angry'   => '😠',
        'tired'   => '😴',
        'excited' => '🤩',
        'anxious' => '😰',
    ];
    return $moods[$mood] ?? '📝';
}

// Helper: Time Ago
function timeAgo($timestamp) {
    $time_ago        = strtotime($timestamp);
    $current_time    = time();
    $time_difference = $current_time - $time_ago;
    $seconds         = $time_difference;

    $minutes = round($seconds / 60);
    $hours   = round($seconds / 3600);
    $days    = round($seconds / 86400);

    if ($seconds <= 60) return "Just now";
    if ($minutes <= 60) return ($minutes == 1) ? "1 minute ago" : "$minutes minutes ago";
    if ($hours   <= 24) return ($hours   == 1) ? "1 hour ago"   : "$hours hours ago";
    if ($days    <= 7)  return ($days    == 1) ? "Yesterday"    : "$days days ago";
    return date('M d, Y', $time_ago);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Journal | Glass Edition</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg-color: #1a1616;
            --accent: #ff8c00;
            --glass-bg: rgba(255, 255, 255, 0.07);
            --glass-border: rgba(255, 255, 255, 0.15);
            --text-main: #ffffff;
            --text-muted: #b0a0a0;
            --header-bg: rgba(26, 22, 22, 0.9);
        }

        body.light-mode {
            --bg-color: #fdfaf7;
            --accent: #e67e22;
            --glass-bg: rgba(255, 255, 255, 0.7);
            --glass-border: rgba(0, 0, 0, 0.1);
            --text-main: #2c2c2c;
            --text-muted: #666;
            --header-bg: rgba(253, 250, 247, 0.9);
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background-color: var(--bg-color);
            background-image: 
                radial-gradient(circle at 5% 30%, rgba(255, 140, 0, 0.1) 0%, transparent 25%),
                radial-gradient(circle at 95% 70%, rgba(255, 140, 0, 0.08) 0%, transparent 25%);
            color: var(--text-main);
            min-height: 100vh;
            transition: all 0.3s ease;
        }

        header {
            background: var(--header-bg);
            backdrop-filter: blur(10px);
            padding: 0 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 70px;
            position: sticky; top: 0; z-index: 100;
            border-bottom: 1px solid var(--glass-border);
        }

        .logo { font-family: 'Playfair Display', serif; font-style: italic; font-size: 1.6rem; color: var(--text-main); }
        .logo span { color: var(--accent); }

        .header-actions { display: flex; gap: 1rem; align-items: center; }

        .mode-btn {
            background: var(--glass-bg); border: 1px solid var(--glass-border); color: var(--text-main);
            padding: 0.5rem 1rem; border-radius: 50px; cursor: pointer; font-size: 0.8rem;
        }

        .new-btn {
            background: linear-gradient(135deg, var(--accent), #d35400); color: #fff;
            text-decoration: none; font-size: .75rem; font-weight: 700; text-transform: uppercase;
            padding: .6rem 1.2rem; border-radius: 8px; box-shadow: 0 4px 15px rgba(255, 140, 0, 0.2);
        }

        main { max-width: 1100px; margin: 0 auto; padding: 3rem 1.5rem; }
        .page-title { font-family: 'Playfair Display', serif; font-size: 2.5rem; margin-bottom: .3rem; }
        .page-sub { color: var(--text-muted); margin-bottom: 2.5rem; font-size: 0.9rem; }

        .search-wrap { position: relative; margin-bottom: 3rem; }
        .search-input {
            width: 100%; padding: 1rem 1rem 1rem 3.2rem; background: var(--glass-bg);
            border: 1px solid var(--glass-border); border-radius: 10px; color: var(--text-main);
            outline: none; font-size: 0.95rem;
        }
        .search-wrap svg { position: absolute; left: 1.2rem; top: 50%; transform: translateY(-50%); color: var(--accent); }

        .entries-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.8rem; }

        .entry-card {
            background: var(--glass-bg); backdrop-filter: blur(12px);
            border: 1px solid var(--glass-border); border-radius: 12px;
            padding: 1.5rem; display: flex; flex-direction: column; transition: all 0.3s ease;
            position: relative;
        }
        .entry-card:hover { transform: translateY(-5px); border-color: var(--accent); }

        .entry-card::after {
            content: ''; position: absolute; top: 0; left: 10%; width: 80%; height: 2px;
            background: linear-gradient(90deg, transparent, var(--accent), transparent); opacity: 0.5;
        }

        .card-header-meta { display: flex; justify-content: space-between; margin-bottom: 1.2rem; font-size: 0.75rem; color: var(--text-muted); }

        .mood-badge {
            background: rgba(255, 140, 0, 0.15); color: var(--accent);
            padding: 3px 10px; border-radius: 50px; border: 1px solid rgba(255, 140, 0, 0.3);
        }

        .card-title { font-family: 'Playfair Display', serif; font-size: 1.4rem; margin-bottom: 0.8rem; color: var(--text-main); }
        .card-excerpt { font-size: .92rem; color: var(--text-muted); line-height: 1.6; margin-bottom: 1.5rem; word-wrap: break-word; }

        .card-footer { margin-top: auto; display: flex; justify-content: flex-end; align-items: center; }
        .card-actions { display: flex; gap: 0.6rem; }

        .btn-edit, .btn-del {
            text-decoration: none; font-size: 0.7rem; font-weight: 700; padding: 0.4rem 0.9rem; border-radius: 4px; text-transform: uppercase;
        }
        .btn-edit { border: 1px solid var(--text-main); color: var(--text-main); }
        .btn-edit:hover { background: var(--text-main); color: var(--bg-color); }
        .btn-del { border: 1px solid var(--accent); color: var(--accent); }
        .btn-del:hover { background: var(--accent); color: #fff; }

        mark { background: var(--accent); color: #fff; padding: 0 2px; }
    </style>
</head>
<body id="body">

<header>
    <div class="logo">My<span>Journal</span></div>
    <div class="header-actions">
        <button onclick="toggleMode()" class="mode-btn" id="modeBtn">🌙 Mode</button>
        <a href="create.php" class="new-btn">+ NEW ENTRY</a>
    </div>
</header>

<main>
    <h1 class="page-title">Anong thought's mo?</h1>
    <p class="page-sub">Record your life, one day at a time.</p>

    <form class="search-wrap" method="GET" action="index.php">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        <input type="text" name="search" class="search-input" placeholder="Search entries..." value="<?= htmlspecialchars($search) ?>">
    </form>

    <div class="entries-list">
        <?php if (empty($entries)): ?>
            <div style="grid-column: 1 / -1; text-align: center; color: var(--text-muted);">
                <h3>No entries found.</h3>
            </div>
        <?php else: ?>
            <?php foreach ($entries as $entry): ?>
                <div class="entry-card">
                    <div class="card-header-meta">
                        <span><?= timeAgo($entry['created_at']) ?></span>
                        <span class="mood-badge"><?= getMoodEmoji($entry['mood']) ?> <?= htmlspecialchars(ucfirst($entry['mood'])) ?></span>
                    </div>
                    
                    <h2 class="card-title"><?= htmlspecialchars($entry['title']) ?></h2>
                    
                    <div class="card-excerpt">
                        <?= nl2br(htmlspecialchars($entry['content'])) ?>
                    </div>
                    
                    <div class="card-footer">
                        <div class="card-actions">
                            <a href="edit.php?id=<?= $entry['id'] ?>" class="btn-edit">Edit</a>
                            <a href="delete.php?id=<?= $entry['id'] ?>" class="btn-del" onclick="return confirm('Delete?')">Delete</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<script>
    function toggleMode() {
        const body = document.getElementById('body');
        const btn  = document.getElementById('modeBtn');
        body.classList.toggle('light-mode');
        btn.innerHTML = body.classList.contains('light-mode') ? "☀️ Light" : "🌙 Mode";
        localStorage.setItem('theme', body.classList.contains('light-mode') ? 'light' : 'dark');
    }
    if (localStorage.getItem('theme') === 'light') {
        document.getElementById('body').classList.add('light-mode');
        document.getElementById('modeBtn').innerHTML = "☀️ Light";
    }
</script>
</body>
</html>