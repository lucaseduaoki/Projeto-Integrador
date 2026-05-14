<?php 

namespace app\helpers;

class Validador {

    private array $erros = [];

    public function obrigatorio(string $campo, mixed $valor, ?string $mensagem = null) {

        //! = = 
        if (empty($valor) && $valor !== '0') {
            $this->erros[$campo] = $mensagem ?? "O campo {$campo} é obrigatório";
        }

        return $this;

    }

    public function tamanhoMinMax(string $campo, ?string $valor, int $min, int $max, ?string $mensagem = null) {

        if ($valor === null) {
            return $this;
        }

        $tamanho = strlen(trim($valor));
        if ($tamanho < $min || $tamanho > $max) {
            $this->erros[$campo] = $mensagem ?? "O campo {$campo} deve ter entre {$min} e {$max} caracteres";
        }

        return $this;

    }

    public function tamanhoMax(string $campo, ?string $valor, int $max, ?string $mensagem = null) {

        if ($valor === null) {
            return $this;
        }

        $tamanho = strlen(trim($valor));
        if ($tamanho > $max) {
            $this->erros[$campo] = $mensagem ?? "O campo {$campo} deve ter no maximo {$max} caracteres";
        }

        return $this;

    }

    public function dataValida(string $campo, ?string $valor, bool $naoFuturo = false, ?string $mensagem = null) {

        if ($valor === null || $valor === '') {
            return $this;
        }

        $data = \DateTime::createFromFormat('Y-m-d', $valor);
        $erros = \DateTime::getLastErrors();
        $warningCount = is_array($erros) ? $erros['warning_count'] : 0;
        $errorCount = is_array($erros) ? $erros['error_count'] : 0;

        if (!$data || $warningCount > 0 || $errorCount > 0 || $data->format('Y-m-d') !== $valor) {
            $this->erros[$campo] = $mensagem ?? "O campo {$campo} deve ser uma data valida";
            return $this;
        }

        if ($naoFuturo) {
            $hoje = new \DateTime('today');
            if ($data > $hoje) {
                $this->erros[$campo] = $mensagem ?? "O campo {$campo} nao pode ser uma data futura";
            }
        }

        return $this;

    }

    public function emLista(string $campo, ?string $valor, array $lista, ?string $mensagem = null) {

        if ($valor === null || $valor === '') {
            return $this;
        }

        if (!in_array($valor, $lista, true)) {
            $this->erros[$campo] = $mensagem ?? "O campo {$campo} possui um valor invalido";
        }

        return $this;

    }

    public function emailValido(string $campo, ?string $valor, ?string $mensagem = null) {

        if ($valor === null || $valor === '') {
            return $this;
        }

        if (!filter_var($valor, FILTER_VALIDATE_EMAIL)) {
            $this->erros[$campo] = $mensagem ?? "O campo {$campo} deve ser um e-mail valido";
        }

        return $this;

    }

    public function email(string $campo, string $valor, ?string $mensagem = null): self
    {
        return $this->emailValido($campo, $valor, $mensagem);
    }

    public function minimo(string $campo, string $valor, int $min, ?string $mensagem = null): self
    {
        if (strlen(trim($valor)) < $min) {
            $this->erros[$campo] = $mensagem ?? "O campo {$campo} deve ter no mínimo {$min} caracteres";
        }
        return $this;
    }

    public function maximo(string $campo, string $valor, int $max, ?string $mensagem = null): self
    {
        if (strlen(trim($valor)) > $max) {
            $this->erros[$campo] = $mensagem ?? "O campo {$campo} deve ter no máximo {$max} caracteres";
        }
        return $this;
    }

    public function numerico(string $campo, mixed $valor, ?string $mensagem = null): self
    {
        if ($valor === null || $valor === '') {
            return $this;
        }

        if (!is_numeric($valor)) {
            $this->erros[$campo] = $mensagem ?? "O campo {$campo} deve ser numérico";
        }
        return $this;
    }

    public function notaAvaliacao(string $campo, mixed $valor, ?string $mensagem = null): self
    {
        if ($valor === null || $valor === '') {
            return $this;
        }

        $nota = (int)$valor;
        if ($nota < 1 || $nota > 5) {
            $this->erros[$campo] = $mensagem ?? "O campo {$campo} deve ser uma nota entre 1 e 5";
        }
        return $this;
    }

    public function cpf(string $campo, string $valor, ?string $mensagem = null): self
    {
        if ($valor === null || $valor === '') {
            return $this;
        }

        // Remove caracteres não numéricos
        $cpf = preg_replace('/\D/', '', $valor);

        // Valida tamanho
        if (strlen($cpf) !== 11) {
            $this->erros[$campo] = $mensagem ?? "CPF inválido";
            return $this;
        }

        // Valida sequências repetidas
        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            $this->erros[$campo] = $mensagem ?? "CPF inválido";
            return $this;
        }

        // Calcula primeiro dígito verificador
        $soma = 0;
        for ($i = 0; $i < 9; $i++) {
            $soma += (int)$cpf[$i] * (10 - $i);
        }
        $resto = $soma % 11;
        $digito1 = $resto < 2 ? 0 : 11 - $resto;

        // Calcula segundo dígito verificador
        $soma = 0;
        for ($i = 0; $i < 10; $i++) {
            $soma += (int)$cpf[$i] * (11 - $i);
        }
        $resto = $soma % 11;
        $digito2 = $resto < 2 ? 0 : 11 - $resto;

        // Valida dígitos verificadores
        if ((int)$cpf[9] !== $digito1 || (int)$cpf[10] !== $digito2) {
            $this->erros[$campo] = $mensagem ?? "CPF inválido";
            return $this;
        }

        return $this;
    }

    public function cnpj(string $campo, string $valor, ?string $mensagem = null): self
    {
        if ($valor === null || $valor === '') {
            return $this;
        }

        // Remove caracteres não numéricos
        $cnpj = preg_replace('/\D/', '', $valor);

        // Valida tamanho
        if (strlen($cnpj) !== 14) {
            $this->erros[$campo] = $mensagem ?? "CNPJ inválido";
            return $this;
        }

        // Valida sequências repetidas
        if (preg_match('/^(\d)\1{13}$/', $cnpj)) {
            $this->erros[$campo] = $mensagem ?? "CNPJ inválido";
            return $this;
        }

        // Calcula primeiro dígito verificador
        $soma = 0;
        $multiplicador = 5;
        for ($i = 0; $i < 12; $i++) {
            $soma += (int)$cnpj[$i] * $multiplicador;
            $multiplicador = $multiplicador === 2 ? 9 : $multiplicador - 1;
        }
        $resto = $soma % 11;
        $digito1 = $resto < 2 ? 0 : 11 - $resto;

        // Calcula segundo dígito verificador
        $soma = 0;
        $multiplicador = 6;
        for ($i = 0; $i < 13; $i++) {
            $soma += (int)$cnpj[$i] * $multiplicador;
            $multiplicador = $multiplicador === 2 ? 9 : $multiplicador - 1;
        }
        $resto = $soma % 11;
        $digito2 = $resto < 2 ? 0 : 11 - $resto;

        // Valida dígitos verificadores
        if ((int)$cnpj[12] !== $digito1 || (int)$cnpj[13] !== $digito2) {
            $this->erros[$campo] = $mensagem ?? "CNPJ inválido";
            return $this;
        }

        return $this;
    }

    public function temErros() : bool {

        return !empty($this->erros);

    }

    public function getErros(){
        return $this->erros;
    }

}

