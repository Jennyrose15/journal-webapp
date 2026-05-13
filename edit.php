<?php
include 'db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: index.php');
    exit;
}

$entry = db_get_by_id($id);
if (!$entry) {
    die("Entry not found.");
}

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = trim($_POST['title']);
    $content = trim($_POST['content']);
    $mood    = trim($_POST['mood']);

    db_update($id, $title, $content, $mood);
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Memory | MyJournal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg-color: #fcf9f5;
            --accent: #e67e22;
            --glass-bg: rgba(255, 255, 255, 0.5);
            --glass-border: rgba(255, 255, 255, 0.7);
            --text-main: #2c2c2c;
            --text-muted: #5a5a5a;
            --input-bg: rgba(255, 255, 255, 0.3);
        }

        body.dark-mode {
            --bg-color: #1a1616;
            --accent: #ff8c00;
            --glass-bg: rgba(255, 255, 255, 0.08);
            --glass-border: rgba(255, 255, 255, 0.15);
            --text-main: #ffffff;
            --text-muted: #b0a0a0;
            --input-bg: rgba(255, 255, 255, 0.05);
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            min-height: 100vh;
            display: flex; flex-direction: column;
            transition: all 0.4s ease;
            position: relative; overflow-x: hidden;
        }

        body::before {
            content: '✿'; position: fixed; top: -50px; right: -20px;
            font-size: 350px; opacity: 0.05; z-index: -1;
            pointer-events: none; color: var(--accent);
        }
        body::after {
            content: '❀'; position: fixed; bottom: -30px; left: -30px;
            font-size: 250px; opacity: 0.04; z-index: -1;
            pointer-events: none; color: var(--accent);
        }

        header {
            padding: 0 2rem; display: flex; align-items: center;
            justify-content: space-between; height: 70px;
            border-bottom: 1px solid var(--glass-border); backdrop-filter: blur(15px);
        }

        .logo { font-family: 'Playfair Display', serif; font-style: italic; font-size: 1.6rem; color: var(--text-main); text-decoration: none; }
        .logo span { color: var(--accent); }

        main { flex: 1; display: flex; align-items: center; justify-content: center; padding: 2rem; }

        .edit-card {
            background: var(--glass-bg); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border); border-radius: 20px;
            padding: 2.5rem; width: 100%; max-width: 600px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.05);
        }

        .form-title { font-family: 'Playfair Display', serif; font-size: 2rem; margin-bottom: 1.5rem; text-align: center; }

        .form-group { margin-bottom: 1.5rem; }

        label { display: block; font-size: 0.85rem; font-weight: 500; margin-bottom: 0.5rem; color: var(--text-muted); }

        input, textarea, select {
            width: 100%; padding: 0.8rem 1rem; background: var(--input-bg);
            border: 1px solid var(--glass-border); border-radius: 10px;
            color: var(--text-main); font-family: inherit; outline: none; transition: 0.3s;
        }
        input:focus, textarea:focus { border-color: var(--accent); }
        textarea { height: 150px; resize: none; line-height: 1.6; }

        .actions { display: flex; gap: 1rem; margin-top: 2rem; }

        .btn-update {
            flex: 2; background: linear-gradient(135deg, var(--accent), #d35400); color: #fff;
            border: none; padding: 1rem; border-radius: 10px; font-weight: 700;
            cursor: pointer; text-transform: uppercase; letter-spacing: 1px; transition: 0.3s;
        }
        .btn-update:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(230, 126, 34, 0.3); }

        .btn-cancel {
            flex: 1; background: transparent; border: 1px solid var(--glass-border);
            color: var(--text-main); text-decoration: none;
            display: flex; align-items: center; justify-content: center;
            border-radius: 10px; font-size: 0.8rem; font-weight: 500; transition: 0.3s;
        }
        .btn-cancel:hover { background: rgba(255,255,255,0.2); }
    </style>
</head>
<body id="body">

<header>
    <a href="index.php" class="logo">My<span>Journal</span></a>
</header>

<main>
    <div class="edit-card">
        <h1 class="form-title">Edit Memory</h1>
        
        <form action="" method="POST">
            <div class="form-group">
                <label>How's the mood?</label>
                <select name="mood">
                    <option value="happy"   <?= $entry['mood'] === 'happy'   ? 'selected' : '' ?>>😊 Happy</option>
                    <option value="sad"     <?= $entry['mood'] === 'sad'     ? 'selected' : '' ?>>😔 Sad</option>
                    <option value="angry"   <?= $entry['mood'] === 'angry'   ? 'selected' : '' ?>>😠 Angry</option>
                    <option value="tired"   <?= $entry['mood'] === 'tired'   ? 'selected' : '' ?>>😴 Tired</option>
                    <option value="excited" <?= $entry['mood'] === 'excited' ? 'selected' : '' ?>>🤩 Excited</option>
                    <option value="anxious" <?= $entry['mood'] === 'anxious' ? 'selected' : '' ?>>😰 Anxious</option>
                    <!-- Legacy moods from old SQLite data -->
                    <option value="Happy"   <?= $entry['mood'] === 'Happy'   ? 'selected' : '' ?>>😊 Happy (old)</option>
                    <option value="Sad"     <?= $entry['mood'] === 'Sad'     ? 'selected' : '' ?>>😢 Sad (old)</option>
                    <option value="Angry"   <?= $entry['mood'] === 'Angry'   ? 'selected' : '' ?>>😠 Angry (old)</option>
                    <option value="Calm"    <?= $entry['mood'] === 'Calm'    ? 'selected' : '' ?>>😌 Calm (old)</option>
                    <option value="Excited" <?= $entry['mood'] === 'Excited' ? 'selected' : '' ?>>🤩 Excited (old)</option>
                </select>
            </div>

            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" value="<?= htmlspecialchars($entry['title']) ?>" required>
            </div>

            <div class="form-group">
                <label>What happened?</label>
                <textarea name="content" required><?= htmlspecialchars($entry['content']) ?></textarea>
            </div>

            <div class="actions">
                <a href="index.php" class="btn-cancel">Cancel</a>
                <button type="submit" class="btn-update">Update Story</button>
            </div>
        </form>
    </div>
</main>

<script>
    if (localStorage.getItem('theme') === 'dark') {
        document.getElementById('body').classList.add('dark-mode');
    }
</script>

</body>
</html>