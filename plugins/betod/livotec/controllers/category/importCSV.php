<?php

namespace Betod\Livotec\Controllers\Category;

use Backend\Classes\Controller;
use Illuminate\Http\Request;
use Betod\Livotec\Models\Category;
use League\Csv\Reader;
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
                $csv->setHeaderOffset(0); // Thiết lập header
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

                // Kiểm tra danh mục có tồn tại hay không
                $category = null;

                if (!empty($row['id'])) {
                    $category = Category::find($row['id']);
                }

                if (!$category && !empty($row['name'])) {
                    $category = Category::whereRaw('LOWER(name) = ?', [$row['name']])->first();
                }

                if (!$category) {
                    $existingCategory = Category::whereRaw('LOWER(name) = ?', [$row['name']])->first();
                    if ($existingCategory) {
                        continue;
                    }
                }

                // Nếu tìm thấy danh mục
                if ($category) {
                    $originalData = $category->toArray();
                    $newData = [
                        'name' => $row['name'] ?? $category->name,
                        'slug' => !empty($row['slug']) ? $row['slug'] : $this->generateUniqueSlug($row['name'] ?? 'danh-muc', $category->id),
                        'parent_id' => isset($row['parent_id']) ? (int) $row['parent_id'] : $category->parent_id,
                        'description' => $row['description'] ?? $category->description,
                        'property' => $row['property'] ?? $category->property,
                        'nest_left' => isset($row['nest_left']) ? (int) $row['nest_left'] : $category->nest_left,
                        'nest_right' => isset($row['nest_right']) ? (int) $row['nest_right'] : $category->nest_right,
                        'nest_depth' => isset($row['nest_depth']) ? (int) $row['nest_depth'] : $category->nest_depth,
                    ];

                    if ($originalData != $newData) {
                        $category->update($newData);
                        $updatedOrCreatedCount++;
                    }
                } else {
                    // Nếu không tìm thấy danh mục, tạo mới
                    Category::create([
                        'name' => $row['name'] ?? 'Danh mục không tên',
                        'slug' => !empty($row['slug']) ? $row['slug'] : $this->generateUniqueSlug($row['name'] ?? 'danh-muc'),
                        'parent_id' => isset($row['parent_id']) ? (int) $row['parent_id'] : null,
                        'description' => $row['description'] ?? null,
                        'property' => $row['property'] ?? null,
                        'nest_left' => isset($row['nest_left']) ? (int) $row['nest_left'] : 0,
                        'nest_right' => isset($row['nest_right']) ? (int) $row['nest_right'] : 0,
                        'nest_depth' => isset($row['nest_depth']) ? (int) $row['nest_depth'] : 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $updatedOrCreatedCount++;
                }
            }

            if ($updatedOrCreatedCount > 0) {
                return response()->json([
                    'success' => true,
                    'message' => "Đã cập nhật hoặc thêm mới {$updatedOrCreatedCount} danh mục!"
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => "Không có danh mục nào được cập nhật hoặc thêm mới."
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

        while (Category::where('slug', $slug)->where('id', '!=', $id)->exists()) {
            $count++;
            $slug = Str::slug($name) . '-' . $count;
        }

        return $slug;
    }
}
