<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'lib/env.php';
// Database credentials
$host    = $_ENV['DB_HOST'];
$db      = $_ENV['DB_NAME'];
$user    = $_ENV['DB_USER'];
$pass    = $_ENV['DB_PASS'];
$charset = 'utf8mb4';

// Data Source Name
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// PDO configuration options
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Create PDO instance
    $pdo = new PDO($dsn, $user, $pass, $options);

    // SQL query
    $sql = "SELECT id, comment, created_at FROM test";
    $stmt = $pdo->query($sql);

    // Fetch all records
    $items = $stmt->fetchAll();
} catch (PDOException $e) {
    // Handle connection errors safely
    echo "Database connection failed: " . htmlspecialchars($e->getMessage());
    exit;
}
?>

<!DOCTYPE html>
<html lang="en" class="h-full antialiased">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Comments · Test App</title>
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

    <div class="mx-auto flex min-h-full max-w-5xl flex-col px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
        <header class="mb-10 text-center sm:mb-12">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-indigo-400/90">MySQL · PDO</p>
            <h1 class="mt-3 text-3xl font-bold tracking-tight text-white sm:text-4xl">Comments</h1>
            <p class="mx-auto mt-3 max-w-lg text-sm leading-relaxed text-slate-400">
                Rows from the <code class="rounded bg-slate-800/80 px-1.5 py-0.5 font-mono text-xs text-indigo-200">test</code> table.
            </p>
        </header>

        <main class="flex-1">
            <div class="rounded-2xl border border-white/10 bg-slate-900/60 p-1 shadow-glow backdrop-blur-md">
                <div class="rounded-[14px] border border-white/5 bg-slate-900/80 p-6 sm:p-8">
                    <?php if (!empty($items)): ?>
                        <div class="mb-6 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <h2 class="text-lg font-semibold text-white">All records</h2>
                                <p class="text-sm text-slate-400"><?= count($items) ?> <?= count($items) === 1 ? 'row' : 'rows' ?></p>
                            </div>
                        </div>

                        <div class="overflow-hidden rounded-xl border border-white/10">
                            <div class="overflow-x-auto">
                                <table class="w-full min-w-[520px] text-left text-sm">
                                    <thead>
                                        <tr class="border-b border-white/10 bg-slate-950/80">
                                            <th scope="col" class="whitespace-nowrap px-4 py-3 font-semibold text-slate-300 sm:px-5">ID</th>
                                            <th scope="col" class="px-4 py-3 font-semibold text-slate-300 sm:px-5">Comment</th>
                                            <th scope="col" class="whitespace-nowrap px-4 py-3 font-semibold text-slate-300 sm:px-5">Created</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-white/5">
                                        <?php foreach ($items as $item): ?>
                                            <tr class="transition-colors hover:bg-white/[0.03]">
                                                <td class="whitespace-nowrap px-4 py-3.5 sm:px-5">
                                                    <span class="inline-flex rounded-full bg-indigo-500/15 px-2.5 py-0.5 text-xs font-medium tabular-nums text-indigo-300 ring-1 ring-inset ring-indigo-400/20">
                                                        <?= htmlspecialchars((string) $item['id']) ?>
                                                    </span>
                                                </td>
                                                <td class="max-w-md px-4 py-3.5 text-slate-200 sm:max-w-xl sm:px-5">
                                                    <?= htmlspecialchars((string) $item['comment']) ?>
                                                </td>
                                                <td class="whitespace-nowrap px-4 py-3.5 font-mono text-xs text-slate-400 sm:px-5">
                                                    <?= htmlspecialchars((string) $item['created_at']) ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="flex flex-col items-center justify-center py-16 text-center">
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-500/10 ring-1 ring-indigo-400/20">
                                <svg class="h-7 w-7 text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                </svg>
                            </div>
                            <h2 class="mt-6 text-lg font-semibold text-white">No rows yet</h2>
                            <p class="mt-2 max-w-sm text-sm leading-relaxed text-slate-400">
                                The <code class="rounded bg-slate-800 px-1 py-0.5 font-mono text-xs text-slate-300">test</code> table returned no records.
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>

        <footer class="mt-10 text-center text-xs text-slate-500">
            <a href="upload.php" class="text-indigo-400 hover:text-indigo-300">Test image upload</a>
            <span class="mx-2 text-slate-600">·</span>
            Rendered with PHP &middot; Styled with Tailwind CSS
        </footer>
    </div>

</body>

</html>