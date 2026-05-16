<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$uploadDir = __DIR__ . '/uploads';
$maxBytes = 5 * 1024 * 1024;
$allowedMimes = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/gif' => 'gif',
    'image/webp' => 'webp',
];

$message = null;
$messageType = 'info';

if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
    http_response_code(500);
    die('Could not create uploads directory.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['image']) || !is_array($_FILES['image'])) {
        $message = 'No file was submitted.';
        $messageType = 'error';
    } elseif ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $message = match ($_FILES['image']['error']) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'File is too large.',
            UPLOAD_ERR_PARTIAL => 'Upload was interrupted. Try again.',
            UPLOAD_ERR_NO_FILE => 'Choose an image to upload.',
            default => 'Upload failed. Try again.',
        };
        $messageType = 'error';
    } elseif ($_FILES['image']['size'] > $maxBytes) {
        $message = 'Image must be 5 MB or smaller.';
        $messageType = 'error';
    } else {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($_FILES['image']['tmp_name']);

        if ($mime === false || !isset($allowedMimes[$mime])) {
            $message = 'Only JPEG, PNG, GIF, and WebP images are allowed.';
            $messageType = 'error';
        } else {
            $extension = $allowedMimes[$mime];
            $filename = bin2hex(random_bytes(16)) . '.' . $extension;
            $destination = $uploadDir . '/' . $filename;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                header('Location: upload.php?uploaded=' . urlencode($filename));
                exit;
            }

            $message = 'Could not save the file. Check directory permissions.';
            $messageType = 'error';
        }
    }
}

if (isset($_GET['uploaded'])) {
    $uploadedName = basename((string) $_GET['uploaded']);
    $uploadedPath = $uploadDir . '/' . $uploadedName;

    if (is_file($uploadedPath)) {
        $message = 'Uploaded successfully.';
        $messageType = 'success';
        $justUploaded = $uploadedName;
    }
}

$existingImages = [];
foreach (glob($uploadDir . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE) ?: [] as $path) {
    $existingImages[] = basename($path);
}
rsort($existingImages);
?>

<!DOCTYPE html>
<html lang="en" class="h-full antialiased">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Upload · Test App</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'system-ui', 'sans-serif'],
                    },
                    boxShadow: {
                        glow: '0 0 0 1px rgba(99, 102, 241, 0.08), 0 20px 50px -12px rgba(15, 23, 42, 0.25)',
                    },
                },
            },
        };
    </script>
</head>

<body class="min-h-full bg-slate-950 bg-[radial-gradient(ellipse_120%_80%_at_50%_-20%,rgba(99,102,241,0.35),transparent)] font-sans text-slate-100">

    <div class="mx-auto flex min-h-full max-w-3xl flex-col px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
        <header class="mb-10 text-center sm:mb-12">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-indigo-400/90">Storage test</p>
            <h1 class="mt-3 text-3xl font-bold tracking-tight text-white sm:text-4xl">Image upload</h1>
            <p class="mx-auto mt-3 max-w-lg text-sm leading-relaxed text-slate-400">
                Files are saved to <code class="rounded bg-slate-800/80 px-1.5 py-0.5 font-mono text-xs text-indigo-200">uploads/</code>
                and served from <code class="rounded bg-slate-800/80 px-1.5 py-0.5 font-mono text-xs text-indigo-200">/uploads/</code>.
            </p>
            <p class="mt-4">
                <a href="index.php" class="text-sm font-medium text-indigo-300 hover:text-indigo-200">← Back to comments</a>
            </p>
        </header>

        <main class="flex-1 space-y-8">
            <div class="rounded-2xl border border-white/10 bg-slate-900/60 p-1 shadow-glow backdrop-blur-md">
                <div class="rounded-[14px] border border-white/5 bg-slate-900/80 p-6 sm:p-8">
                    <?php if ($message !== null): ?>
                        <div class="mb-6 rounded-xl border px-4 py-3 text-sm <?= $messageType === 'success'
                            ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-200'
                            : ($messageType === 'error'
                                ? 'border-rose-500/30 bg-rose-500/10 text-rose-200'
                                : 'border-indigo-500/30 bg-indigo-500/10 text-indigo-200') ?>">
                            <?= htmlspecialchars($message) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($justUploaded)): ?>
                        <div class="mb-6 overflow-hidden rounded-xl border border-white/10 bg-slate-950/50 p-4">
                            <p class="mb-3 text-sm font-medium text-slate-300">Latest upload</p>
                            <img
                                src="/uploads/<?= htmlspecialchars($justUploaded) ?>"
                                alt="Uploaded image"
                                class="mx-auto max-h-80 rounded-lg object-contain"
                            >
                            <p class="mt-3 break-all font-mono text-xs text-slate-500"><?= htmlspecialchars($justUploaded) ?></p>
                        </div>
                    <?php endif; ?>

                    <form method="post" enctype="multipart/form-data" class="space-y-5">
                        <div>
                            <label for="image" class="mb-2 block text-sm font-medium text-slate-300">Choose an image</label>
                            <input
                                type="file"
                                name="image"
                                id="image"
                                accept="image/jpeg,image/png,image/gif,image/webp"
                                required
                                class="block w-full cursor-pointer rounded-xl border border-white/10 bg-slate-950/80 px-4 py-3 text-sm text-slate-300 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-500/20 file:px-4 file:py-2 file:text-sm file:font-medium file:text-indigo-200 hover:file:bg-indigo-500/30"
                            >
                            <p class="mt-2 text-xs text-slate-500">JPEG, PNG, GIF, or WebP · max 5 MB</p>
                        </div>
                        <button
                            type="submit"
                            class="w-full rounded-xl bg-indigo-500 px-4 py-3 text-sm font-semibold text-white transition hover:bg-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-2 focus:ring-offset-slate-900"
                        >
                            Upload
                        </button>
                    </form>
                </div>
            </div>

            <?php if (!empty($existingImages)): ?>
                <div class="rounded-2xl border border-white/10 bg-slate-900/60 p-1 shadow-glow backdrop-blur-md">
                    <div class="rounded-[14px] border border-white/5 bg-slate-900/80 p-6 sm:p-8">
                        <h2 class="text-lg font-semibold text-white">Uploaded images</h2>
                        <p class="mt-1 text-sm text-slate-400"><?= count($existingImages) ?> file<?= count($existingImages) === 1 ? '' : 's' ?> in volume</p>
                        <ul class="mt-6 grid gap-4 sm:grid-cols-2">
                            <?php foreach ($existingImages as $name): ?>
                                <li class="overflow-hidden rounded-xl border border-white/10 bg-slate-950/50">
                                    <a href="/uploads/<?= htmlspecialchars($name) ?>" target="_blank" rel="noopener">
                                        <img
                                            src="/uploads/<?= htmlspecialchars($name) ?>"
                                            alt=""
                                            class="aspect-video w-full object-cover"
                                            loading="lazy"
                                        >
                                    </a>
                                    <p class="truncate px-3 py-2 font-mono text-xs text-slate-500"><?= htmlspecialchars($name) ?></p>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
        </main>

        <footer class="mt-10 text-center text-xs text-slate-500">
            Redeploy and revisit this page to confirm Dokploy volume persistence.
        </footer>
    </div>

</body>

</html>
