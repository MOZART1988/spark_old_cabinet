<?php

namespace App\Calculator;

class Calculator
{
    /**
     * Compute volume weight.
     * @param float|int $width
     * @param float|int $height
     * @param float|int $length
     * @return float|int
     * @throws \Exception
     */
    static public function volume_weight($width = null, $height = null, $length = null)
    {
        if(!$width || !is_numeric($width)) {
            throw new \Exception("Volume width ${width} error");
        }
        if(!$height || !is_numeric($height)) {
            throw new \Exception("Volume height ${height} error");
        }
        if(!$length || !is_numeric($length)) {
            throw new \Exception("Volume length ${length} error");
        }

        return $length * $height * $width / 5000;
    }
}