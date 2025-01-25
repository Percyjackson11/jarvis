<?php
session_start();

// Initialize cameras array in session if not exists
if (!isset($_SESSION['cameras'])) {
    $_SESSION['cameras'] = [];
}
// Handle form submission to add new camera
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_camera'])) {
    $newCamera = [
        'code' => $_POST['camera_code'],
        'name' => $_POST['camera_name']
    ];
    
    // Check if camera code already exists
    $exists = false;
    foreach ($_SESSION['cameras'] as $camera) {
        if ($camera['code'] === $newCamera['code']) {
            $exists = true;
            break;
        }
    }
    
    if (!$exists) {
        $_SESSION['cameras'][] = $newCamera;
    }
}

// Handle camera removal
if (isset($_GET['remove'])) {
    $removeIndex = $_GET['remove'];
    if (isset($_SESSION['cameras'][$removeIndex])) {
        unset($_SESSION['cameras'][$removeIndex]);
        $_SESSION['cameras'] = array_values($_SESSION['cameras']); // Reindex array
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width">
    <title>CCTV Viewer</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: #f0f0f0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .add-camera-form {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .camera-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .camera-container {
            background: white;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .camera-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        video {
            width: 100%;
            border-radius: 4px;
        }

        input[type="text"] {
            padding: 8px;
            margin-right: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        button {
            padding: 8px 15px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background: #0056b3;
        }

        .remove-btn {
            background: #dc3545;
            padding: 4px 8px;
            font-size: 12px;
        }

        .remove-btn:hover {
            background: #c82333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>CCTV Viewer</h1>

        <div class="add-camera-form">
            <h3>Add New Camera</h3>
            <form method="POST">
                <input type="text" name="camera_code" placeholder="Camera Code" required>
                <input type="text" name="camera_name" placeholder="Camera Name" required>
                <button type="submit" name="add_camera">Add Camera</button>
            </form>
        </div>

        <div class="camera-grid">
<?php foreach ($_SESSION['cameras'] as $index => $camera): ?>
                <div class="camera-container">
                    <div class="camera-header">
                        <h3><?php echo htmlspecialchars($camera['name']); ?></h3>
                        <a href="?remove=<?php echo $index; ?>" class="remove-btn">Remove</a>
                    </div>
                    <video id="video-<?php echo $camera['code']; ?>" autoplay></video>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.scaledrone.com/scaledrone.min.js"></script>
    <script>
        // Initialize connections for each camera
        const cameras = <?php echo json_encode($_SESSION['cameras']); ?>;
        const configuration = {
            iceServers: [{
                urls: 'stun:stun.l.google.com:19302'
            }]
        };

        cameras.forEach(camera => {
            initializeCamera(camera);
        });

        function initializeCamera(camera) {
            const drone = new ScaleDrone('yiS12Ts5RdNhebyM');
            const roomName = 'cctv-' + camera.code;
            let pc;

            drone.on('open', error => {
                if (error) {
                    console.error(error);
                    return;
                }

                const room = drone.subscribe(roomName);

                room.on('open', error => {
                    if (error) console.error(error);
                });

                room.on('data', (message, client) => {
                    if (client.id === drone.clientId) return;

                    if (message.sdp) {
                        pc.setRemoteDescription(
                            new RTCSessionDescription(message.sdp),
                            () => {
                                if (pc.remoteDescription.type === 'offer') {
                                    pc.createAnswer()
                                        .then(answer => pc.setLocalDescription(answer))
                                        .then(() => {
                                            drone.publish({
                                                room: roomName,
                                                message: { sdp: pc.localDescription }
                                            });
                                        });
                                }
                            },
                            error => console.error(error)
                        );
                    } else if (message.candidate) {
                        pc.addIceCandidate(
                            new RTCIceCandidate(message.candidate)
                        ).catch(error => console.error(error));
                    }
                });
            });

            pc = new RTCPeerConnection(configuration);

            pc.onicecandidate = event => {
                if (event.candidate) {
                    drone.publish({
                        room: roomName,
                        message: { candidate: event.candidate }
                    });
                }
            };

            // When a remote stream arrives display it in the corresponding video element
            pc.ontrack = event => {
                const stream = event.streams[0];
                const videoElement = document.getElementById(`video-${camera.code}`);
                if (!videoElement.srcObject || videoElement.srcObject.id !== stream.id) {
                    videoElement.srcObject = stream;
                }
            };
        }
    </script>

    <script>
        // Add some basic error handling
        window.onerror = function(msg, url, lineNo, columnNo, error) {
            console.error('Error: ' + msg + '\nURL: ' + url + '\nLine: ' + lineNo + '\nColumn: ' + columnNo + '\nError object: ' + JSON.stringify(error));
            return false;
        };
    </script>
</body>
</html>