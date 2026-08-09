<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>Document</title>
    <style>
        html,
        body,
        .frame {
            width: 1080px;
            height: 800px;
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Oxygen, Ubuntu, Cantarell, Fira Sans,
                Droid Sans, Helvetica Neue, sans-serif;
            padding: 20px;
            font-size: 14px;
            border: none;
        }

        .warning {
            background-color: #df68a2;
            padding: 3px;
            border-radius: 5px;
            color: white;
        }
    </style>
</head>

<body>
    <h2>Ready Player Me iframe example</h2>
    <ul>
        <li>Click the "Open Ready Player Me" button.</li>
        <li>Create an avatar and click the "Done" button when you're done customizing.</li>
        <li>After creation, this parent page receives the URL to the avatar.</li>
        <li>The Ready Player Me window closes and the URL is displayed.</li>
    </ul>
    <p class="warning">
        If you have a subdomain, replace the 'demo' subdomain in the iframe source URL with yours.
    </p>

    <input type="button" value="Open Ready Player Me" onClick="displayIframe()" />
    <p id="avatarUrl">Avatar URL:</p> <button type="button" class="btn btn-primary"><a href="http://">Use this avatar</a></button>

    <iframe id="frame" class="frame" allow="camera *; microphone *; clipboard-write" hidden></iframe>

    <script>
        const subdomain = 'ipnz'; // Replace with your custom subdomain
        const frame = document.getElementById('frame');

        frame.src = `https://${subdomain}.readyplayer.me/avatar?frameApi`;

        window.addEventListener('message', subscribe);
        document.addEventListener('message', subscribe);

        function subscribe(event) {
            const json = parse(event);

            if (json?.source !== 'readyplayerme') {
                return;
            }

            // Susbribe to all events sent from Ready Player Me once frame is ready
            if (json.eventName === 'v1.frame.ready') {
                frame.contentWindow.postMessage(
                    JSON.stringify({
                        target: 'readyplayerme',
                        type: 'subscribe',
                        eventName: 'v1.**'
                    }),
                    '*'
                );
            }

            // Get avatar GLB URL
            if (json.eventName === 'v1.avatar.exported') {
                console.log(`Avatar URL: ${json.data.url}`);
                document.getElementById('avatarUrl').innerHTML = `Avatar URL: ${json.data.url}`;
                document.getElementById('frame').hidden = true;
            }

            // Get user id
            if (json.eventName === 'v1.user.set') {
                console.log(`User with id ${json.data.id} set: ${JSON.stringify(json)}`);
            }
        }

        function parse(event) {
            try {
                return JSON.parse(event.data);
            } catch (error) {
                return null;
            }
        }

        function displayIframe() {
            document.getElementById('frame').hidden = false;
        }
    </script>
        <?php
        // Version marker: short digest over key files for deployment verification
        $versionMeta = include __DIR__ . '/version_meta.php';
        $vmFiles = $versionMeta['files'] ?? [];
        $vmParts = [];
        foreach ($vmFiles as $vmRel) {
            $vmPath = __DIR__ . '/' . $vmRel;
            if (is_file($vmPath)) {
                $vmParts[] = hash_file('sha256', $vmPath);
            }
        }
        $vmDigest = substr(hash('sha256', implode('', $vmParts)), 0, 12);
        echo "<!-- version=" . ($versionMeta['version'] ?? 'unknown') . " commit=" . ($versionMeta['commit'] ?? 'unknown') . " digest=" . $vmDigest . " -->";
        ?>
</body>

</html>