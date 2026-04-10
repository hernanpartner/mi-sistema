<?php

class MotorRotaciones
{
    public static function rotacionesValidas($l, $a, $h, $contenedor)
    {
        $rotaciones = [
            ['l'=>$l,'a'=>$a,'h'=>$h],
            ['l'=>$l,'a'=>$h,'h'=>$a],
            ['l'=>$a,'a'=>$l,'h'=>$h],
            ['l'=>$a,'a'=>$h,'h'=>$l],
            ['l'=>$h,'a'=>$l,'h'=>$a],
            ['l'=>$h,'a'=>$a,'h'=>$l],
        ];

        $validas = [];

        foreach ($rotaciones as $r) {

            // 🔥 si no existe puerta_ancho, usar ancho normal
            $puerta = $contenedor['puerta_ancho'] ?? $contenedor['ancho'];

            if (
                $r['l'] <= $contenedor['largo'] &&
                $r['a'] <= $contenedor['ancho'] &&
                $r['h'] <= $contenedor['alto'] &&
                $r['a'] <= $puerta // 🔥 control de puerta
            ) {
                $validas[] = $r;
            }
        }

        return $validas;
    }
}