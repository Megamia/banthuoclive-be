<?php

namespace Betod\Livotec\Controllers\Schedules;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Betod\Livotec\Models\Schedules;
use Betod\Livotec\Models\Doctor;
use League\Csv\Reader;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Log;

class ImportCsvSchedules extends Controller
{
    public function importCsvSchedules()
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
            $importedCount = 0;

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

                if (empty($row['doctor'])) continue;

                $doctor = Doctor::whereRaw('LOWER(name) = ?', [strtolower($row['doctor'])])->first();
                if (!$doctor) continue;

                $dayOfWeek = isset($row['day_of_week']) ? (int)$row['day_of_week'] : null;
                if (!$dayOfWeek || $dayOfWeek < 1 || $dayOfWeek > 7) continue;

                $schedule = Schedules::where('doctor_id', $doctor->id)
                    ->where('day_of_week', $dayOfWeek)
                    ->where('start_time', $row['start_time'])
                    ->where('end_time', $row['end_time'])
                    ->first();

                if ($schedule) {
                    continue; 
                }

                Schedules::create([
                    'doctor_id' => $doctor->id,
                    'day_of_week' => $dayOfWeek,
                    'start_time' => $row['start_time'] ?? null,
                    'end_time' => $row['end_time'] ?? null,
                ]);
                $importedCount++;
            }

            return response()->json([
                'success' => true,
                'message' => "Đã import thành công {$importedCount} lịch bác sĩ!"
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi import file Schedules: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
