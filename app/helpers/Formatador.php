<?php

namespace app\helpers;

class Formatador
{
    /**
     * Aplica máscara de CPF (11 dígitos) ou CNPJ (14 dígitos) conforme o tamanho do valor.
     * Valores com outro tamanho são devolvidos sem alteração (não há como saber a máscara certa).
     */
    public static function documento(?string $valor): string
    {
        $digitos = preg_replace('/\D/', '', (string)$valor);

        if (strlen($digitos) === 11) {
            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $digitos);
        }

        if (strlen($digitos) === 14) {
            return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $digitos);
        }

        return (string)$valor;
    }
}
