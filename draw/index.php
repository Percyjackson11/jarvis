<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drawing App</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: hidden;
        }

        canvas {
            display: block;
            margin: 0;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        .button-bar {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            background-color: rgba(255, 255, 255, 0.7);
            display: flex;
            justify-content: space-between;
            z-index: 2; /* Ensure buttons are on top */
        }

        button {
            padding: 10px;
            font-size: 16px;
            border: none;
            cursor: pointer;
        }

        input[type="color"] {
            margin-left: 10px;
        }

        input[type="range"] {
            flex-grow: 1;
        }

        .gallery-container {
            display: none;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            z-index: 3; /* Ensure gallery is on top */
            overflow-y: scroll;
        }

        .gallery {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .gallery img {
            max-width: 200px;
            max-height: 200px;
            margin: 10px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="button-bar">
        <button id="clearButton">Clear</button>
        <button id="saveButton">Save</button>
        <button id="modeButton">Pencil</button>
        <button id="undoButton">Undo</button>
        <button id="redoButton">Redo</button>
        <input type="color" id="colorPicker" value="#000000">
        <input type="range" id="thicknessSlider" min="1" max="50" value="2">
        <button id="galleryButton">Gallery</button>
    </div>
    <canvas id="drawingCanvas"></canvas>

    <div class="gallery-container" id="galleryContainer">
        <div class="gallery" id="gallery">
            <?php
            $drawingsDirectory = 'drawings';
            if (is_dir($drawingsDirectory)) {
                $files = scandir($drawingsDirectory);
                foreach ($files as $file) {
                    if ($file !== '.' && $file !== '..') {
                        echo '<img src="' . $drawingsDirectory . '/' . $file . '" alt="Drawing">';
                    }
                }
            }
            ?>
        </div>
    </div>

    <script>
        const canvas = document.getElementById('drawingCanvas');
        const ctx = canvas.getContext('2d');
        let drawing = false;
        let currentColor = '#000000';
        let currentThickness = 2;
        let isErasing = false;
        let undoStack = [];
        let redoStack = [];

        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;

        ctx.lineJoin = 'round';

        const getTouchPos = (e) => {
            const rect = canvas.getBoundingClientRect();
            return {
                x: e.touches[0].clientX - rect.left,
                y: e.touches[0].clientY - rect.top
            };
        };

        canvas.addEventListener('touchstart', (e) => {
            e.preventDefault();
            if (isErasing) {
                ctx.globalCompositeOperation = 'destination-out';
                currentColor = '#ffffff';
                ctx.lineWidth = currentThickness;
            } else {
                ctx.globalCompositeOperation = 'source-over';
                currentColor = document.getElementById('colorPicker').value;
                ctx.lineWidth = currentThickness;
            }
            const pos = getTouchPos(e);
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
            ctx.lineTo(pos.x + 1, pos.y + 1); // Draw a dot on touch start
            ctx.stroke();
            drawing = true;
            // Clear the redo stack when starting a new drawing
            redoStack = [];
        });

        canvas.addEventListener('touchmove', (e) => {
            e.preventDefault();
            if (!drawing) return;
            const pos = getTouchPos(e);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
        });

        canvas.addEventListener('touchend', () => {
            drawing = false;
            ctx.closePath();
            // Store the drawing state for undo
            undoStack.push(canvas.toDataURL());
        });

        document.getElementById('clearButton').addEventListener('click', () => {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            // Clear both undo and redo stacks when clearing the canvas
            undoStack = [];
            redoStack = [];
        });

        document.getElementById('saveButton').addEventListener('click', () => {
            const image = canvas.toDataURL();
            fetch('save.php', {
                method: 'POST',
                body: JSON.stringify({ imageData: image }),
            })
            .then(response => response.text())
            .then(data => {
                alert(data);
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });

        document.getElementById('modeButton').addEventListener('click', () => {
            isErasing = !isErasing;
            if (isErasing) {
                document.getElementById('modeButton').textContent = 'Pencil';
            } else {
                document.getElementById('modeButton').textContent = 'Eraser';
            }
        });

        document.getElementById('colorPicker').addEventListener('input', (e) => {
            currentColor = e.target.value;
        });

        document.getElementById('thicknessSlider').addEventListener('input', (e) => {
            currentThickness = e.target.value;
            ctx.lineWidth = currentThickness;
        });

        document.getElementById('undoButton').addEventListener('click', () => {
            if (undoStack.length > 0) {
                const lastState = undoStack.pop();
                redoStack.push(canvas.toDataURL());
                const img = new Image();
                img.src = lastState;
                img.onload = () => {
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                };
            }
        });

        document.getElementById('redoButton').addEventListener('click', () => {
            if (redoStack.length > 0) {
                const nextState = redoStack.pop();
                undoStack.push(canvas.toDataURL());
                const img = new Image();
                img.src = nextState;
                img.onload = () => {
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                };
            }
        });

        document.getElementById('galleryButton').addEventListener('click', () => {
            const galleryContainer = document.getElementById('galleryContainer');
            galleryContainer.style.display = 'block';
        });

        // Close the gallery when clicking outside of it
        const galleryContainer = document.getElementById('galleryContainer');
        galleryContainer.addEventListener('click', (e) => {
            if (e.target === galleryContainer) {
                galleryContainer.style.display = 'none';
            }
        });

        // Adjust canvas size on window resize
        window.addEventListener('resize', () => {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        });
    </script>
</body>
</html>
