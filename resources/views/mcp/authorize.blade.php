@php
    /** @var \Laravel\Passport\Client $client */
    $redirectHosts = collect($client->redirect_uris)
        ->map(fn (string $uri): string => parse_url($uri, PHP_URL_HOST) ?: $uri)
        ->unique()
        ->implode(', ');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Connect {{ $client->name }} · {{ $siteName }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <style>
        :root {
            color-scheme: light dark;
            --bg: #f4f4f5;
            --card: #ffffff;
            --border: #e4e4e7;
            --text: #18181b;
            --muted: #71717a;
            --primary: #4f46e5;
            --primary-text: #ffffff;
            --note: #fef3c7;
            --note-text: #78350f;
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --bg: #09090b;
                --card: #18181b;
                --border: #27272a;
                --text: #fafafa;
                --muted: #a1a1aa;
                --primary: #818cf8;
                --primary-text: #09090b;
                --note: #422006;
                --note-text: #fde68a;
            }
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px 16px;
            background: var(--bg);
            color: var(--text);
            font: 15px/1.55 ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
        }

        main {
            width: 100%;
            max-width: 440px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 28px;
        }

        h1 { font-size: 20px; line-height: 1.3; margin: 0 0 6px; }
        p { margin: 0; }
        .muted { color: var(--muted); font-size: 14px; }
        .section { margin-top: 20px; }
        .label { font-size: 12px; text-transform: uppercase; letter-spacing: .06em; color: var(--muted); margin-bottom: 6px; }
        ul { margin: 0; padding-left: 18px; }
        li + li { margin-top: 4px; }
        .note { margin-top: 20px; padding: 10px 12px; border-radius: 8px; background: var(--note); color: var(--note-text); font-size: 13px; }
        .actions { display: flex; gap: 12px; margin-top: 24px; }
        .actions form { flex: 1; margin: 0; }

        button {
            width: 100%;
            height: 42px;
            border-radius: 8px;
            font: inherit;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid var(--border);
            background: transparent;
            color: var(--text);
        }

        button.primary { background: var(--primary); border-color: var(--primary); color: var(--primary-text); }
        button:focus-visible { outline: 2px solid var(--primary); outline-offset: 2px; }
        code { font-size: 13px; }
    </style>
</head>
<body>
<main>
    <h1>Connect {{ $client->name }} to {{ $siteName }}?</h1>
    <p class="muted">Signed in as {{ $user->email }}</p>

    <div class="section">
        <div class="label">It will be able to</div>
        <ul>
            <li>Read your portfolio: profile, projects, experience, companies and skills.</li>
            <li>Create, edit and delete that content, and upload images for it.</li>
        </ul>
    </div>

    <div class="section">
        <div class="label">After you approve, you go back to</div>
        <p><code>{{ $redirectHosts }}</code></p>
    </div>

    <p class="note">Only approve if you started this connection yourself, from Claude. You can disconnect it any time on <strong>Site → Claude connector</strong>.</p>

    <div class="actions">
        <form method="POST" action="{{ route('passport.authorizations.deny') }}">
            @csrf
            @method('DELETE')
            <input type="hidden" name="state" value="">
            <input type="hidden" name="client_id" value="{{ $client->getKey() }}">
            <input type="hidden" name="auth_token" value="{{ $authToken }}">
            <button type="submit">Cancel</button>
        </form>

        <form method="POST" action="{{ route('passport.authorizations.approve') }}">
            @csrf
            <input type="hidden" name="state" value="">
            <input type="hidden" name="client_id" value="{{ $client->getKey() }}">
            <input type="hidden" name="auth_token" value="{{ $authToken }}">
            <button type="submit" class="primary">Approve</button>
        </form>
    </div>
</main>
</body>
</html>
