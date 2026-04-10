<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Dashboard Logístico</title>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
body{
    background:#000;
    color:#00ffcc;
    font-family:monospace;
    margin:0;
}

.header{
    padding:10px;
    text-align:center;
    font-size:24px;
}

.grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:20px;
    padding:20px;
}

.card{
    background:#111;
    padding:20px;
    border-radius:10px;
}
</style>

</head>

<body>

<div class="header">📊 DASHBOARD LOGÍSTICO</div>

<div class="grid">

<div class="card">
<canvas id="estadoChart"></canvas>
</div>

<div class="card">
<canvas id="flujoChart"></canvas>
</div>

</div>

<script>

let estadoChart, flujoChart;

function cargar(){

fetch('stats.php')
.then(r=>r.json())
.then(data=>{

// 🔵 GRÁFICO ESTADOS
if(estadoChart) estadoChart.destroy();

estadoChart = new Chart(document.getElementById('estadoChart'), {
    type:'doughnut',
    data:{
        labels:['Programados','En tránsito','Arribados'],
        datasets:[{
            data:[
                data.programados,
                data.transito,
                data.arribados
            ]
        }]
    }
});

// 🔴 GRÁFICO ALERTAS
if(flujoChart) flujoChart.destroy();

flujoChart = new Chart(document.getElementById('flujoChart'), {
    type:'bar',
    data:{
        labels:['Total','Retrasados'],
        datasets:[{
            data:[
                data.total,
                data.retrasados
            ]
        }]
    }
});

});
}

// refresco
setInterval(cargar,5000);
cargar();

</script>

</body>
</html>