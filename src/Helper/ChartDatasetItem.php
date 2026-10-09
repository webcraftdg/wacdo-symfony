<?php

namespace App\Helper;

final class ChartDatasetItem
{
   public function __construct(
        private string $label,
        private array $data,
        private array $options = []
   )
   {}

   public function toArray() : array
   {
        $data = [
            'label' => $this->label,
            'data' => $this->data,
        ];
        foreach($this->options as $key => $value) {
            $data[$key] = $value;
        }
        return $data;
   }

}
