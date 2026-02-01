<?php
$geniesApiKey = getenv('GENIES_PUBLIC_API_KEY');
if (!$geniesApiKey) {
    $geniesApiKey = '';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Genies Avatar Creation</title>
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Oxygen, Ubuntu, Cantarell, Fira Sans, Droid Sans, Helvetica Neue, sans-serif; padding: 16px; background: #f8f9fa; }
        .card { background: #fff; border-radius: 10px; border: 1px solid #e5e7eb; padding: 16px; margin-bottom: 16px; }
        .muted { color: #6b7280; font-size: 12px; }
        #avatarEditorContainer { min-height: 520px; border: 1px dashed #d1d5db; border-radius: 8px; background: #fff; }
    </style>
    <script src="https://unpkg.com"></script>
</head>

<body data-genies-api-key="<?php echo htmlspecialchars($geniesApiKey, ENT_QUOTES, 'UTF-8'); ?>">
    <div class="card">
        <h4>Genies Avatar Creator</h4>
        <p class="muted">Log in, create your avatar, then paste your avatar URL or ID below and click “Use this avatar”.</p>

        <button id="geniesLoginBtn" class="btn btn-primary">Login with Genies</button>
        <div id="authContainer" style="display: none; margin-top: 12px;">
            <div class="row g-2">
                <div class="col-md-5">
                    <input type="email" id="emailInput" class="form-control" placeholder="Enter email">
                </div>
                <div class="col-md-3">
                    <button class="btn btn-outline-primary w-100" onclick="sendOTP()">Send OTP</button>
                </div>
                <div class="col-md-2">
                    <input type="text" id="otpInput" class="form-control" placeholder="OTP">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-primary w-100" onclick="verifyOTP()">Verify</button>
                </div>
            </div>
        </div>

        <div style="margin-top: 12px;">
            <button id="createAvatarBtn" class="btn btn-success" style="display: none;">Create/Edit Avatar</button>
        </div>
    </div>

    <div class="card">
        <label for="avatarUrl" class="form-label">Avatar URL or ID</label>
        <input id="avatarUrl" class="form-control" type="text" placeholder="Paste your Genies avatar URL or ID">
        <div class="d-flex gap-2" style="margin-top: 8px;">
            <button id="sendToSignupBtn" class="btn btn-primary">Use this avatar</button>
            <button id="copyBtn" class="btn btn-outline-secondary">Copy</button>
        </div>
        <div class="muted" style="margin-top: 6px;">If this page is opened inside the signup form, “Use this avatar” will send it back automatically.</div>
    </div>

    <div class="card">
        <div id="avatarEditorContainer"></div>
    </div>

    <script type="text/javascript">
        let geniesSDK;
        let userEmail;

        document.getElementById('geniesLoginBtn').addEventListener('click', async () => {
            const apiKey = document.body.dataset.geniesApiKey;
            if (!apiKey) {
                alert('Genies API key is not configured.');
                return;
            }
            geniesSDK = new AvatarSDK.default({
                apiKey: apiKey
            });
            document.getElementById('authContainer').style.display = 'block';
            document.getElementById('geniesLoginBtn').style.display = 'none';
        });

        async function sendOTP() {
            userEmail = document.getElementById('emailInput').value;
            try {
                await geniesSDK.auth.sendOTP({ email: userEmail });
                alert('OTP sent to email.');
            } catch (error) {
                console.error('Error sending OTP:', error);
            }
        }

        async function verifyOTP() {
            const otp = document.getElementById('otpInput').value;
            try {
                const result = await geniesSDK.auth.verifyOTP({ email: userEmail, otp });
                console.log('Login successful:', result);
                alert('Successfully logged in! You can now create an avatar.');
                document.getElementById('createAvatarBtn').style.display = 'inline-block';
            } catch (error) {
                console.error('Error verifying OTP:', error);
                alert('Failed to verify OTP.');
            }
        }

        document.getElementById('createAvatarBtn').addEventListener('click', async () => {
            await geniesSDK.editor.launch({
                container: 'avatarEditorContainer'
            });
        });

        function sendAvatarToParent() {
            const value = document.getElementById('avatarUrl').value.trim();
            if (!value) {
                alert('Please paste your Genies avatar URL or ID first.');
                return;
            }
            const message = { type: 'genies.avatar', avatarUrl: value };
            window.parent.postMessage(JSON.stringify(message), '*');
        }

        document.getElementById('sendToSignupBtn').addEventListener('click', sendAvatarToParent);
        document.getElementById('copyBtn').addEventListener('click', () => {
            const value = document.getElementById('avatarUrl').value.trim();
            if (!value) return;
            navigator.clipboard.writeText(value).catch(() => {});
        });
    </script>
</body>
</html>
