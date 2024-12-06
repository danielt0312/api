{{-- Vista para crear un producto --}}
<x-app-layout>
    <div class="bg-white p-6 rounded shadow">
        <video id="video" autoplay class="w-full mb-4"></video>
        <button id="capture" class="bg-blue-500 text-white px-4 py-2 rounded">Capturar</button>
        <canvas id="canvas" class="hidden"></canvas>
    </div>
</x-app-layout>

<script>
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const captureButton = document.getElementById('capture');

    navigator.mediaDevices.getUserMedia({ video: true })
        .then((stream) => { video.srcObject = stream; })
        .catch((err) => { console.error("Error al acceder a la cámara:", err); });

    captureButton.addEventListener('click', () => {
        const context = canvas.getContext('2d');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        context.drawImage(video, 0, 0, canvas.width, canvas.height);

        const imageData = canvas.toDataURL('image/png');

        fetch('/upload-image', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ image: imageData })
        })
        .then(response => response.json())
        .then(data => console.log('Resultado:', data))
        .catch(error => console.error('Error al enviar la imagen:', error));
    });
</script>