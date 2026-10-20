<?php
/**
 * PageTitle.php
 *
 * PHP Version 8.3+
 *
 * @author David Ghyse <david.ghyse@webcraftdg.fr>
 * @version XXX
 * @package App\Attribute
 */
namespace App\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class PageTitle
{

    /**
     * constructor
     *
     * @param  string      $title
     * @param  string      $application
     * @param  string|null $section
     * @param  string      $separator
     */
    public function __construct(
        private string $title,
        private string $application = 'WacDo',
        private ?string $section = null,
        private string $separator = ' - '
    )
    {

    }

    public function __toString() : string
    {
        $separator = ($this->separator) ?? ' - ';
        return implode($separator, array_filter(
            [
                $this->title,
                $this->section,
                $this->application
            ]
        ));
    }
}
