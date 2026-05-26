<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Documentation Portal</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f6f7fb; color: #1f2937; }
        .container { max-width: 980px; margin: 32px auto; padding: 0 16px; }
        .card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 20px; margin-bottom: 16px; }
        .title { margin: 0 0 8px; font-size: 28px; }
        .subtitle { margin: 0; color: #6b7280; }
        .pill { display: inline-block; font-size: 12px; background: #e0e7ff; color: #3730a3; padding: 4px 8px; border-radius: 999px; margin-top: 12px; }
        .group-title { margin: 0 0 6px; font-size: 20px; }
        .group-description { margin: 0 0 12px; color: #4b5563; }
        .paths { margin: 0 0 12px 18px; color: #374151; }
        .actions a { text-decoration: none; color: #fff; background: #111827; padding: 8px 12px; border-radius: 8px; display: inline-block; margin-right: 8px; }
        .muted-link { color: #374151; background: #f3f4f6; }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <h1 class="title">API Documentation</h1>
        <p class="subtitle">Available API groups for your account.</p>
        @if($hasFullAccess)
            <span class="pill">Full documentation access enabled</span>
        @endif
        <div class="actions" style="margin-top:16px;">
            <a href="{{ $docsUiUrl }}">Open Swagger UI</a>
            <a class="muted-link" href="{{ $docsJsonUrl }}">Open OpenAPI JSON</a>
            <a class="muted-link" href="{{ $filteredDocsJsonUrl }}">Open filtered API spec</a>
        </div>
    </div>

    @if(count($visibleGroups) === 0)
        <div class="card">
            <h2 class="group-title">No available API groups</h2>
            <p class="group-description">
                You have access to API documentation, but no API groups are available for your current permissions.
            </p>
        </div>
    @else
        @foreach($visibleGroups as $groupKey => $group)
            <div class="card" data-group="{{ $groupKey }}">
                <h2 class="group-title">{{ $group['label'] ?? $groupKey }}</h2>
                <p class="group-description">{{ $group['description'] ?? '' }}</p>
                @if(!empty($group['paths']) && is_array($group['paths']))
                    <ul class="paths">
                        @foreach($group['paths'] as $pathPattern)
                            <li>{{ $pathPattern }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endforeach
    @endif
</div>
</body>
</html>
