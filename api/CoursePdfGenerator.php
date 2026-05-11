<?php

final class CoursePdfGenerator
{
    public static function build(array $course): string
    {
        $title = trim((string) ($course['title'] ?? 'Course'));
        $description = trim((string) ($course['description'] ?? ''));
        $level = strtolower(trim((string) ($course['level'] ?? 'beginner')));

        $levelLabel = match ($level) {
            'intermediate' => 'Intermediate',
            'advanced' => 'Advanced',
            default => 'Beginner',
        };

        $id = (int) ($course['id'] ?? 0);
        $generatedAt = date('Y-m-d H:i');

        $content = self::buildContentStream([
            'title' => $title !== '' ? $title : 'Course',
            'level' => $levelLabel,
            'description' => $description,
            'id' => $id,
            'generatedAt' => $generatedAt,
        ]);

        return self::wrapAsPdf($content);
    }

    private static function buildContentStream(array $data): string
    {
        $pageWidth = 595;
        $pageHeight = 842;

        $left = 48;
        $right = 48;
        $top = $pageHeight - 62;
        $maxTextWidthChars = 90;

        $title = self::pdfText($data['title']);
        $level = self::pdfText($data['level']);
        $desc = (string) ($data['description'] ?? '');
        $descLines = self::wrapLines($desc, $maxTextWidthChars);
        $descLines = array_slice($descLines, 0, 18);

        $idLine = self::pdfText('Course ID: ' . (string) ($data['id'] ?? 0));
        $generatedLine = self::pdfText('Generated: ' . (string) ($data['generatedAt'] ?? ''));

        $y = $top;
        $stream = '';

        // Background
        $stream .= "1 1 1 rg 0 0 {$pageWidth} {$pageHeight} re f\n";

        // Header band
        $stream .= "0.18 0.29 0.78 rg 0 " . ($pageHeight - 90) . " {$pageWidth} 90 re f\n";
        $stream .= "0 0 0 rg\n";

        // Header title
        $stream .= "BT\n/F1 22 Tf\n1 1 1 rg\n{$left} " . ($pageHeight - 55) . " Td\n({$title}) Tj\nET\n";

        // Level badge
        $badgeX = $left;
        $badgeY = $pageHeight - 82;
        $badgeW = 140;
        $badgeH = 20;
        $stream .= "0.94 0.95 1 rg {$badgeX} {$badgeY} {$badgeW} {$badgeH} re f\n";
        $stream .= "0.18 0.29 0.78 rg 0.8 w {$badgeX} {$badgeY} {$badgeW} {$badgeH} re S\n";
        $stream .= "BT\n/F1 11 Tf\n0.18 0.29 0.78 rg\n" . ($badgeX + 10) . " " . ($badgeY + 6) . " Td\n(Level: {$level}) Tj\nET\n";

        // Main card
        $cardX = $left;
        $cardY = 140;
        $cardW = $pageWidth - $left - $right;
        $cardH = $pageHeight - 90 - 70 - 40;
        $stream .= "0.98 0.98 0.99 rg {$cardX} {$cardY} {$cardW} {$cardH} re f\n";
        $stream .= "0.85 0.87 0.92 rg 1 w {$cardX} {$cardY} {$cardW} {$cardH} re S\n";

        // Section heading
        $stream .= "BT\n/F1 16 Tf\n0.12 0.15 0.22 rg\n{$left} " . ($pageHeight - 135) . " Td\n(Course Overview) Tj\nET\n";

        // Description label
        $stream .= "BT\n/F1 12 Tf\n0.18 0.19 0.23 rg\n{$left} " . ($pageHeight - 165) . " Td\n(Description) Tj\nET\n";

        // Description body
        $startY = $pageHeight - 190;
        $lineHeight = 16;
        $stream .= "BT\n/F1 11 Tf\n0.22 0.23 0.26 rg\n{$left} {$startY} Td\n";
        if (count($descLines) === 0) {
            $stream .= "(No description provided.) Tj\n";
        } else {
            $first = true;
            foreach ($descLines as $line) {
                $t = self::pdfText($line);
                if (!$first) {
                    $stream .= "0 -" . $lineHeight . " Td\n";
                }
                $stream .= "({$t}) Tj\n";
                $first = false;
            }
        }
        $stream .= "ET\n";

        // Footer info
        $lineX2 = $pageWidth - $right;
        $stream .= "0.85 0.87 0.92 rg 0.8 w {$left} 110 m {$lineX2} 110 l S\n";
        $stream .= "BT\n/F1 10 Tf\n0.35 0.37 0.42 rg\n{$left} 95 Td\n({$idLine}) Tj\n0 -14 Td\n({$generatedLine}) Tj\nET\n";
        $stream .= "BT\n/F1 9 Tf\n0.55 0.57 0.62 rg\n" . ($pageWidth - $right - 190) . " 95 Td\n(Downloaded from Eduleb) Tj\nET\n";

        return $stream;
    }

    private static function pdfText(string $text): string
    {
        $text = str_replace(["\\", "(", ")", "\r"], ["\\\\", "\\(", "\\)", ""], $text);
        return str_replace("\n", ' ', $text);
    }

    private static function wrapLines(string $text, int $maxChars): array
    {
        $text = trim(preg_replace('/\s+/', ' ', $text) ?? '');
        if ($text === '') {
            return [];
        }

        $words = preg_split('/\s+/', $text) ?: [];
        $lines = [];
        $current = '';

        foreach ($words as $word) {
            $word = (string) $word;
            if ($current === '') {
                $current = $word;
                continue;
            }

            if (strlen($current) + 1 + strlen($word) <= $maxChars) {
                $current .= ' ' . $word;
                continue;
            }

            $lines[] = $current;
            $current = $word;
        }

        if ($current !== '') {
            $lines[] = $current;
        }

        return $lines;
    }

    private static function wrapAsPdf(string $contentStream): string
    {
        $objects = [];
        $offsets = [];

        $pdf = "%PDF-1.4\n";
        $pdf .= "%\xE2\xE3\xCF\xD3\n";

        $addObject = static function (string $obj) use (&$pdf, &$objects, &$offsets): void {
            $objects[] = $obj;
            $id = count($objects);
            $offsets[$id] = strlen($pdf);
            $pdf .= $id . " 0 obj\n" . $obj . "\nendobj\n";
        };

        // 1: Catalog
        $addObject("<< /Type /Catalog /Pages 2 0 R >>");
        // 2: Pages
        $addObject("<< /Type /Pages /Kids [3 0 R] /Count 1 >>");
        // 3: Page
        $addObject("<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>");
        // 4: Font
        $addObject("<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>");
        // 5: Content stream
        $stream = $contentStream;
        $streamObj = "<< /Length " . strlen($stream) . " >>\nstream\n" . $stream . "\nendstream";
        $addObject($streamObj);

        $xrefPos = strlen($pdf);
        $count = count($objects) + 1;

        $pdf .= "xref\n";
        $pdf .= "0 {$count}\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i < $count; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        $pdf .= "trailer\n";
        $pdf .= "<< /Size {$count} /Root 1 0 R >>\n";
        $pdf .= "startxref\n";
        $pdf .= $xrefPos . "\n";
        $pdf .= "%%EOF";

        return $pdf;
    }
}
