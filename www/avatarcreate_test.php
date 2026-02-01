<?php
$geniesApiKey = getenv('GENIES_PUBLIC_API_KEY') ?: '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Genies Avatar 2D Render Test</title>
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Oxygen, Ubuntu, Cantarell, Fira Sans, Droid Sans, Helvetica Neue, sans-serif; padding: 16px; background: #f8f9fa; }
        .card { background: #fff; border-radius: 10px; border: 1px solid #e5e7eb; padding: 16px; margin-bottom: 16px; }
        #avatarCanvas { width: 240px; height: 240px; border: 1px solid #e5e7eb; border-radius: 8px; }
        #avatarProfilePic { width: 240px; height: 240px; border-radius: 8px; border: 1px solid #e5e7eb; display: none; }
        #avatarEditorContainer { min-height: 520px; border: 1px dashed #d1d5db; border-radius: 8px; background: #fff; }
        .muted { color: #6b7280; font-size: 12px; }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js"></script>
    <script src="https://unpkg.com"></script>
</head>

<body data-genies-api-key="<?php echo htmlspecialchars($geniesApiKey, ENT_QUOTES, 'UTF-8'); ?>">
    <div class="card">
        <h4>Genies Avatar Test (2D Render)</h4>
        <p class="muted">Login, create an avatar, then paste the .glb URL below to render a 2D headshot.</p>

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
        <label for="glbUrl" class="form-label">Genies .glb URL</label>
        <input id="glbUrl" class="form-control" type="text" placeholder="Paste Genies .glb model URL">
        <div class="d-flex gap-2" style="margin-top: 8px;">
            <button id="renderBtn" class="btn btn-primary">Render 2D Headshot</button>
            <button id="clearBtn" class="btn btn-outline-secondary">Clear</button>
        </div>
        <div class="muted" style="margin-top: 6px;">The rendered 2D image appears below and can be downloaded or copied.</div>
    </div>

    <div class="card">
        <div class="d-flex gap-4 flex-wrap">
            <div>
                <div class="muted">Canvas render</div>
                <canvas id="avatarCanvas" width="240" height="240"></canvas>
            </div>
            <div>
                <div class="muted">PNG output</div>
                <img id="avatarProfilePic" alt="User Avatar" />
            </div>
        </div>
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

        function renderAvatarTo2D(glbUrl) {
            return new Promise((resolve, reject) => {
                const canvas = document.getElementById('avatarCanvas');
                const renderer = new THREE.WebGLRenderer({ canvas: canvas, antialias: true, alpha: false });
                renderer.setClearColor(0xffffff);
                renderer.setSize(240, 240);

                const scene = new THREE.Scene();
                const camera = new THREE.PerspectiveCamera(35, 240 / 240, 0.1, 100);
                camera.position.set(0, 1.45, 1.8);
                camera.lookAt(0, 1.4, 0);

                const ambientLight = new THREE.AmbientLight(0x404040, 2.5);
                scene.add(ambientLight);
                const mainLight = new THREE.DirectionalLight(0xffffff, 2);
                mainLight.position.set(1, 2, 3).normalize();
                scene.add(mainLight);
                const backLight = new THREE.DirectionalLight(0xffffff, 0.5);
                backLight.position.set(-1, 2, -2).normalize();
                scene.add(backLight);

                const loader = new THREE.GLTFLoader();
                loader.load(
                    glbUrl,
                    (gltf) => {
                        scene.add(gltf.scene);
                        renderer.render(scene, camera);
                        const imageDataUrl = canvas.toDataURL('image/png');
                        resolve(imageDataUrl);
                        renderer.dispose();
                    },
                    undefined,
                    (error) => {
                        console.error('An error occurred loading the GLB model:', error);
                        reject(error);
                    }
                );
            });
        }

        document.getElementById('renderBtn').addEventListener('click', async () => {
            const glbUrl = document.getElementById('glbUrl').value.trim();
            if (!glbUrl) {
                alert('Please paste a .glb URL first.');
                return;
            }
            try {
                const dataUrl = await renderAvatarTo2D(glbUrl);
                const img = document.getElementById('avatarProfilePic');
                img.src = dataUrl;
                img.style.display = 'block';
            } catch (e) {
                alert('Failed to render the avatar. Check the console for details.');
            }
        });

        document.getElementById('clearBtn').addEventListener('click', () => {
            document.getElementById('glbUrl').value = '';
            const img = document.getElementById('avatarProfilePic');
            img.src = '';
            img.style.display = 'none';
            const canvas = document.getElementById('avatarCanvas');
            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        });
    </script>
</body>
</html>
