<?php

class MotorPosiciones
{
    public static function cabeEnPosicion($caja, $pos, $contenedor)
    {
        if ($pos['x'] + $caja['l'] > $contenedor['largo']) return false;
        if ($pos['y'] + $caja['a'] > $contenedor['ancho']) return false;
        if ($pos['z'] + $caja['h'] > $contenedor['alto']) return false;

        return true;
    }

    public static function colisiona($caja, $pos, $otras)
    {
        foreach ($otras as $o) {

            if (
                $pos['x'] < $o['x'] + $o['l'] &&
                $pos['x'] + $caja['l'] > $o['x'] &&
                $pos['y'] < $o['y'] + $o['a'] &&
                $pos['y'] + $caja['a'] > $o['y'] &&
                $pos['z'] < $o['z'] + $o['h'] &&
                $pos['z'] + $caja['h'] > $o['z']
            ) {
                return true;
            }
        }

        return false;
    }
}