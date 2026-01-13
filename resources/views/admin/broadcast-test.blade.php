<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Broadcast Test</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body {
            font-family: system-ui, sans-serif;
            background-color: #f4f7f9;
            color: #333;
            padding: 2rem;
        }

        .container {
            max-width: 800px;
            margin: auto;
            background: #fff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #1a202c;
        }

        #status {
            padding: 1rem;
            border-radius: 6px;
            margin-top: 1rem;
        }

        .status-connecting {
            background-color: #e0f2fe;
            color: #0c5460;
        }

        .status-connected {
            background-color: #d4edda;
            color: #155724;
        }

        .status-error {
            background-color: #f8d7da;
            color: #721c24;
        }

        #messages {
            list-style: none;
            padding: 0;
            margin-top: 1rem;
            background: #2d3748;
            color: #f7fafc;
            padding: 1rem;
            border-radius: 6px;
            height: 300px;
            overflow-y: auto;
        }

        #messages li {
            padding: 0.5rem 0;
            border-bottom: 1px solid #4a5568;
        }

        #messages li:last-child {
            border-bottom: none;
        }

        button {
            background-color: #4299e1;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
            margin-top: 1rem;
        }

        button:hover {
            background-color: #2b6cb0;
        }
    </style>
</head>

<body>

    <div class="container">
        <h1>Pusher Broadcast Test</h1>
        <p>This page will attempt to connect to Pusher and listen on the public 'test-channel'.</p>

        <div id="status" class="status-connecting">Connecting to Pusher...</div>

        <button id="trigger-event">Trigger Test Event via API</button>

        <h2>Received Messages:</h2>
        <ul id="messages">
            <li>Waiting for messages...</li>
        </ul>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.4.0/dist/web/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
    <script>
        const statusDiv = document.getElementById('status');
        const messagesUl = document.getElementById('messages');
        const triggerBtn = document.getElementById('trigger-event');
        let messageCount = 0;

        function addMessage(message) {
            if (messageCount === 0) {
                messagesUl.innerHTML = ''; // Clear the 'waiting' message
            }
            const li = document.createElement('li');
            li.textContent = `[${new Date().toLocaleTimeString()}] ${JSON.stringify(message)}`;
            messagesUl.appendChild(li);
            messagesUl.scrollTop = messagesUl.scrollHeight;
            messageCount++;
        }

        try {
            const echo = new Echo({
                broadcaster: 'pusher',
                key: '{{ config('broadcasting.connections.pusher.key') }}',
                cluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}',
                forceTLS: {{ (config('broadcasting.connections.pusher.options.scheme') ?? 'https') === 'https' }},
                authEndpoint: '/admin/broadcasting/auth', // Not used for public channels, but good to have
                auth: {
                    headers: {
                        'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content
                    }
                }
            });

            echo.connector.pusher.connection.bind('state_change', function (states) {
                statusDiv.textContent = `Pusher connection state: ${states.current}`;
                if (states.current === 'connected') {
                    statusDiv.className = 'status-connected';
                } else if (states.current === 'failed' || states.current === 'unavailable') {
                    statusDiv.className = 'status-error';
                } else {
                    statusDiv.className = 'status-connecting';
                }
            });

            echo.channel('test-channel')
                .listen('.test.event', (e) => {
                    console.log('Received broadcast event:', e);
                    addMessage(e);
                });

            statusDiv.textContent = 'Pusher connection state: ' + echo.connector.pusher.connection.state + '. Listening for events...';

        } catch (e) {
            statusDiv.textContent = 'An error occurred while setting up Laravel Echo. Check the console.';
            statusDiv.className = 'status-error';
            console.error(e);
        }

        // Trigger button
        triggerBtn.addEventListener('click', () => {
            statusDiv.textContent = 'Triggering event via API...';
            fetch('/admin/test-broadcast', {
                method: 'POST', // Use POST to avoid caching
                headers: {
                    'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ message: 'Event triggered from test page button.' })
            })
                .then(response => response.json())
                .then(data => {
                    console.log('API response:', data);
                    statusDiv.textContent = 'API call finished. Check "Received Messages" or logs.';
                })
                .catch(error => {
                    console.error('API error:', error);
                    statusDiv.textContent = 'API call failed. Check the browser console and laravel.log.';
                    statusDiv.className = 'status-error';
                });
        });

    </script>

</body>

</html>