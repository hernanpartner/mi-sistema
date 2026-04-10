<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Pantalla Logística EXPERTO</title>

<style>
body {
    background:#000;
    color:#00ffcc;
    font-family: monospace;
    margin:0;
}

/* HEADER */
.header {
    display:flex;
    justify-content:space-between;
    padding:10px;
    background:#050505;
}

/* KPI */
.kpis {
    display:flex;
    justify-content:space-around;
    padding:10px;
    background:#111;
}

.kpi {
    text-align:center;
    font-size:20px;
}

.kpi span {
    display:block;
    font-size:28px;
}

/* TABLA */
.contenedor {
    height: calc(100vh - 140px);
    overflow:hidden;
}

table {
    width:100%;
    border-collapse:collapse;
    font-size:18px;
}

th, td {
    padding:10px;
}

th { background:#111; }

.programado { color:#00bfff; }
.en_transito { color:yellow; }
.arribado { color:#00ff00; }
.sin_programar { color:gray; }

/* ALERTA */
.retrasado {
    background:red;
    color:white;
    font-weight:bold;
}

/* PARPADEO */
.parpadeo {
    animation: blink 1s infinite;
}

@keyframes blink {
    50% { opacity: 0; }
}

/* SCROLL */
#tabla {
    position:relative;
    animation:scroll 40s linear infinite;
}

@keyframes scroll {
    0% { top:100%; }
    100% { top:-100%; }
}
</style>

</head>

<body>

<div class="header">
    <div>✈️ PANEL LOGÍSTICO</div>
    <div id="hora"></div>
</div>

<div class="kpis">
    <div class="kpi">Total<span id="total">0</span></div>
    <div class="kpi">Programados<span id="programados">0</span></div>
    <div class="kpi">En tránsito<span id="transito">0</span></div>
    <div class="kpi">Arribados<span id="arribados">0</span></div>
    <div class="kpi">🚨 Retrasados<span id="retrasados">0</span></div>
</div>

<div class="contenedor">
<table>
<thead>
<tr>
<th>Servicio</th>
<th>Empresa</th>
<th>Origen</th>
<th>Destino</th>
<th>ETD</th>
<th>ETA</th>
<th>Estado</th>
</tr>
</thead>
<tbody id="tabla"></tbody>
</table>
</div>

<!-- 🔊 SONIDO -->
<audio id="alerta" src="https://www.soundjay.com/buttons/beep-01a.mp3"></audio>

<script>

let alertaActiva = false;

// reloj
setInterval(()=> {
    document.getElementById('hora').innerText = new Date().toLocaleString();
},1000);

// 🔊 sonido controlado (NO spam)
function sonarAlerta(){
    if(!alertaActiva){
        document.getElementById('alerta').play();
        alertaActiva = true;

        setTimeout(()=> alertaActiva = false, 5000);
    }
}

// cargar tabla
function cargar(){
fetch('data.php')
.then(r=>r.json())
.then(data=>{

let html='';

data.forEach(s=>{

let clase = '';

if(s.estado_logistico === 'arribado'){
    let ahora = new Date();
    let eta = new Date(s.eta);

    if(s.eta && eta < ahora){
        clase = 'retrasado parpadeo';
        sonarAlerta();
    }
}

html+=`
<tr class="${clase}">
<td>${s.servicio}</td>
<td>${s.empresa}</td>
<td>${s.origen||''}</td>
<td>${s.destino||''}</td>
<td>${s.etd||''}</td>
<td>${s.eta||''}</td>
<td class="${s.estado_logistico}">
${s.estado_logistico.replace('_',' ')}
</td>
</tr>`;
});

document.getElementById('tabla').innerHTML = html;

});
}

// cargar KPIs
function stats(){
fetch('stats.php')
.then(r=>r.json())
.then(s=>{
document.getElementById('total').innerText = s.total;
document.getElementById('programados').innerText = s.programados;
document.getElementById('transito').innerText = s.transito;
document.getElementById('arribados').innerText = s.arribados;
document.getElementById('retrasados').innerText = s.retrasados;
});
}

// intervalos
setInterval(cargar,5000);
setInterval(stats,5000);

cargar();
stats();

</script>

</body>
</html>