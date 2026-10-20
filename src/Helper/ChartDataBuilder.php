<?php
/**
 * ChartDataBuilder.php
 *
 * PHP Version 8.3+
 *
 * @author David Ghyse <david.ghyse@webcraftdg.fr>
 * @version XXX
 * @package App\Helper
 */
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
