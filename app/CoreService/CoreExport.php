<?php

namespace App\CoreService;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CoreExport implements FromCollection, WithHeadings, WithMapping
{
    protected $data;
    protected $fields;

    public function __construct($data, $fields)
    {
        $this->data = $data;
        $this->fields = $fields;
    }

    // Mengambil data dari collection yang dikirim dari controller
    public function collection()
    {
        return $this->data;
    }

    // Membuat header Excel secara dinamis
    public function headings(): array
    {
        return array_map(function($field) {
            return strtoupper(str_replace('_', ' ', $field));
        }, $this->fields);
    }

    // Memetakan data agar sesuai dengan kolom header
    public function map($row): array
    {
        $mappedData = [];
        foreach ($this->fields as $field) {
            $mappedData[] = $row->{$field} ?? '-';
        }
        return $mappedData;
    }
}