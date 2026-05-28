<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=1100, initial-scale=1">
    <title>{{ $evaluation['header']['title'] ?? 'Umfrageauswertung' }} — pr0p0ll</title>
    <style>
        html, body { margin:0; padding:0; }
        body { background:#161618; font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; padding:24px; }
    </style>
</head>
<body>
    <x-results.evaluation :evaluation="$evaluation" />
</body>
</html>
