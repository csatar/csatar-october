<?php

namespace Csatar\KnowledgeRepository\Classes\Xlsx;

use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\RichText\Run;
use PhpOffice\PhpSpreadsheet\Settings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Html;
class XlxsHtml extends Html
{

    public function generateHtmlFromCell(Worksheet $worksheet, Cell $cell, $cssClass = '', $cellType = 'td')
    {
        return $this->generateRowCellData($worksheet, $cell, $cssClass, $cellType);
    }

    private function generateRowCellData(Worksheet $worksheet, $cell, &$cssClass, string $cellType): string
    {
        $cellData = '&nbsp;';
        if ($cell instanceof Cell) {
            $cellData = '';

            $this->generateRowCellDataValue($worksheet, $cell, $cellData);

            $cellData = (string) preg_replace('/(?m)(?:^|\\G) /', '&nbsp;', $cellData);

            $cellData = nl2br($cellData);
        } else {
            if (is_string($cssClass)) {
                $cssClass .= ' style0';
            }
        }

        return $cellData;
    }

    private function generateRowCellDataValue(Worksheet $worksheet, Cell $cell, string &$cellData): void
    {
        if ($cell->getValue() instanceof RichText) {
            $this->generateRowCellDataValueRich($cell, $cellData);
        } else {
            $origData   = $this->preCalculateFormulas ? $cell->getCalculatedValue() : $cell->getValue();
            $formatCode = $worksheet->getParentOrThrow()->getCellXfByIndex($cell->getXfIndex())->getNumberFormat()->getFormatCode();

            $cellData = NumberFormat::toFormattedString(
                $origData ?? '',
                $formatCode ?? NumberFormat::FORMAT_GENERAL,
                [$this, 'formatColor']
            );

            if ($cellData === $origData) {
                $cellData = htmlspecialchars($cellData, Settings::htmlEntityFlags());
            }

            if ($worksheet->getParentOrThrow()->getCellXfByIndex($cell->getXfIndex())->getFont()->getSuperscript()) {
                $cellData = '<sup>' . $cellData . '</sup>';
            } elseif ($worksheet->getParentOrThrow()->getCellXfByIndex($cell->getXfIndex())->getFont()->getSubscript()) {
                $cellData = '<sub>' . $cellData . '</sub>';
            }
        }
    }

    private function generateRowCellDataValueRich(Cell $cell, string &$cellData): void
    {
        // Loop through rich text elements
        $elements = $cell->getValue()->getRichTextElements();
        foreach ($elements as $element) {
            // Rich text start?
            if ($element instanceof Run) {
                $cellEnd = '';
                if ($element->getFont() !== null) {
                    $cellData .= '<span style="' . $this->assembleCSS($this->createCSSStyleFont($element->getFont())) . '">';

                    if ($element->getFont()->getSuperscript()) {
                        $cellData .= '<sup>';
                        $cellEnd   = '</sup>';
                    } elseif ($element->getFont()->getSubscript()) {
                        $cellData .= '<sub>';
                        $cellEnd   = '</sub>';
                    }
                }

                // Convert UTF8 data to PCDATA
                $cellText  = $element->getText();
                $cellData .= htmlspecialchars($cellText, Settings::htmlEntityFlags());

                $cellData .= $cellEnd;

                $cellData .= '</span>';
            } else {
                // Convert UTF8 data to PCDATA
                $cellText  = $element->getText();
                $cellData .= htmlspecialchars($cellText, Settings::htmlEntityFlags());
            }
        }
    }

    private function assembleCSS(array $values = [])
    {
        $pairs = [];
        foreach ($values as $property => $value) {
            $pairs[] = $property . ':' . $value;
        }

        $string = implode('; ', $pairs);

        return $string;
    }

    private function createCSSStyleFont(Font $font)
    {
        $css = [];

        if ($font->getBold()) {
            $css['font-weight'] = 'bold';
        }

        if ($font->getUnderline() != Font::UNDERLINE_NONE && $font->getStrikethrough()) {
            $css['text-decoration'] = 'underline line-through';
        } elseif ($font->getUnderline() != Font::UNDERLINE_NONE) {
            $css['text-decoration'] = 'underline';
        } elseif ($font->getStrikethrough()) {
            $css['text-decoration'] = 'line-through';
        }

        if ($font->getItalic()) {
            $css['font-style'] = 'italic';
        }

        $css['color']       = '#' . $font->getColor()->getRGB();
        $css['font-family'] = '\'' . $font->getName() . '\'';
        $css['font-size']   = $font->getSize() . 'pt';

        return $css;
    }

}
