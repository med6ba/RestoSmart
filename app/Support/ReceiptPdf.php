<?php

namespace App\Support;

use App\Models\Order;

class ReceiptPdf
{
    private const PAGE_WIDTH = 612;

    private const PAGE_HEIGHT = 792;

    public static function make(Order $order): string
    {
        $order->loadMissing(['items', 'restaurantTable', 'driver']);

        $tenantName = function_exists('tenant') ? (string) tenant('name') : config('app.name', 'RestoSmart');

        return self::pdf(self::contentStream($order, $tenantName));
    }

    private static function contentStream(Order $order, string $tenantName): string
    {
        $placedAt = ($order->placed_at ?? $order->created_at)?->format('Y-m-d H:i') ?? '';
        $ops = [];

        self::fillRect($ops, 0, 0, self::PAGE_WIDTH, self::PAGE_HEIGHT, '1 1 1');
        self::fillRect($ops, 48, 704, 516, 56, '0.09 0.09 0.11');
        self::text($ops, 'F2', 21, 64, 735, $tenantName, '1 1 1');
        self::text($ops, 'F1', 10, 64, 718, __('Order receipt'), '0.86 0.86 0.86');
        self::textRight($ops, 'F2', 12, 548, 735, $order->public_code, '1 1 1');
        self::textRight($ops, 'F1', 9, 548, 718, $placedAt, '0.86 0.86 0.86');

        self::metaCard($ops, 48, 632, 154, __('Customer'), $order->customer_name, $order->customer_phone ?: $order->customer_email);
        self::metaCard($ops, 218, 632, 154, __('Order type'), $order->typeLabel(), $order->restaurantTable ? __('Table').' '.$order->restaurantTable->code : __(ucfirst((string) $order->status)));
        self::metaCard($ops, 388, 632, 176, __('Payment'), __(ucfirst((string) $order->payment_status)), __('Total').' '.Money::mad($order->total_cents));

        $detailY = 600;

        if ($order->type === 'delivery' && $order->delivery_address) {
            self::label($ops, 48, $detailY, __('Delivery address'));
            foreach (self::wrap($order->delivery_address, 92) as $line) {
                $detailY -= 13;
                self::text($ops, 'F1', 9, 48, $detailY, $line, '0.25 0.25 0.28');
            }
        }

        if ($order->kitchen_notes) {
            $detailY -= 18;
            self::label($ops, 48, $detailY, __('Kitchen notes'));
            foreach (self::wrap($order->kitchen_notes, 92) as $line) {
                $detailY -= 13;
                self::text($ops, 'F1', 9, 48, $detailY, $line, '0.25 0.25 0.28');
            }

            $detailY -= 12;
        }

        $tableHeaderBottom = min(536, $detailY - 28);
        self::fillRect($ops, 48, $tableHeaderBottom, 516, 28, '0.95 0.95 0.96');
        self::line($ops, 48, $tableHeaderBottom, 564, $tableHeaderBottom, '0.82 0.82 0.84');
        self::line($ops, 48, $tableHeaderBottom + 28, 564, $tableHeaderBottom + 28, '0.82 0.82 0.84');
        self::text($ops, 'F2', 9, 64, $tableHeaderBottom + 10, __('Item'), '0.28 0.28 0.31');
        self::textRight($ops, 'F2', 9, 390, $tableHeaderBottom + 10, __('Qty'), '0.28 0.28 0.31');
        self::textRight($ops, 'F2', 9, 464, $tableHeaderBottom + 10, __('Price'), '0.28 0.28 0.31');
        self::textRight($ops, 'F2', 9, 548, $tableHeaderBottom + 10, __('Total'), '0.28 0.28 0.31');

        $rowTop = $tableHeaderBottom;
        $omitted = 0;

        foreach ($order->items as $index => $item) {
            $nameLines = self::wrap($item->name, 38);
            $noteLines = $item->notes ? self::wrap(__('Note: :note', ['note' => $item->notes]), 54) : [];
            $rowHeight = max(42, 24 + (count($nameLines) * 12) + (count($noteLines) * 10));
            $rowBottom = $rowTop - $rowHeight;

            if ($rowBottom < 154) {
                $omitted++;

                continue;
            }

            if ($index % 2 === 1) {
                self::fillRect($ops, 48, $rowBottom, 516, $rowHeight, '0.99 0.99 0.99');
            }

            $textY = $rowTop - 23;

            foreach ($nameLines as $lineIndex => $line) {
                self::text($ops, $lineIndex === 0 ? 'F2' : 'F1', 10, 64, $textY - ($lineIndex * 12), $line, '0.10 0.10 0.12');
            }

            $noteY = $textY - (count($nameLines) * 12) - 2;
            foreach ($noteLines as $line) {
                self::text($ops, 'F1', 8, 76, $noteY, $line, '0.45 0.45 0.48');
                $noteY -= 10;
            }

            self::textRight($ops, 'F1', 10, 390, $textY, (string) $item->quantity, '0.20 0.20 0.22');
            self::textRight($ops, 'F1', 10, 464, $textY, Money::mad($item->unit_price_cents), '0.20 0.20 0.22');
            self::textRight($ops, 'F2', 10, 548, $textY, Money::mad($item->total_price_cents), '0.10 0.10 0.12');

            self::line($ops, 48, $rowBottom, 564, $rowBottom, '0.90 0.90 0.91');
            $rowTop = $rowBottom;
        }

        if ($omitted > 0 && $rowTop > 164) {
            self::text($ops, 'F1', 9, 64, $rowTop - 16, trans_choice(':count additional item|:count additional items', $omitted, ['count' => '+ '.$omitted]), '0.45 0.45 0.48');
            $rowTop -= 32;
        }

        $showDeliveryFee = $order->delivery_fee_cents > 0 || $order->type === 'delivery';
        $totalsHeight = $showDeliveryFee ? 88 : 66;
        $totalsY = max(76, $rowTop - ($totalsHeight + 18));
        self::fillRect($ops, 336, $totalsY, 228, $totalsHeight, '0.97 0.97 0.98');
        self::strokeRect($ops, 336, $totalsY, 228, $totalsHeight, '0.84 0.84 0.86');

        if ($showDeliveryFee) {
            self::text($ops, 'F1', 10, 352, $totalsY + 62, __('Subtotal'), '0.35 0.35 0.38');
            self::textRight($ops, 'F1', 10, 548, $totalsY + 62, Money::mad($order->subtotal_cents), '0.20 0.20 0.22');
            self::text($ops, 'F1', 10, 352, $totalsY + 40, __('Delivery fee'), '0.35 0.35 0.38');
            self::textRight($ops, 'F1', 10, 548, $totalsY + 40, Money::mad($order->delivery_fee_cents), '0.20 0.20 0.22');
            self::line($ops, 352, $totalsY + 28, 548, $totalsY + 28, '0.78 0.78 0.80');
        } else {
            self::text($ops, 'F1', 10, 352, $totalsY + 40, __('Subtotal'), '0.35 0.35 0.38');
            self::textRight($ops, 'F1', 10, 548, $totalsY + 40, Money::mad($order->subtotal_cents), '0.20 0.20 0.22');
            self::line($ops, 352, $totalsY + 28, 548, $totalsY + 28, '0.78 0.78 0.80');
        }

        self::text($ops, 'F2', 12, 352, $totalsY + 12, __('Total'), '0.09 0.09 0.11');
        self::textRight($ops, 'F2', 12, 548, $totalsY + 12, Money::mad($order->total_cents), '0.09 0.09 0.11');

        self::line($ops, 48, 50, 564, 50, '0.82 0.82 0.84');
        self::text($ops, 'F1', 8, 48, 34, $tenantName.' - '.$order->public_code, '0.45 0.45 0.48');
        self::textRight($ops, 'F1', 8, 564, 34, __('Generated :date', ['date' => now()->format('Y-m-d H:i')]), '0.45 0.45 0.48');

        return implode("\n", $ops)."\n";
    }

    private static function metaCard(array &$ops, int $x, int $y, int $width, string $label, string $primary, ?string $secondary = null): void
    {
        self::fillRect($ops, $x, $y, $width, 52, '0.98 0.98 0.98');
        self::strokeRect($ops, $x, $y, $width, 52, '0.85 0.85 0.87');
        self::label($ops, $x + 12, $y + 34, $label);
        self::text($ops, 'F2', 10, $x + 12, $y + 20, self::limit($primary, 28), '0.11 0.11 0.13');

        if ($secondary) {
            self::text($ops, 'F1', 8, $x + 12, $y + 8, self::limit($secondary, 34), '0.42 0.42 0.45');
        }
    }

    private static function label(array &$ops, int $x, int $y, string $text): void
    {
        self::text($ops, 'F2', 7, $x, $y, strtoupper($text), '0.45 0.45 0.48');
    }

    private static function fillRect(array &$ops, int $x, int $y, int $width, int $height, string $rgb): void
    {
        $ops[] = $rgb.' rg';
        $ops[] = "{$x} {$y} {$width} {$height} re f";
    }

    private static function strokeRect(array &$ops, int $x, int $y, int $width, int $height, string $rgb): void
    {
        $ops[] = $rgb.' RG';
        $ops[] = '1 w';
        $ops[] = "{$x} {$y} {$width} {$height} re S";
    }

    private static function line(array &$ops, int $x1, int $y1, int $x2, int $y2, string $rgb): void
    {
        $ops[] = $rgb.' RG';
        $ops[] = '1 w';
        $ops[] = "{$x1} {$y1} m {$x2} {$y2} l S";
    }

    private static function text(array &$ops, string $font, int $size, int $x, int $y, string $text, string $rgb): void
    {
        $ops[] = $rgb.' rg';
        $ops[] = sprintf('BT /%s %d Tf 1 0 0 1 %d %d Tm %s Tj ET', $font, $size, $x, $y, self::pdfText($text));
    }

    private static function textRight(array &$ops, string $font, int $size, int $rightX, int $y, string $text, string $rgb): void
    {
        $x = (int) ($rightX - self::estimatedWidth($text, $size, $font));

        self::text($ops, $font, $size, $x, $y, $text, $rgb);
    }

    private static function pdf(string $stream): string
    {
        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R /F2 5 0 R >> >> /Contents 6 0 R >>',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>',
            '<< /Length '.strlen($stream)." >>\nstream\n".$stream.'endstream',
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objects as $index => $object) {
            $objectNumber = $index + 1;
            $offsets[$objectNumber] = strlen($pdf);
            $pdf .= $objectNumber." 0 obj\n".$object."\nendobj\n";
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n";
        $pdf .= "0000000000 65535 f \n";

        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        $pdf .= "trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\n";
        $pdf .= "startxref\n".$xref."\n%%EOF";

        return $pdf;
    }

    private static function pdfText(string $text): string
    {
        $text = preg_replace('/[[:cntrl:]]+/', ' ', $text) ?? $text;
        $converted = @iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $text);
        $text = $converted !== false ? $converted : preg_replace('/[^\x20-\x7E]/', '?', $text);

        return '('.strtr((string) $text, [
            '\\' => '\\\\',
            '(' => '\\(',
            ')' => '\\)',
        ]).')';
    }

    /**
     * @return array<int, string>
     */
    private static function wrap(string $text, int $width): array
    {
        return explode("\n", wordwrap($text, $width, "\n", true));
    }

    private static function estimatedWidth(string $text, int $size, string $font): int
    {
        $factor = $font === 'F2' ? 0.62 : 0.56;

        return (int) min(260, strlen($text) * $size * $factor);
    }

    private static function limit(string $text, int $width): string
    {
        return strlen($text) > $width ? substr($text, 0, max(0, $width - 3)).'...' : $text;
    }
}
