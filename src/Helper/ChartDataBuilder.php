<?php

namespace App\Helper;

final class ChartDataBuilder
{
    private array $labels = [];
    private array $datasets = [];


    public function addLabel(string $label) : self
    {
        $this->labels[] = $label;
        return $this;
    }

    public function addDataset(string $label, array $data, array $options = []) : self
    {
        $this->datasets[] = new ChartDatasetItem(
            label: $label,
            data: $data,
            options: $options
        );
        return $this;
    }

    public function toArray() : array
    {
        return  [
            'labels' => $this->labels,
            'datasets' => array_map(
                fn (ChartDatasetItem $dataset) => $dataset->toArray(),
                $this->datasets
            )
        ];
    }
}
