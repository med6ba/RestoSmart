<?php

namespace App\Support;

use App\Models\Order;

class ReceiptPdf
{
    public static function make(Order $order): string
    {
        $order->loadMissing(['items', 'restaurantTable', 'driver']);

        $tenantName = function_exists('tenant') ? (string) tenant('name') : config('app.name', 'RestoSmart');
        $lines = self::receiptLines($order, $tenantName);
        $stream = self::contentStream($lines, $tenantName, $order->public_code);

        return self::pdf($stream);
    }

    /**
     * @return array<int, array{font: string, size: int, text: string, gap?: int}>
     */
    private static function receiptLines(Order $order, string $tenantName): array
    {
        $placedAt = ($order->placed_at ?? $order->created_at)?->format('Y-m-d H:i') ?? '';

        $lines = [
            ['font' => 'F2', 'size' => 20, 'text' => 'ORDER RECEIPT', 'gap' => 22],
            ['font' => 'F2', 'size' => 13, 'text' => $tenantName, 'gap' => 17],
            ['font' => 'F1', 'size' => 10, 'text' => 'Receipt: '.$order->public_code, 'gap' => 13],
            ['font' => 'F1', 'size' => 10, 'text' => 'Date: '.$placedAt, 'gap' => 13],
            ['font' => 'F1', 'size' => 10, 'text' => 'Customer: '.$order->customer_name.' - '.$order->customer_phone, 'gap' => 13],
            ['font' => 'F1', 'size' => 10, 'text' => 'Mode: '.$order->typeLabel(), 'gap' => 13],
            ['font' => 'F1', 'size' => 1, 'text' => '---', 'gap' => 14],
        ];

        if ($order->type === 'local' && $order->restaurantTable) {
            $lines[] = ['font' => 'F1', 'size' => 10, 'text' => 'Table: '.$order->restaurantTable->code, 'gap' => 13];
        }

        if ($order->type === 'delivery' && $order->delivery_address) {
            foreach (self::wrap('Address: '.$order->delivery_address, 70) as $wrapped) {
                $lines[] = ['font' => 'F1', 'size' => 10, 'text' => $wrapped, 'gap' => 13];
            }
        }

        if ($order->kitchen_notes) {
            foreach (self::wrap('Kitchen notes: '.$order->kitchen_notes, 70) as $wrapped) {
                $lines[] = ['font' => 'F1', 'size' => 10, 'text' => $wrapped, 'gap' => 13];
            }
        }

        $lines[] = ['font' => 'F2', 'size' => 11, 'text' => 'Items', 'gap' => 15];

        foreach ($order->items as $item) {
            $itemLine = $item->quantity.' x '.$item->name.' - '.Money::mad($item->total_price_cents);

            foreach (self::wrap($itemLine, 70) as $index => $wrapped) {
                $lines[] = ['font' => $index === 0 ? 'F1' : 'F1', 'size' => 10, 'text' => $wrapped, 'gap' => 13];
            }

            if ($item->notes) {
                foreach (self::wrap('  Note: '.$item->notes, 68) as $wrapped) {
                    $lines[] = ['font' => 'F1', 'size' => 9, 'text' => $wrapped, 'gap' => 11];
                }
            }
        }

        $lines[] = ['font' => 'F1', 'size' => 1, 'text' => '---', 'gap' => 14];
        $lines[] = ['font' => 'F1', 'size' => 10, 'text' => 'Subtotal: '.Money::mad($order->subtotal_cents), 'gap' => 13];
        $lines[] = ['font' => 'F1', 'size' => 10, 'text' => 'Delivery fee: '.Money::mad($order->delivery_fee_cents), 'gap' => 13];
        $lines[] = ['font' => 'F2', 'size' => 13, 'text' => 'Total: '.Money::mad($order->total_cents), 'gap' => 18];
        $lines[] = ['font' => 'F1', 'size' => 1, 'text' => '---', 'gap' => 14];
        $lines[] = ['font' => 'F1', 'size' => 9, 'text' => 'Kitchen copy - print and attach to the ticket.', 'gap' => 12];

        return $lines;
    }

    /**
     * @param  array<int, array{font: string, size: int, text: string, gap?: int}>  $lines
     */
    private static function contentStream(array $lines, string $tenantName, string $publicCode): string
    {
        $operations = [
            '0 0 0 rg',
            '0 0 0 RG',
            '1 w',
            '54 64 504 664 re S',
            '0.86 0.86 0.86 RG',
            '54 692 m 558 692 l S',
        ];

        $y = 728;

        foreach ($lines as $line) {
            if ($y < 92) {
                break;
            }

            if ($line['text'] === '---') {
                $operations[] = '0.86 0.86 0.86 RG';
                $operations[] = sprintf('64 %d m 548 %d l S', $y + 4, $y + 4);
                $operations[] = '0 0 0 RG';
                $y -= $line['gap'] ?? 13;
                continue;
            }

            $operations[] = sprintf(
                'BT /%s %d Tf 1 0 0 1 64 %d Tm %s Tj ET',
                $line['font'],
                $line['size'],
                $y,
                self::text($line['text'])
            );

            $y -= $line['gap'] ?? 13;
        }

        $operations[] = sprintf('BT /F1 8 Tf 1 0 0 1 64 46 Tm %s Tj ET', self::text($tenantName.' - '.$publicCode));

        return implode("\n", $operations)."\n";
    }

    private static function pdf(string $stream): string
    {
        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R /F2 5 0 R >> >> /Contents 6 0 R >>',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>',
            "<< /Length ".strlen($stream)." >>\nstream\n".$stream."endstream",
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

    private static function text(string $text): string
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
}
