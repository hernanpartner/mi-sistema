<?php
require_once "../login/Auth.php";
require_once "../config/database.php";
require_once "MotorAcomodo.php";

if (session_status() === PHP_SESSION_NONE) session_start();
Auth::verificar();

$db = Database::conectar();

$proyecto_id = $_SESSION['proyecto_id'] ?? 0;

if(!$proyecto_id){
    header("Location: index.php");
    exit;
}

$contenedor = $_SESSION['contenedor'] ?? [];

if (
    !isset($contenedor['largo']) ||
    !isset($contenedor['ancho']) ||
    !isset($contenedor['alto'])
) {
    header("Location: proyecto.php?id=".$proyecto_id);
    exit;
}

$stmt = $db->prepare("SELECT * FROM cubicaje WHERE proyecto_id=?");
$stmt->execute([$proyecto_id]);
$cajasDB = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* LIMPIAR IMAGENES */
$dir = $_SERVER['DOCUMENT_ROOT']."/temp/";
if(file_exists($dir)){
    foreach(glob($dir."cubicaje_*") as $f){
        unlink($f);
    }
}

/* CAJAS */
$cajas = [];

foreach($cajasDB as $c){
    for($i=0;$i<$c['cantidad'];$i++){
        $cajas[] = [
            'nombre'=>$c['nombre'],
            'l'=>$c['largo'],
            'a'=>$c['ancho'],
            'h'=>$c['alto'],
            'peso'=>$c['peso'],
            'color'=>$c['color'],
            'apilable'=>$c['apilable'],
            'rotable'=>$c['rotable']
        ];
    }
}

usort($cajas,function($a,$b){
    return ($b['l']*$b['a']*$b['h']) <=> ($a['l']*$a['a']*$a['h']);
});

$resultado = MotorAcomodo::acomodar($contenedor,$cajas);

/* FIX NOMBRE + COLOR */
foreach($resultado as &$cont){
    foreach($cont['cajas'] as &$caja){
        foreach($cajas as $o){
            if($o['l']==$caja['l'] && $o['a']==$caja['a'] && $o['h']==$caja['h']){
                $caja['nombre']=$o['nombre'];
                $caja['color']=$o['color'];
                break;
            }
        }
    }
}

$_SESSION['resultado_cubicaje']=$resultado;

$contenedores = array_column($resultado,'cajas');

$stats=[];
foreach($resultado as $r){
    $vol=0;
    foreach($r['cajas'] as $c){
        $vol += $c['l']*$c['a']*$c['h'];
    }
    $stats[]=[
        'cantidad'=>count($r['cajas']),
        'peso'=>$r['peso'],
        'volumen'=>$vol
    ];
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<link href="../libs/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<title>Simulador 3D</title>
</head>

<body style="margin:0; overflow:hidden; background:#fff;">

<!-- PANEL -->
<div style="position:absolute;top:15px;left:15px;z-index:10;width:280px;">

<div class="card shadow">

<div class="card-header bg-primary text-white">
<b>📦 Proyecto #<?php echo $proyecto_id; ?></b>
</div>

<div class="card-body p-2">

<div class="d-grid gap-2 mb-2">
<?php foreach($contenedores as $i=>$c): ?>
<button onclick="mostrar(<?php echo $i; ?>)" class="btn btn-outline-primary btn-sm">
Contenedor <?php echo $i+1; ?>
</button>
<?php endforeach; ?>
</div>

<div id="infoContenedor" class="alert alert-light small"></div>

<div class="d-grid gap-2 mb-2">
<a href="reporte_pdf.php" target="_blank" class="btn btn-danger btn-sm">📄 PDF</a>
<a href="../export/excel_simulacion.php" class="btn btn-success btn-sm">📊 Excel</a>
<a href="../export/word_simulacion.php" class="btn btn-primary btn-sm">📘 Word</a>
</div>

<a href="proyecto.php?id=<?php echo $proyecto_id; ?>" class="btn btn-secondary btn-sm w-100">
⬅ Volver
</a>

</div>
</div>
</div>

<script type="module">
import * as THREE from '../libs/three/build/three.module.js';
import { OrbitControls } from '../libs/three/examples/jsm/controls/OrbitControls.js';

const contenedor = <?php echo json_encode($contenedor); ?>;
const contenedores = <?php echo json_encode($contenedores); ?>;
const stats = <?php echo json_encode($stats); ?>;

const scene = new THREE.Scene();
scene.background = new THREE.Color(0xffffff);

const camera = new THREE.PerspectiveCamera(60, window.innerWidth/window.innerHeight, 0.1, 10000);

const renderer = new THREE.WebGLRenderer({antialias:true, preserveDrawingBuffer:true});
renderer.setSize(window.innerWidth, window.innerHeight);
document.body.appendChild(renderer.domElement);

const controls = new OrbitControls(camera, renderer.domElement);

scene.add(new THREE.AmbientLight(0xffffff,1));

const light = new THREE.DirectionalLight(0xffffff,1);
light.position.set(1,1,1);
scene.add(light);

let grupos=[];

contenedores.forEach((cajas,index)=>{
    const group = new THREE.Group();

    const geoCont = new THREE.BoxGeometry(contenedor.largo, contenedor.alto, contenedor.ancho);
    const matCont = new THREE.MeshBasicMaterial({color:0x000000,wireframe:true});
    const meshCont = new THREE.Mesh(geoCont, matCont);

    meshCont.position.set(contenedor.largo/2, contenedor.alto/2, contenedor.ancho/2);
    group.add(meshCont);

    cajas.forEach(c=>{
        const geo = new THREE.BoxGeometry(c.l,c.h,c.a);
        const mat = new THREE.MeshLambertMaterial({color:c.color});
        const mesh = new THREE.Mesh(geo,mat);

        mesh.position.set(c.x + c.l/2, c.z + c.h/2, c.y + c.a/2);
        group.add(mesh);
    });

    group.visible = (index===0);
    scene.add(group);
    grupos.push(group);
});

/* ZOOM REAL */
function enfocar(group){
    const box = new THREE.Box3().setFromObject(group);
    const center = new THREE.Vector3();
    box.getCenter(center);

    const size = new THREE.Vector3();
    box.getSize(size);

    const max = Math.max(size.x,size.y,size.z);

    camera.position.set(center.x + max*1.2, center.y + max*1.2, center.z + max*1.2);
    camera.lookAt(center);
    controls.target.copy(center);
}

/* CAPTURA PRO */
function capturar(index){

    const group = grupos[index];
    const box = new THREE.Box3().setFromObject(group);
    const center = new THREE.Vector3();
    box.getCenter(center);

    const size = new THREE.Vector3();
    box.getSize(size);
    const max = Math.max(size.x,size.y,size.z);

    /* 🔥 MÁS ZOOM (ANTES 1.2) */
    const zoom3D = 0.8;
    const zoom2D = 1.2;

    /* 3D GRANDE */
    camera.position.set(
        center.x + max*zoom3D,
        center.y + max*zoom3D,
        center.z + max*zoom3D
    );
    camera.lookAt(center);
    renderer.render(scene,camera);
    const img3d = renderer.domElement.toDataURL("image/jpeg",1.0);

    /* 2D MÁS CERCA */
    camera.position.set(
        center.x,
        center.y + max*zoom2D,
        center.z
    );
    camera.lookAt(center);
    renderer.render(scene,camera);
    const img2d = renderer.domElement.toDataURL("image/jpeg",1.0);
    fetch("guardar_imagen.php",{
        method:"POST",
        headers:{"Content-Type":"application/json"},
        body: JSON.stringify({img3d:img3d,img2d:img2d,index:index})
    });
}

/* GENERAR */
function generar(){
    let i=0;
    function loop(){
        if(i>=grupos.length) return;
        mostrar(i);
        setTimeout(()=>{
            capturar(i);
            i++;
            loop();
        },400);
    }
    loop();
}

window.mostrar = function(i){
    grupos.forEach((g,index)=>g.visible=(index===i));

    let d = stats[i];
    document.getElementById("infoContenedor").innerHTML = `
    <b>Contenedor ${i+1}</b><br>
    📦 ${d.cantidad} cajas<br>
    ⚖️ ${d.peso} kg<br>
    📐 ${d.volumen.toLocaleString()} cm³
    `;

    enfocar(grupos[i]);
};

mostrar(0);
generar();

function animate(){
    requestAnimationFrame(animate);
    controls.update();
    renderer.render(scene,camera);
}
animate();

window.addEventListener('resize', ()=>{
    camera.aspect = window.innerWidth/window.innerHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(window.innerWidth, window.innerHeight);
});
</script>

</body>
</html>