<?php

class Graph
{

    const IMG_HEIGHT      = 900;
    const IMG_WIDTH       = 1900;
    const MARGIN_BOTTOM   = 20;
    const MARGIN          = 5;
    const FONT_WIDTH      = 9;
    const COUNT_Y         = 10;
    const DEFAULT_COUNT_X = 10;

    public $measurements = [];
    private $image;
    private $marginLeft  = 8;
    private $marginRight = 0;
    private $textWidth   = 0;
    private $signs_x     = [];
    private $colors;
    private $realHeight;
    private $realWidth;
    private $step_y;
    private $step_x;
    private $maxValue;
    private $count_x;
    private $count;
    private $x0;
    private $y0;
    private $calc;

    public function __construct(CalculationLargeNumbers $calc)
    {
        $this->calc = $calc;
    }

    public function get_microtime(): float
    {
        list($usec, $sec) = explode(' ', microtime());
        $time = ((float) $usec + (float) $sec);
        return $time;
    }

    public function end_of_metering(float $start)
    {
        $finish = (string) $this->get_microtime();
        $start  = (string) $start;
        $result = (float) $this->calc->diff_array([$finish, $start]);

        $this->measurements[] = $result;
    }

    private function set_count()
    {
        $this->count = count($this->measurements);
    }

    private function create_image()
    {
        $this->image = imagecreate(self::IMG_WIDTH, self::IMG_HEIGHT);
    }

    private function get_html_image($image, string $width = '90%')
    {
        return "<br><img src='data:image/jpeg;base64," . base64_encode($image) . "' width='" . $width . "'><br>";
    }

    private function averaging_values()
    {
        if ($this->count < 5) {
            return;
        }
        $measurements = $this->measurements;
        for ($i = 2; $i < $this->count - 2; $i++) {
            $this->measurements[$i] = ($measurements[$i - 1] +
                    $measurements[$i - 2] +
                    $measurements[$i] +
                    $measurements[$i + 1] +
                    $measurements[$i + 2]) / 5;
        }
    }

    private function set_colors($image)
    {
        $colors = [
            'bgColors'  => [
                imagecolorallocate($image, 255, 255, 255),
                imagecolorallocate($image, 231, 231, 231),
                imagecolorallocate($image, 212, 212, 212),
            ],
            'gridColor' => imagecolorallocate($image, 184, 184, 184),
            'textColor' => imagecolorallocate($image, 136, 136, 136),
            'barColor'  => imagecolorallocate($image, 191, 65, 170),
        ];

        $this->colors = $colors;
    }

    private function set_max_value()
    {
        $this->maxValue = max($this->measurements);
        // Максимальное значение, увеличенное на 10%
        $this->maxValue = $this->maxValue + ($this->maxValue / 10);
    }

    private function add_text_width_to_margin_left()
    {
        for ($i = 1; $i <= self::COUNT_Y; $i++) {
            $strlen = mb_strlen(($this->maxValue / self::COUNT_Y) * $i) * self::FONT_WIDTH;
            if ($strlen > $this->textWidth) {
                $this->textWidth = $strlen;
            }
        }

        $this->marginLeft += $this->textWidth;
    }

    private function set_signs_x()
    {
        $this->set_count_x();
        $this->signs_x = [];
        $step          = $this->count / ($this->count_x - 1);

        for ($i = 0; $i < $this->count_x; $i++) {
            $this->signs_x[] = round($i * $step);
        }

        $this->set_margin_right();
    }

    private function set_margin_right()
    {
        $last_sign_x       = $this->signs_x[count($this->signs_x) - 1];
        $this->marginRight = mb_strlen($last_sign_x) * self::FONT_WIDTH / 2;
    }

    private function set_count_x()
    {
        if ($this->count > self::DEFAULT_COUNT_X) {
            $this->count_x = self::DEFAULT_COUNT_X;
        } else {
            $this->count_x = $this->count;
        }
    }

    private function draw_grid_y()
    {
        for ($i = 1; $i <= self::COUNT_Y; $i++) {
            $y = $this->y0 - $this->step_y * $i;
            imageline($this->image, $this->x0, $y, $this->x0 + $this->realWidth, $y, $this->colors['gridColor']);
            imageline($this->image, $this->x0, $y, $this->x0 - ($this->marginLeft - $this->textWidth) / 4, $y, $this->colors['textColor']);
        }
    }

    private function draw_grid_x()
    {
        for ($i = 0; $i < $this->count_x; $i++) {
            $x = $this->x0 + $i * $this->step_x;
            imageline($this->image, $x, $this->y0, $x, $this->y0 - $this->realHeight, $this->colors['gridColor']);
        }
    }

    private function draw_graph_lines()
    {
        $y1 = $this->y0 - ($this->realHeight / $this->maxValue * $this->measurements[0]);
        $x1 = $this->x0;

        for ($i = 1; $i < $this->count; $i++) {
            $x2 = intval($this->x0 + $i * ($this->realWidth / ($this->count - 1)));
            $y2 = $this->y0 - ($this->realHeight / $this->maxValue * $this->measurements[$i]);

            imageline($this->image, $x1, $y1, $x2, $y2, $this->colors['barColor']);
            $y1 = $y2;
            $x1 = $x2;
        }
    }

    private function draw_sign_y()
    {
        for ($i = 0; $i <= self::COUNT_Y; $i++) {
            $str = ($this->maxValue / self::COUNT_Y) * $i;
            $x   = $this->x0 - mb_strlen($str) * self::FONT_WIDTH - $this->marginLeft / 4 - 2;
            $y   = $this->y0 - $this->step_y * $i - imagefontheight(4) / 2;
            imagestring($this->image, 6, $x, $y, $str, $this->colors['textColor']);
        }
    }

    private function draw_sign_x()
    {
        for ($i = 0; $i <= $this->count_x; $i++) {
            $str = $this->signs_x[$i];
            $x   = $this->x0 + $i * $this->step_x;
            imageline($this->image, $x, $this->y0, $x, $this->y0 + 5, $this->colors['textColor']);
            imagestring($this->image, 6, $x - (mb_strlen($str) * self::FONT_WIDTH) / 2, $this->y0 + 7, $str, $this->colors['textColor']);
        }
    }

    private function set_real_sizes()
    {
        $this->realWidth  = self::IMG_WIDTH - $this->marginLeft - self::MARGIN - $this->marginRight;
        $this->realHeight = self::IMG_HEIGHT - self::MARGIN_BOTTOM - self::MARGIN;
    }

    public function init()
    {
        $this->set_count();
        $this->set_max_value();
        $this->set_signs_x();
        $this->averaging_values();

        $this->create_image();
        $this->set_colors($this->image);
        $this->add_text_width_to_margin_left();
        $this->set_real_sizes();

        $this->x0 = $this->marginLeft;
        $this->y0 = self::IMG_HEIGHT - self::MARGIN_BOTTOM;

        $this->step_y = $this->realHeight / self::COUNT_Y;
        $this->step_x = $this->realWidth / ($this->count_x - 1);

        $arr1 = [
            'x1'    => $this->x0,
            'x2'    => $this->x0 + $this->realWidth,
            'y1'    => $this->y0 - $this->realHeight,
            'y2'    => $this->y0,
            'color' => $this->colors['bgColors'][1],
        ];
        $arr2 = [
            'x1'    => $this->x0,
            'x2'    => $this->x0 + $this->realWidth,
            'y1'    => $this->y0,
            'y2'    => $this->y0 - $this->realHeight,
            'color' => $this->colors['gridColor'],
        ];
        imagefilledrectangle($this->image, $arr1['x1'], $arr1['y1'], $arr1['x2'], $arr1['y2'], $arr1['color']);
        imagerectangle($this->image, $arr2['x1'], $arr2['y1'], $arr2['x2'], $arr2['y2'], $arr2['color']);
        unset($arr1);
        unset($arr2);

        $this->draw_grid_y();
        $this->draw_grid_x();

        $this->draw_graph_lines();

        $this->marginLeft -= $this->textWidth;

        $this->draw_sign_y();
        $this->draw_sign_x();

        ob_start();
        imagejpeg($this->image, null, 100);
        $finalImage = ob_get_clean();
        imagedestroy($this->image);

        return $this->get_html_image($finalImage);
    }

}
