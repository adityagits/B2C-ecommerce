<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Invoice') ?> · <?= e(config('app_name')) ?></title>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body class="print-page">
    <div class="no-print print-bar">
        <a href="javascript:history.back()">← Back</a>
        <button class="btn" onclick="window.print()">Print / Save as PDF</button>
    </div>
    <?= $content ?>
</body>
</html>
