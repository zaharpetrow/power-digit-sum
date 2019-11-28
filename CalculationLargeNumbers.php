<?php

class CalculationLargeNumbers
{

    private function validate(array $integers)
    {
        if (count($integers) < 1) {
            throw new Exception("В массиве менее 2 чисел");
        }

        foreach ($integers as $integer) {
            if (!is_string($integer) || !preg_match('/^\d+(\.\d+)?$/', $integer)) {
                throw new Exception("В строке '$integer' должны быть только цыфры");
            }
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

    public function to_equal_symbols(string &$int1, string &$int2): int
    {
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

    public function sum_array(array $numbersArray): string
    {
        $result = '0';
        $this->validate($numbersArray);

        foreach ($numbersArray as $number) {
            $result = $this->sum_2_integers($result, $number);
        }

        return $result;
    }

}
