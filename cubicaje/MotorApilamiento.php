<?php

class MotorApilamiento
{
    /**
     * Verifica si una caja puede colocarse encima de otra
     */
    public static function puedeApilar($cajaInferior, $cajaSuperior)
    {
        // base superior debe ser menor o igual a base inferior
        if ($cajaSuperior['l'] > $cajaInferior['l']) return false;
        if ($cajaSuperior['a'] > $cajaInferior['a']) return false;

        // opcional: peso máximo soportado
        if (isset($cajaInferior['peso_max']) && isset($cajaSuperior['peso'])) {
            if ($cajaSuperior['peso'] > $cajaInferior['peso_max']) {
                return false;
            }
        }

        return true;
    }

    /**
     * Calcula altura total al apilar
     */
    public static function alturaTotal($cajaInferior, $cajaSuperior)
    {
        return $cajaInferior['h'] + $cajaSuperior['h'];
    }
}