<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ImageController extends Controller
{
    public function store(Request $request)
    {
        $userId = auth()->id(); // Obtener el ID del usuario autenticado
        $imageData = $request->input('image'); // Imagen codificada en base64
    
        if (!$imageData) {
            return response()->json(['error' => 'No se recibió imagen'], 400);
        }
    
        // Decodificar la imagen
        $image = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData));
    
        // Guardar la imagen en la carpeta correspondiente
        $imageName = uniqid() . '.jpg';
        $path = 'faces/' . $userId . '/' . $imageName;
    
        Storage::put($path, $image);
    
        return response()->json(['message' => 'Imagen guardada exitosamente', 'path' => $path], 200);
    }

    public function identify(Request $request)
    {
        $imageData = $request->input('image'); // Imagen base64

        if (!$imageData) {
            return response()->json(['error' => 'No se recibió imagen'], 400);
        }

        // Decodificar y guardar temporalmente la imagen
        $tempPath = 'temp/' . uniqid() . '.jpg';
        Storage::put($tempPath, base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData)));

        $localPath = storage_path('app/' . $tempPath);
        $databasePath = storage_path('app/private/faces');

        // Ejecutar el script de Python
        $process = new Process(['python3', base_path('deepface_recognition.py'), $localPath, $databasePath]);
        $process->run();

        // Manejar errores del proceso
        if (!$process->isSuccessful()) {
            Storage::delete($tempPath);
            throw new ProcessFailedException($process);
        }

        // Leer y retornar el resultado
        $output = $process->getOutput();
        Storage::delete($tempPath); // Limpiar imagen temporal
        return response()->json(json_decode($output, true), 200);
    }
}