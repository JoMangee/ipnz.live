<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avatar Creator - GLB to 2D</title>
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Oxygen, Ubuntu, Cantarell, Fira Sans, Droid Sans, Helvetica Neue, sans-serif; 
            padding: 16px; 
            background: #f8f9fa; 
        }
        .card { 
            background: #fff; 
            border-radius: 10px; 
            border: 1px solid #e5e7eb; 
            padding: 16px; 
            margin-bottom: 16px; 
        }
        #avatarCanvas { 
            width: 240px; 
            height: 240px; 
            border: 1px solid #e5e7eb; 
            border-radius: 8px; 
        }
        #avatarProfilePic { 
            width: 240px; 
            height: 240px; 
            border-radius: 8px; 
            border: 1px solid #e5e7eb; 
            display: none; 
        }
        .muted { 
            color: #6b7280; 
            font-size: 12px; 
        }
        #errorMsg { 
            color: #dc2626; 
            background: #fef2f2; 
            padding: 8px; 
            border-radius: 4px; 
            margin-bottom: 12px; 
            display: none; 
        }
        #loadingMsg { 
            color: #0d6efd; 
            background: #e7f1ff; 
            padding: 8px; 
            border-radius: 4px; 
            margin-bottom: 12px; 
            display: none; 
        }
    </style>
    <script src="/js/vendor/three.r128.min.js"></script>
    <script src="/js/vendor/GLTFLoader.js"></script>
</head>

<body>
    <div class="card">
        <h4>Avatar Creator</h4>
        <p class="muted">Paste a .glb model URL and render a 2D headshot.</p>

        <label for="glbUrl" class="form-label" style="margin-top: 12px;">GLB Model URL</label>
        <input id="glbUrl" class="form-control" type="text" placeholder="Paste .glb model URL">
        
        <div class="d-flex gap-2" style="margin-top: 12px;">
            <button id="renderBtn" class="btn btn-primary">Render 2D Headshot</button>
            <button id="downloadBtn" class="btn btn-success" style="display: none;">Download PNG</button>
            <button id="useAvatarBtn" class="btn btn-success" style="display: none;">Use This Avatar</button>
        </div>
        <div class="muted" style="margin-top: 6px;">The rendered 2D image appears below.</div>
    </div>

    <div class="card">
        <div id="errorMsg"></div>
        <div id="loadingMsg">Rendering...</div>
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

    <script>
        function showError(msg) {
            const errorDiv = document.getElementById('errorMsg');
            errorDiv.textContent = msg;
            errorDiv.style.display = 'block';
            console.error(msg);
        }

        function clearError() {
            const errorDiv = document.getElementById('errorMsg');
            errorDiv.style.display = 'none';
        }

        function showLoading(show = true) {
            document.getElementById('loadingMsg').style.display = show ? 'block' : 'none';
        }

        /**
         * Renders a 3D avatar GLB URL to a 2D canvas with headshot style.
         */
        function renderAvatarTo2D(glbUrl) {
            return new Promise((resolve, reject) => {
                try {
                    clearError();
                    showLoading(true);

                    const canvas = document.getElementById('avatarCanvas');
                    const renderer = new THREE.WebGLRenderer({ canvas: canvas, antialias: true, alpha: false });
                    renderer.setClearColor(0xffffff); // White background
                    renderer.setSize(240, 240);

                    const scene = new THREE.Scene();
                    const camera = new THREE.PerspectiveCamera(35, 240 / 240, 0.1, 100);
                    
                    // Position camera for close-up headshot
                    camera.position.set(0, 1.45, 1.8);
                    camera.lookAt(0, 1.4, 0);

                    // Add studio lighting
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
                            console.log('GLB loaded successfully');
                            scene.add(gltf.scene);
                            renderer.render(scene, camera);
                            
                            const imageDataUrl = canvas.toDataURL('image/png');
                            document.getElementById('avatarProfilePic').src = imageDataUrl;
                            document.getElementById('avatarProfilePic').style.display = 'block';
                            
                            // Show action buttons
                            document.getElementById('downloadBtn').style.display = 'inline-block';
                            document.getElementById('useAvatarBtn').style.display = 'inline-block';
                            
                            showLoading(false);
                            resolve(imageDataUrl);
                            renderer.dispose();
                        },
                        (progress) => {
                            const percentComplete = (progress.loaded / progress.total * 100).toFixed(2);
                            console.log('Loading: ' + percentComplete + '%');
                        },
                        (error) => {
                            console.error('Error loading GLB model:', error);
                            showError('Failed to load model: ' + error.message);
                            showLoading(false);
                            reject(error);
                        }
                    );
                } catch (error) {
                    console.error('Render error:', error);
                    showError('Render error: ' + error.message);
                    showLoading(false);
                    reject(error);
                }
            });
        }

        // Event listeners
        document.getElementById('renderBtn').addEventListener('click', async () => {
            const glbUrl = document.getElementById('glbUrl').value.trim();
            if (!glbUrl) {
                showError('Please enter a .glb URL');
                return;
            }
            try {
                await renderAvatarTo2D(glbUrl);
            } catch (error) {
                // Error already displayed
            }
        });

        document.getElementById('downloadBtn').addEventListener('click', () => {
            const img = document.getElementById('avatarProfilePic');
            if (img.src) {
                const link = document.createElement('a');
                link.href = img.src;
                link.download = 'avatar-headshot.png';
                link.click();
            }
        });

        document.getElementById('useAvatarBtn').addEventListener('click', () => {
            const glbUrl = document.getElementById('glbUrl').value.trim();
            // Send message to parent frame (if embedded in iframe)
            if (window.parent !== window) {
                window.parent.postMessage({
                    type: 'avatar.selected',
                    avatarUrl: glbUrl
                }, 'https://avatars.ipnz.live');
                console.log('Avatar URL sent to parent frame');
            } else {
                // Not in iframe, just log
                console.log('Avatar selected (not in iframe):', glbUrl);
                alert('Avatar selected:\n\n' + glbUrl);
            }
        });
    </script>
</body>

</html>
