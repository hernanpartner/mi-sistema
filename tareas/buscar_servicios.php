<?php
require_once "../login/Auth.php";
require_once "../config/database.php";

Auth::verificar();

$db = Database::conectar();

$q = $_GET['q'] ?? '';

$stmt = $db->prepare("
    SELECT s.*, c.nombre as categoria
    FROM servicios s
    LEFT JOIN categorias c ON s.categoria_id = c.id
    WHERE s.codigo LIKE ? OR s.cliente LIKE ? OR s.descripcion LIKE ?
    ORDER BY s.id DESC
");

$like = "%$q%";
$stmt->execute([$like,$like,$like]);

$servicios = $stmt->fetchAll();

foreach($servicios as $s){

$estadoLogistico = 'programado';

if (!empty($s['etd']) && !empty($s['eta'])) {

    $ahora = date('Y-m-d H:i:s');

    if ($ahora < $s['etd']) {
        $estadoLogistico = 'programado';
    } elseif ($ahora >= $s['etd'] && $ahora < $s['eta']) {
        $estadoLogistico = 'en tránsito';
    } else {
        $estadoLogistico = 'arribado';
    }
}
?>

<tr id="fila_<?php echo $s['id']; ?>">

<td><?php echo htmlspecialchars($s['codigo']); ?></td>
<td><?php echo htmlspecialchars($s['cliente']); ?></td>
<td><?php echo htmlspecialchars($s['origen']); ?></td>
<td><?php echo htmlspecialchars($s['destino']); ?></td>

<td><?php echo $s['etd'] ? date('d/m H:i', strtotime($s['etd'])) : ''; ?></td>
<td><?php echo $s['eta'] ? date('d/m H:i', strtotime($s['eta'])) : ''; ?></td>

<td>
<span class="badge bg-<?php 
echo $estadoLogistico == 'programado' ? 'primary' :
     ($estadoLogistico == 'en tránsito' ? 'warning' : 'success'); 
?>">
<?php echo ucfirst($estadoLogistico); ?>
</span>
</td>

<td><?php echo htmlspecialchars($s['categoria']); ?></td>

<td>
<a href="ver_servicio.php?id=<?php echo $s['id']; ?>" class="btn btn-info btn-sm">👁</a>
<a href="editar_servicio.php?id=<?php echo $s['id']; ?>" class="btn btn-warning btn-sm">✏️</a>
<button onclick="eliminarServicio(<?php echo $s['id']; ?>)" class="btn btn-danger btn-sm">🗑</button>
</td>

</tr>

<?php } ?>