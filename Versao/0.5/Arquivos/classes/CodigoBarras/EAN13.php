<?php
class EAN13
{
    private mixed $code12;
    private mixed $checkDigit;
    private mixed $fullCode;

    public function __construct(){
        
    }

    /**
     * Função responsável por fazer a validação do digito verificador do código de barras
     * @param mixed $code12
     * @throws InvalidArgumentException
     * @return void
     */
    private function validateCheckDigit($code12)
    {
        if (!preg_match('/^\d{12}$/', $code12)) {
            throw new InvalidArgumentException("O código deve conter exatamente 12 dígitos numéricos.");
        }

        $this->code12 = $code12;
        $this->checkDigit = $this->calculateCheckDigit();
        $this->fullCode = $this->code12 . $this->checkDigit;
    }

    /**
     * Função responsável por calcular o digito verificador
     * @return int
     */
    private function calculateCheckDigit()
    {
        $digits = str_split($this->code12);

        $sumOdd = 0;
        $sumEven = 0;

        foreach ($digits as $index => $digit) {
            if (($index % 2) == 0) {
                $sumOdd += (int) $digit;
            } else {
                $sumEven += (int) $digit;
            }
        }

        $total = $sumOdd + ($sumEven * 3);
        $remainder = $total % 10;

        return ($remainder == 0) ? 0 : (10 - $remainder);
    }

    /**
     * Função responsável por validar a quantidade de digitos de um código de barras, recebendo 12 digitos, calculando o 13
     * @param string $code12 - Código que será calculado
     * @return string
     */
    public function getFullCode($code12 = '')
    {
        if($code12 == ''){ 
            $code12 = (string) microtime(true);
            $code12 = (string) str_replace('.', '', $code12);
            $code12 = (string) str_replace(' ', '', $code12);

            $code12 = (string) substr($code12,0,12);
        }

        $this->validateCheckDigit($code12);
        return (string) $this->fullCode;
    }
}
?>