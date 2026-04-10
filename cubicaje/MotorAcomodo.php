<?php

require_once "MotorApilamiento.php";

class MotorAcomodo {

    public static function acomodar($contenedor, $cajas){

        $L = $contenedor['largo'];
        $A = $contenedor['ancho'];
        $H = $contenedor['alto'];
        $pesoMax = $contenedor['peso_max'] ?? 999999;

        $contenedores = [];

        // 🔥 ORDEN MEJORADO: PESO + VOLUMEN (BASE FUERTE ABAJO)
        usort($cajas, function($a,$b){
            $volA = $a['l']*$a['a']*$a['h'];
            $volB = $b['l']*$b['a']*$b['h'];

            // primero peso, luego volumen
            if($b['peso'] == $a['peso']){
                return $volB <=> $volA;
            }
            return $b['peso'] <=> $a['peso'];
        });

        $pendientes = $cajas;

        while(count($pendientes) > 0){

            $espacios = [[
                'x'=>0,'y'=>0,'z'=>0,
                'l'=>$L,'a'=>$A,'h'=>$H
            ]];

            $actual = [];
            $pesoActual = 0;
            $colocoAlgo = false;

            foreach($pendientes as $key => $caja){

                $mejor = null;
                $mejorScore = PHP_INT_MAX;

                foreach($espacios as $ei => $esp){

                    $orientaciones = [];

                    if($caja['rotable']){
                        $orientaciones = [
                            [$caja['l'],$caja['a'],$caja['h']],
                            [$caja['a'],$caja['l'],$caja['h']],
                            [$caja['h'],$caja['l'],$caja['a']],
                            [$caja['l'],$caja['h'],$caja['a']],
                            [$caja['a'],$caja['h'],$caja['l']],
                            [$caja['h'],$caja['a'],$caja['l']]
                        ];
                    } else {
                        $orientaciones = [
                            [$caja['l'],$caja['a'],$caja['h']]
                        ];
                    }

                    foreach($orientaciones as $o){

                        list($l,$a,$h) = $o;

                        // 🔥 PRIORIDAD: PRIMERO PISO
                        if($esp['z'] > 0 && $caja['apilable'] == 0){
                            continue;
                        }

                        if(
                            $l <= $esp['l'] &&
                            $a <= $esp['a'] &&
                            $h <= $esp['h']
                        ){

                            if(($pesoActual + $caja['peso']) > $pesoMax){
                                continue;
                            }

                            // 🔥 SCORE MEJORADO (PRIORIDAD ORDEN REAL)
                            $score =
                                ($esp['z'] * 1000000) +   // prioridad piso
                                ($esp['y'] * 1000) +      // atrás → adelante
                                ($esp['x']);              // izquierda → derecha

                            if($score < $mejorScore){

                                $mejorScore = $score;

                                $mejor = [
                                    'l'=>$l,'a'=>$a,'h'=>$h,
                                    'espacio'=>$esp,
                                    'espIndex'=>$ei
                                ];
                            }
                        }
                    }
                }

                if($mejor){

                    $esp = $mejor['espacio'];

                    $x = $esp['x'];
                    $y = $esp['y'];
                    $z = $esp['z'];

                    $l = $mejor['l'];
                    $a = $mejor['a'];
                    $h = $mejor['h'];

                    // 🔥 VALIDAR SOPORTE REAL (CLAVE)
                    if($z > 0){

                        $soporteValido = false;

                        foreach($actual as $base){

                            if($base['z'] + $base['h'] == $z){

                                $areaBase = $base['l'] * $base['a'];
                                $areaCaja = $l * $a;

                                if(
                                    $caja['peso'] <= $base['peso'] && // 🔥 peso correcto
                                    $areaCaja <= $areaBase &&         // 🔥 no sobresale
                                    MotorApilamiento::puedeApilar($base, [
                                        'l'=>$l,'a'=>$a,'h'=>$h,'peso'=>$caja['peso']
                                    ])
                                ){
                                    $soporteValido = true;
                                    break;
                                }
                            }
                        }

                        if(!$soporteValido){
                            continue;
                        }
                    }

                    $actual[] = [
                        'x'=>$x,'y'=>$y,'z'=>$z,
                        'l'=>$l,'a'=>$a,'h'=>$h,
                        'peso'=>$caja['peso'],
                        'color'=>$caja['color'] ?? '#ff0000'
                    ];

                    $pesoActual += $caja['peso'];
                    $colocoAlgo = true;

                    unset($espacios[$mejor['espIndex']]);

                    // 🔥 DIVISIÓN DE ESPACIOS CORREGIDA

                    // DERECHA
                    $espacios[] = [
                        'x'=>$x + $l,
                        'y'=>$y,
                        'z'=>$z,
                        'l'=>$esp['l'] - $l,
                        'a'=>$esp['a'],
                        'h'=>$esp['h']
                    ];

                    // FRENTE
                    $espacios[] = [
                        'x'=>$x,
                        'y'=>$y + $a,
                        'z'=>$z,
                        'l'=>$l,
                        'a'=>$esp['a'] - $a,
                        'h'=>$esp['h']
                    ];

                    // ARRIBA (solo si es apilable)
                    if($caja['apilable']){
                        $espacios[] = [
                            'x'=>$x,
                            'y'=>$y,
                            'z'=>$z + $h,
                            'l'=>$l,
                            'a'=>$a,
                            'h'=>$esp['h'] - $h
                        ];
                    }

                    unset($pendientes[$key]);
                }
            }

            if($colocoAlgo){
                $contenedores[] = [
                    'cajas'=>$actual,
                    'peso'=>$pesoActual
                ];
            } else {
                break;
            }
        }

        return $contenedores;
    }
}