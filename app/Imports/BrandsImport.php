<?php

namespace App\Imports;

use App\Models\Brand;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BrandsImport implements ToCollection, WithHeadingRow
{
    public int $created = 0;

    public int $updated = 0;

    /** @var array<int, string> */
    public array $rowErrors = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;

            $validator = Validator::make($row->toArray(), [
                'nama' => ['required', 'string', 'max:255'],
            ]);

            if ($validator->fails()) {
                $this->rowErrors[] = "Baris {$rowNumber}: ".$validator->errors()->first();

                continue;
            }

            $slug = Str::slug($row['nama']);
            $brand = Brand::where('slug', $slug)->first();

            $attributes = [
                'name' => trim((string) $row['nama']),
                'slug' => $slug,
            ];

            if ($brand) {
                $brand->update($attributes);
                $this->updated++;
            } else {
                Brand::create($attributes);
                $this->created++;
            }
        }
    }
}
