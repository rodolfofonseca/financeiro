<?php
class EAN13
{
    private $code12;
    private $checkDigit;
    private $fullCode;

    public function __construct($code12)
    {
        if (!preg_match('/^\d{12}$/', $code12)) {
            throw new InvalidArgumentException("O código deve conter exatamente 12 dígitos numéricos.");
        }

        $this->code12 = $code12;
        $this->checkDigit = $this->calculateCheckDigit();
        $this->fullCode = $this->code12 . $this->checkDigit;
    }

    private function calculateCheckDigit()
    {
        $digits = str_split($this->code12);

        $sumOdd = 0;
        $sumEven = 0;

        foreach ($digits as $index => $digit) {
            if (($index % 2) == 0) {
                $sumOdd += (int)$digit;
            } else {
                $sumEven += (int)$digit;
            }
        }

        $total = $sumOdd + ($sumEven * 3);
        $remainder = $total % 10;

        return ($remainder == 0) ? 0 : (10 - $remainder);
    }

    public function getFullCode()
    {
        return $this->fullCode;
    }

    public function __toString()
    {
        return $this->fullCode;
    }
}
?>