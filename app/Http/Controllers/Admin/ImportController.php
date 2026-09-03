<?php

namespace App\Http\Controllers\Admin;

use App\Exports\BrandTemplateExport;
use App\Exports\CategoryTemplateExport;
use App\Exports\ProductTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\BrandsImport;
use App\Imports\CategoriesImport;
use App\Imports\ProductsImport;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ImportController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Imports/Index');
    }

    public function template(string $type): BinaryFileResponse
    {
        return match ($type) {
            'categories' => Excel::download(new CategoryTemplateExport, 'template-kategori.xlsx'),
            'brands' => Excel::download(new BrandTemplateExport, 'template-brand.xlsx'),
            'products' => Excel::download(new ProductTemplateExport, 'template-produk.xlsx'),
            default => abort(404),
        };
    }

    public function categories(Request $request): RedirectResponse
    {
        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120']]);

        $import = new CategoriesImport;
        Excel::import($import, $request->file('file'));

        ActivityLog::record('import.categories', "Impor kategori: {$import->created} baru, {$import->updated} diperbarui, ".count($import->rowErrors).' gagal.');

        return back()->with($this->resultFlash($import->created, $import->updated, $import->rowErrors));
    }

    public function brands(Request $request): RedirectResponse
    {
        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120']]);

        $import = new BrandsImport;
        Excel::import($import, $request->file('file'));

        ActivityLog::record('import.brands', "Impor brand: {$import->created} baru, {$import->updated} diperbarui, ".count($import->rowErrors).' gagal.');

        return back()->with($this->resultFlash($import->created, $import->updated, $import->rowErrors));
    }

    public function products(Request $request): RedirectResponse
    {
        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120']]);

        $import = new ProductsImport;
        Excel::import($import, $request->file('file'));

        ActivityLog::record('import.products', "Impor produk: {$import->created} baru, {$import->updated} diperbarui, ".count($import->rowErrors).' gagal.');

        return back()->with($this->resultFlash($import->created, $import->updated, $import->rowErrors));
    }

    /**
     * @param  array<int, string>  $rowErrors
     * @return array<string, mixed>
     */
    private function resultFlash(int $created, int $updated, array $rowErrors): array
    {
        $summary = "{$created} data baru ditambahkan, {$updated} data diperbarui.";

        if (empty($rowErrors)) {
            return ['success' => $summary];
        }

        return [
            'info' => $summary,
            'error' => "Sebagian baris gagal diimpor:\n".implode("\n", array_slice($rowErrors, 0, 20)).
                (count($rowErrors) > 20 ? "\n... dan ".(count($rowErrors) - 20).' baris lainnya.' : ''),
        ];
    }
}
