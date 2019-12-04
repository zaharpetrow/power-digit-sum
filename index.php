<?php

class PowerDigitSum
{

    private $calculator;
    private $graph;

    public function __construct()
    {
        require_once 'CalculationLargeNumbers.php';
        require_once 'Graph.php';

        $this->calculator = new CalculationLargeNumbers();
        $this->graph      = new Graph($this->calculator);
    }

    private function add_zeros_to_string(string &$str, int $countZeros)
    {
        for ($i = 0; $i < $countZeros; $i++) {
            $str .= '0';
        }
    }

    private function multiply(string $int1, string $int2): string
    {
        $integersForSum = [];
        $residue        = '0';

        for ($i = mb_strlen($int1) - 1; $i >= 0; $i--) {
            $multiplyMajor = '';
            for ($j = mb_strlen($int2) - 1; $j >= 0; $j--) {
                $residue       = '0';
                $multiplyMinor = ($int2[$j] * $int1[$i]) . '';
                $countZeros    = ((mb_strlen($int1) - 1) - $i) + ((mb_strlen($int2) - 1) - $j);

                if (mb_strlen($multiplyMinor) > 1) {
                    $residue          = $multiplyMinor[0] . '0';
                    $multiplyMinor    = $multiplyMinor[1];
                    $this->add_zeros_to_string($residue, $countZeros);
                    $integersForSum[] = $residue;
                }

                $multiplyMajor = $multiplyMinor . $multiplyMajor;
                $this->add_zeros_to_string($multiplyMajor, $countZeros);
            }
            $integersForSum[] = $multiplyMajor;
        }

        return $this->calculator->sum_array($integersForSum);
    }

    private function pow(int $base, int $exp): string
    {
        $base   .= '';
        $result = $base;

        for ($i = 1; $i < $exp; $i++) {
            $start  = $this->graph->get_microtime();
            $result = $this->multiply($result, $base);
            $this->graph->end_of_metering($start);
        }
        return $result;
    }

    private function digit_sum(string $int): string
    {
        $sum = 0;

        for ($i = mb_strlen($int) - 1; $i >= 0; $i--) {
            $sum += $int[$i];
        }
        return $sum;
    }

    public function init(int $base, int $exp)
    {
        $result = $this->digit_sum($this->pow($base, $exp));
        echo $this->graph->init();

        return $result;
    }

}

echo '<pre>';
print_r((new PowerDigitSum())->init(2, 1000));
