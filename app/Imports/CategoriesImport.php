<?php

namespace App\Imports;

use App\Models\Category;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CategoriesImport implements ToCollection, WithHeadingRow
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

            $parentId = null;

            if (! empty($row['kategori_induk'])) {
                $parent = Category::where('name', trim((string) $row['kategori_induk']))->first();

                if (! $parent) {
                    $this->rowErrors[] = "Baris {$rowNumber}: kategori induk \"{$row['kategori_induk']}\" tidak ditemukan.";

                    continue;
                }

                $parentId = $parent->id;
            }

            $slug = Str::slug($row['nama']);
            $category = Category::where('slug', $slug)->first();

            $attributes = [
                'name' => trim((string) $row['nama']),
                'slug' => $slug,
                'parent_id' => $parentId,
                'is_active' => $this->parseBool($row['aktif'] ?? null),
            ];

            if ($category) {
                $category->update($attributes);
                $this->updated++;
            } else {
                Category::create($attributes);
                $this->created++;
            }
        }
    }

    private function parseBool(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        return in_array(mb_strtolower(trim((string) $value)), ['1', 'ya', 'yes', 'true', 'aktif'], true);
    }
}
