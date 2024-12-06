<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ImageController extends Controller {
    public function store(Request $request) {
        $image = $request->input('image'); // Recibe la imagen en formato Base64
        $image = str_replace('data:image/png;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $imageName = 'captured_image.png';
        file_put_contents($imageName, base64_decode($image));

        // Ejecutar el script de Python para reconocimiento facial
        $output = shell_exec("python3 /path/to/facial_recognition.py $imageName");
        return response()->json(['result' => $output]);
    }
}
