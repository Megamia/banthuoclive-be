<?php

namespace Betod\Livotec\Controllers\Product;

use Backend\Classes\Controller;
use Illuminate\Http\Request;
use Betod\Livotec\Models\Product;
use League\Csv\Reader;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Str;

class ImportCSV extends Controller
{
    public function importCsv()
    {
        try {
            if (!request()->hasFile('csv_file')) {
                throw new \Exception('Không tìm thấy file.');
            }

            $file = request()->file('csv_file');

            if (!$file) {
                throw new \Exception('Không thể đọc file.');
            }

            $extension = $file->getClientOriginalExtension();
            $updatedOrCreatedCount = 0;

            if ($extension === 'csv') {
                $csv = Reader::createFromPath($file->getPathname(), 'r');
                $csv->setHeaderOffset(0);
                $data = iterator_to_array($csv->getRecords());
            } elseif (in_array($extension, ['xls', 'xlsx', 'xlsm'])) {
                $spreadsheet = IOFactory::load($file->getPathname());
                $worksheet = $spreadsheet->getActiveSheet();
                $rows = $worksheet->toArray(null, true, true, true);

                $headers = array_map('trim', $rows[1] ?? []);
                unset($rows[1]);

                $data = [];
                foreach ($rows as $row) {
                    $rowData = [];
                    foreach ($headers as $index => $header) {
                        $rowData[$header] = trim($row[$index] ?? '');
                    }
                    $data[] = $rowData;
                }
            } else {
                throw new \Exception('Chỉ hỗ trợ file CSV, XLS, XLSX, XLSM.');
            }

            foreach ($data as $row) {
                $row = array_map('trim', $row);
                $row['name'] = strtolower($row['name'] ?? '');
                $row['slug'] = strtolower($row['slug'] ?? '');

                $product = null;

                if (!empty($row['id'])) {
                    $product = Product::find($row['id']);
                }

                if (!$product && !empty($row['name'])) {
                    $product = Product::whereRaw('LOWER(name) = ?', [$row['name']])->first();
                }

                if (!$product) {
                    $existingProduct = Product::whereRaw('LOWER(name) = ?', [$row['name']])->first();
                    if ($existingProduct) {
                        continue;
                    }
                }

                $thongso = isset($row['thongso']) ? json_decode($row['thongso'], true) : null;

                if ($product) {
                    $originalData = $product->toArray();
                    $newData = [
                        'name' => $row['name'] ?? $product->name,
                        'slug' => !empty($row['slug']) ? $row['slug'] : $this->generateUniqueSlug($row['name'] ?? 'san-pham', $product->id),
                        'description' => $row['description'] ?? $product->description,
                        'price' => isset($row['price']) ? (float) $row['price'] : $product->price,
                        'category_id' => isset($row['category_id']) ? (int) $row['category_id'] : $product->category_id,
                        'thongso' => $thongso ?? $product->thongso,
                        'available' => isset($row['available']) ? (int) $row['available'] : $product->available,
                        'post_id' => isset($row['post_id']) ? (int) $row['post_id'] : $product->post_id,
                        'sold_out' => isset($row['sold_out']) ? (int) $row['sold_out'] : $product->sold_out,
                        'stock' => isset($row['stock']) ? (int) $row['stock'] : $product->stock,
                    ];

                    if ($originalData != $newData) {
                        $product->update($newData);
                        $updatedOrCreatedCount++;
                    }
                } else {
                    Product::create([
                        'name' => $row['name'] ?? 'Sản phẩm không tên',
                        'slug' => !empty($row['slug']) ? $row['slug'] : $this->generateUniqueSlug($row['name'] ?? 'san-pham'),
                        'description' => $row['description'] ?? null,
                        'price' => isset($row['price']) ? (float) $row['price'] : 0,
                        'category_id' => isset($row['category_id']) ? (int) $row['category_id'] : null,
                        'thongso' => $thongso ?? null,
                        'available' => isset($row['available']) ? (int) $row['available'] : 0,
                        'post_id' => isset($row['post_id']) ? (int) $row['post_id'] : null,
                        'sold_out' => isset($row['sold_out']) ? (int) $row['sold_out'] : 0,
                        'stock' => isset($row['stock']) ? (int) $row['stock'] : 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $updatedOrCreatedCount++;
                }
            }

            if ($updatedOrCreatedCount > 0) {
                return response()->json([
                    'success' => true,
                    'message' => "Đã cập nhật hoặc thêm mới {$updatedOrCreatedCount} sản phẩm!"
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => "Không có sản phẩm nào được cập nhật hoặc thêm mới."
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Lỗi import file: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function generateUniqueSlug($name, $id = null)
    {
        $slug = Str::slug($name);
        $count = 0;

        while (Product::where('slug', $slug)->where('id', '!=', $id)->exists()) {
            $count++;
            $slug = Str::slug($name) . '-' . $count;
        }

        return $slug;
    }
}
