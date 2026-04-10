<?php

require_once "../login/Auth.php";

Auth::verificar();

header('Content-Type: application/json');

// 🔥 LEER JSON
$data = json_decode(file_get_contents("php://input"), true);

if(!$data){
    echo json_encode(['ok'=>false,'error'=>'Datos inválidos']);
    exit;
}

// 🔥 VALIDAR INDEX
$index = isset($data['index']) ? intval($data['index']) : -1;

if($index < 0){
    echo json_encode(['ok'=>false,'error'=>'Index inválido']);
    exit;
}

// 🔥 FUNCION VALIDAR IMAGEN BASE64
function procesarImagen($base64){

    // quitar prefijo
    if(strpos($base64, 'base64,') !== false){
        $base64 = explode('base64,', $base64)[1];
    }

    // validar base64
    $decoded = base64_decode($base64, true);

    if($decoded === false){
        return false;
    }

    // 🔥 LIMITE 5MB
    if(strlen($decoded) > 5 * 1024 * 1024){
        return false;
    }

    // 🔥 VALIDAR QUE SEA IMAGEN REAL
    $info = @getimagesizefromstring($decoded);

    if($info === false){
        return false;
    }

    return $decoded;
}

// 🔥 PROCESAR IMÁGENES
$img3d = procesarImagen($data['img3d'] ?? '');
$img2d = procesarImagen($data['img2d'] ?? '');

if(!$img3d || !$img2d){
    echo json_encode(['ok'=>false,'error'=>'Imagen inválida']);
    exit;
}

// 🔥 RUTA SEGURA
$ruta = $_SERVER['DOCUMENT_ROOT']."/temp/";

if(!file_exists($ruta)){
    mkdir($ruta,0755,true);
}

// 🔥 NOMBRE SEGURO
$nombre3d = "cubicaje_{$index}_3d.jpg";
$nombre2d = "cubicaje_{$index}_2d.jpg";

// 🔥 GUARDAR
file_put_contents($ruta.$nombre3d, $img3d);
file_put_contents($ruta.$nombre2d, $img2d);

echo json_encode([
    "ok"=>true,
    "archivos"=>[
        "img3d"=>$nombre3d,
        "img2d"=>$nombre2d
    ]
]);