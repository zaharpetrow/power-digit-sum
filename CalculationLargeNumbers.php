<?php

class CalculationLargeNumbers
{

    public function sum_array(array $numbersArray): string
    {
        $result = '0';
        $this->validate($numbersArray);

        foreach ($numbersArray as $number) {
            $result = $this->sum_2_integers($result, $number);
        }

        return $result;
    }

    public function diff_array(array $numbersArray): string
    {
        $this->validate($numbersArray);
        $result = array_shift($numbersArray);

        foreach ($numbersArray as $number) {
            $result = $this->diff_2_integers($result, $number);
        }

        return $result;
    }

    private function validate(array $integers)
    {
        if (count($integers) < 1) {
            throw new Exception("Массив пуст");
        }

        foreach ($integers as $integer) {
            if (!is_string($integer) || !preg_match('/^\d+(\.\d+)?$/', $integer)) {
                throw new Exception("В строке '$integer' должны быть только цыфры");
            }
        }
    }

    private function is_float(string $int): bool
    {
        if (preg_match('/\./', $int)) {
            return true;
        } else {
            return false;
        }
    }

    private function integers_sizes(string $int1, string $int2): array
    {
        $lengthInt1 = mb_strlen($int1);
        $lengthInt2 = mb_strlen($int2);
        $minLength  = min([$lengthInt1, $lengthInt2]);
        $maxLength  = max([$lengthInt1, $lengthInt2]);

        return [
            'lengthInt1' => $lengthInt1,
            'lengthInt2' => $lengthInt2,
            'minLength'  => $minLength,
            'maxLength'  => $maxLength,
        ];
    }

    private function to_equal_after_dot(string &$int1, string &$int2)
    {
        if (!$this->is_float($int1)) {
            $int1 .= '.0';
        }
        if (!$this->is_float($int2)) {
            $int2 .= '.0';
        }

        $int1Array = explode('.', $int1);
        $int2Array = explode('.', $int2);

        $sizes        = $this->integers_sizes($int1Array[1], $int2Array[1]);
        $addingString = '';

        while (mb_strlen($addingString) < ($sizes['maxLength'] - $sizes['minLength'])) {
            $addingString .= '0';
        }

        if ($sizes['lengthInt1'] < $sizes['lengthInt2']) {
            $int1Array[1] .= $addingString;
        } elseif ($sizes['lengthInt1'] > $sizes['lengthInt2']) {
            $int2Array[1] .= $addingString;
        }

        $int1 = implode('.', $int1Array);
        $int2 = implode('.', $int2Array);
    }

    private function to_equal_symbols(string &$int1, string &$int2): int
    {
        if ($this->is_float($int1) || $this->is_float($int2)) {
            $this->to_equal_after_dot($int1, $int2);
        }

        $sizes        = $this->integers_sizes($int1, $int2);
        $addingString = '';

        while (mb_strlen($addingString) < ($sizes['maxLength'] - $sizes['minLength'])) {
            $addingString .= '0';
        }

        if ($sizes['lengthInt1'] < $sizes['lengthInt2']) {
            $int1 = $addingString . $int1;
        } elseif ($sizes['lengthInt1'] > $sizes['lengthInt2']) {
            $int2 = $addingString . $int2;
        }

        return $sizes['maxLength'];
    }

    private function sum_2_integers(string $int1, string $int2): string
    {
        $stringsLength = $this->to_equal_symbols($int1, $int2);
        $result        = '';
        $residue       = 0;
        for ($i = $stringsLength - 1; $i >= 0; $i--) {
            $sum     = $int1[$i] + $int2[$i] + $residue;
            $sum     .= '';
            $residue = 0;
            if (mb_strlen($sum) > 1) {
                $residue = 1;
                $sum     = $sum[1];
            }
            $result = $sum . $result;
        }
        if ($residue) {
            $result = $residue . $result;
        }

        return $result;
    }

    private function diff_minors(int &$minorInt1, int &$minorInt2, int &$residue)
    {
        $residueLocal = 0;

        if ($minorInt1 > 0 && $residue === 1) {
            $minorInt1 -= $residue;
            $residue   = 0;
        }

        if ($minorInt1 < $minorInt2 && $residue === 0) {
            $residueLocal = 1;
            $minorInt1    += 10;
        }

        if ($minorInt1 === 0 && $residue === 1) {
            $residueLocal = 1;
            $minorInt1    += 9;
        }

        $residue = $residueLocal;
    }

    private function diff_2_integers(string $int1, string $int2): string
    {
        $stringsLength = $this->to_equal_symbols($int1, $int2);
        $result        = '';
        $residue       = 0;

        for ($i = $stringsLength - 1; $i >= 0; $i--) {
            if ($int1[$i] === '.') {
                $result = '.' . $result;
                continue;
            }
            $minorInt1 = $int1[$i];
            $minorInt2 = $int2[$i];

            $this->diff_minors($minorInt1, $minorInt2, $residue);

            $diff   = $minorInt1 - $minorInt2;
            $result = $diff . $result;
        }

        return $this->trim_zeros($result);
    }

    private function trim_zeros(string $int): string
    {
        while (true) {
            $current = $int[0];
            $next    = $int[1];
            if ($current === '0' && mb_strlen($next) && $next !== '.') {
                $int = mb_substr($int, 1);
            } else {
                break;
            }
        }

        return $this->trim_zeros_after_dot($int);
    }

    private function trim_zeros_after_dot(string $int): string
    {
        if (!preg_match('/\./', $int)) {
            return $int;
        }

        $i = mb_strlen($int) - 1;

        while (true) {
            $current = $int[$i];
            if ($current === '0' || $current === '.') {
                $int = mb_substr($int, 0, $i);
            }

            if ($current === '.' || $current !== '0') {
                break;
            }

            $i--;
        }

        return $int;
    }

}
