<?php
include 'db.php';

$moods = [
    'happy'   => ['emoji' => '😊', 'label' => 'Happy',   'color' => '#f59e0b'],
    'sad'     => ['emoji' => '😔', 'label' => 'Sad',     'color' => '#3b82f6'],
    'angry'   => ['emoji' => '😠', 'label' => 'Angry',   'color' => '#ef4444'],
    'tired'   => ['emoji' => '😴', 'label' => 'Tired',   'color' => '#8b5cf6'],
    'excited' => ['emoji' => '🤩', 'label' => 'Excited', 'color' => '#10b981'],
    'anxious' => ['emoji' => '😰', 'label' => 'Anxious', 'color' => '#f97316'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = trim($_POST['title']);
    $content = trim($_POST['content']);
    $mood    = isset($moods[$_POST['mood']]) ? $_POST['mood'] : 'happy';

    // SQLite: CURRENT_TIMESTAMP 
    $stmt = $conn->prepare("INSERT INTO entries (title, content, mood) VALUES (?, ?, ?)");
    if ($stmt->execute([$title, $content, $mood])) {
        header("Location: index.php");
        exit();
    } else {
        $error = "May error sa pag-save. Subukan ulit.";
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Entry — MyJournal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --ink:       #1a1118;
            --muted:     #6b5c6e;
            --line:      #e2d9e5;
            --page:      #faf7f5;
            --cream:     #f3ede8;
            --card-bg:   #ffffff;
            --header-bg: #1a1118;
            --accent:    #c0392b;
            --gold:      #b8860b;
            --input-bg:  #faf7f5;
            --shadow:    rgba(0,0,0,.07);
        }

        [data-theme="dark"] {
            --ink:       #f0e8f0;
            --muted:     #9a8a9d;
            --line:      #2a1e2e;
            --page:      #120d14;
            --cream:     #1a1220;
            --card-bg:   #1d1524;
            --header-bg: #0d090f;
            --accent:    #e05c4b;
            --gold:      #d4a017;
            --input-bg:  #150f18;
            --shadow:    rgba(0,0,0,.4);
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--page);
            color: var(--ink);
            min-height: 100vh;
            transition: background .3s, color .3s;
        }

        /* ── Header ── */
        header {
            background: var(--header-bg);
            padding: 0 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 64px;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 14px rgba(0,0,0,.35);
            transition: background .3s;
        }

        .logo {
            font-family: 'Playfair Display', serif;
            font-style: italic;
            font-size: 1.5rem;
            color: #fff;
            text-decoration: none;
            letter-spacing: .02em;
        }
        .logo span { color: var(--accent); }

        .header-right { display: flex; align-items: center; gap: 1rem; }

        .theme-toggle {
            position: relative;
            width: 56px; height: 28px;
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.18);
            border-radius: 14px;
            cursor: pointer;
            flex-shrink: 0;
            transition: background .3s;
        }
        .theme-toggle::after {
            content: '';
            position: absolute;
            top: 3px; left: 3px;
            width: 22px; height: 22px;
            border-radius: 50%;
            background: #fff;
            transition: transform .3s, background .3s;
        }
        [data-theme="dark"] .theme-toggle::after {
            transform: translateX(28px);
            background: var(--accent);
        }
        .toggle-icon {
            position: absolute;
            top: 50%; transform: translateY(-50%);
            font-size: .72rem;
            pointer-events: none;
            line-height: 1;
        }
        .toggle-sun  { left: 6px; }
        .toggle-moon { right: 5px; }

        .back-link {
            color: #bbb;
            text-decoration: none;
            font-size: .82rem;
            font-weight: 500;
            letter-spacing: .06em;
            text-transform: uppercase;
            display: flex;
            align-items: center;
            gap: .4rem;
            transition: color .2s;
        }
        .back-link:hover { color: #fff; }

        /* ── Main ── */
        main {
            max-width: 680px;
            margin: 0 auto;
            padding: 3rem 1.5rem 5rem;
            animation: fadeUp .38s ease both;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .breadcrumb {
            font-size: .78rem;
            color: var(--muted);
            margin-bottom: 1.8rem;
            display: flex;
            align-items: center;
            gap: .4rem;
        }
        .breadcrumb a { color: var(--muted); text-decoration: none; }
        .breadcrumb a:hover { color: var(--ink); }

        /* ── Card ── */
        .card {
            background: var(--card-bg);
            border: 1px solid var(--line);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 24px var(--shadow);
            transition: background .3s, border-color .3s;
        }
        .card-top-bar {
            height: 4px;
            background: linear-gradient(90deg, var(--accent), var(--gold));
        }
        .card-inner { padding: 2.2rem 2.4rem 2rem; }

        .form-heading {
            font-family: 'Playfair Display', serif;
            font-size: 1.7rem;
            font-weight: 700;
            margin-bottom: .3rem;
        }
        .form-sub {
            font-size: .85rem;
            color: var(--muted);
            margin-bottom: 1.8rem;
        }

        .divider {
            border: none;
            border-top: 1px solid var(--line);
            margin: 0 0 1.8rem;
            transition: border-color .3s;
        }

        /* ── Error ── */
        .alert-error {
            background: rgba(192,57,43,.08);
            border: 1px solid rgba(192,57,43,.25);
            border-radius: 5px;
            padding: .8rem 1rem;
            font-size: .88rem;
            color: var(--accent);
            margin-bottom: 1.4rem;
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        /* ── Fields ── */
        .field { margin-bottom: 1.5rem; }

        label {
            display: block;
            font-size: .78rem;
            font-weight: 600;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: .5rem;
        }

        input[type="text"], textarea {
            width: 100%;
            font-family: 'DM Sans', sans-serif;
            font-size: .95rem;
            color: var(--ink);
            background: var(--input-bg);
            border: 1.5px solid var(--line);
            border-radius: 6px;
            padding: .85rem 1rem;
            outline: none;
            transition: border-color .2s, box-shadow .2s, background .3s, color .3s;
            resize: none;
        }
        input[type="text"]:focus, textarea:focus {
            border-color: var(--accent);
            background: var(--card-bg);
            box-shadow: 0 0 0 3px rgba(192,57,43,.1);
        }
        textarea { min-height: 200px; line-height: 1.7; }

        .field-meta { display: flex; justify-content: flex-end; margin-top: .35rem; }
        .char-count { font-size: .73rem; color: var(--muted); }

        /* ── Mood picker ── */
        .mood-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: .6rem;
        }
        .mood-option { display: none; }
        .mood-label {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: .3rem;
            padding: .75rem .5rem;
            border: 2px solid var(--line);
            border-radius: 8px;
            cursor: pointer;
            transition: border-color .2s, background .2s, transform .15s;
            background: var(--input-bg);
            user-select: none;
        }
        .mood-label:hover { transform: translateY(-2px); }
        .mood-emoji-lg { font-size: 1.5rem; line-height: 1; }
        .mood-text {
            font-size: .7rem;
            font-weight: 600;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--muted);
            transition: color .2s;
        }

        /* ── Buttons ── */
        .actions { display: flex; gap: .75rem; margin-top: 2rem; }

        .btn-save {
            flex: 1;
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: .9rem 1.5rem;
            font-family: 'DM Sans', sans-serif;
            font-size: .85rem;
            font-weight: 500;
            letter-spacing: .08em;
            text-transform: uppercase;
            cursor: pointer;
            transition: background .2s, transform .15s;
        }
        .btn-save:hover { background: #a93226; transform: translateY(-1px); }

        .btn-cancel {
            flex: 1;
            background: transparent;
            color: var(--muted);
            border: 1.5px solid var(--line);
            border-radius: 5px;
            padding: .9rem 1.5rem;
            font-family: 'DM Sans', sans-serif;
            font-size: .85rem;
            font-weight: 500;
            letter-spacing: .08em;
            text-transform: uppercase;
            text-decoration: none;
            text-align: center;
            transition: border-color .2s, color .2s;
        }
        .btn-cancel:hover { border-color: var(--ink); color: var(--ink); }

        @media (max-width: 600px) {
            header { padding: 0 1rem; }
            main   { padding: 2rem 1rem 4rem; }
            .card-inner { padding: 1.6rem 1.4rem 1.4rem; }
            .form-heading { font-size: 1.35rem; }
            .actions { flex-direction: column; }
        }
    </style>
</head>
<body>

<header>
    <a href="index.php" class="logo">My<span>Journal</span></a>
    <div class="header-right">
        <button class="theme-toggle" id="themeToggle" aria-label="Toggle dark mode">
            <span class="toggle-icon toggle-sun">☀️</span>
            <span class="toggle-icon toggle-moon">🌙</span>
        </button>
        <a href="index.php" class="back-link">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>
            </svg>
            Back
        </a>
    </div>
</header>

<main>
    <div class="breadcrumb">
        <a href="index.php">All Entries</a>
        <span>›</span>
        <span>New Entry</span>
    </div>

    <div class="card">
        <div class="card-top-bar"></div>
        <div class="card-inner">

            <h1 class="form-heading">New Entry</h1>
            <p class="form-sub">What's on your mind today?</p>

            <hr class="divider">

            <?php if (!empty($error)): ?>
                <div class="alert-error">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form action="create.php" method="POST">

                <!-- Title -->
                <div class="field">
                    <label for="title">Title</label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        placeholder="Enter Here"
                        required
                        maxlength="200"
                        autofocus
                        oninput="updateCount('tc', this.value.length, 200)"
                    >
                    <div class="field-meta">
                        <span class="char-count" id="tc">0/200</span>
                    </div>
                </div>

                <!-- Mood picker -->
                <div class="field">
                    <label>How are you feeling?</label>
                    <div class="mood-grid">
                        <?php foreach ($moods as $key => $m):
                            $checked = $key === 'happy' ? 'checked' : '';
                        ?>
                        <div>
                            <input type="radio" class="mood-option" name="mood" id="mood_<?= $key ?>" value="<?= $key ?>" <?= $checked ?>>
                            <label class="mood-label" for="mood_<?= $key ?>" data-color="<?= $m['color'] ?>">
                                <span class="mood-emoji-lg"><?= $m['emoji'] ?></span>
                                <span class="mood-text"><?= $m['label'] ?></span>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Content -->
                <div class="field">
                    <label for="content">Content</label>
                    <textarea
                        id="content"
                        name="content"
                        placeholder="Write your thoughts here"
                        required
                        maxlength="5000"
                        oninput="autoResize(this); updateCount('cc', this.value.length, 5000)"
                    ></textarea>
                    <div class="field-meta">
                        <span class="char-count" id="cc">0/5000</span>
                    </div>
                </div>

                <div class="actions">
                    <button type="submit" class="btn-save">Save Entry</button>
                    <a href="index.php" class="btn-cancel">Cancel</a>
                </div>
            </form>

        </div>
    </div>
</main>

<script>
    // ── Dark mode ─────────────────────────────────────────
    const html   = document.documentElement;
    const toggle = document.getElementById('themeToggle');
    html.setAttribute('data-theme', localStorage.getItem('journal-theme') || 'light');
    toggle.addEventListener('click', () => {
        const next = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-theme', next);
        localStorage.setItem('journal-theme', next);
    });

    // ── Mood highlight ────────────────────────────────────
    function applyMoodStyles() {
        document.querySelectorAll('.mood-option').forEach(radio => {
            const label = radio.nextElementSibling;
            const color = label.dataset.color;
            if (radio.checked) {
                label.style.borderColor = color;
                label.style.background  = color + '18';
                label.querySelector('.mood-text').style.color = color;
            } else {
                label.style.borderColor = '';
                label.style.background  = '';
                label.querySelector('.mood-text').style.color = '';
            }
        });
    }
    document.querySelectorAll('.mood-option').forEach(r => r.addEventListener('change', applyMoodStyles));
    applyMoodStyles();

    // ── Helpers ───────────────────────────────────────────
    function updateCount(id, len, max) {
        const el = document.getElementById(id);
        el.textContent = len + '/' + max;
        el.style.color = len > max * .88 ? 'var(--accent)' : '';
    }

    function autoResize(el) {
        el.style.height = 'auto';
        el.style.height = Math.max(200, el.scrollHeight) + 'px';
    }
</script>
</body>
</html>