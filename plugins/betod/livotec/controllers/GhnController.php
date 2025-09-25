<?php
namespace Betod\Livotec\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;

class GhnController extends Controller
{
    protected $token;

    public function __construct()
    {
        $this->token = env('GHN_API_KEY');
    }

    public function getProvinces()
    {
        $apiKey = env('GHN_API_KEY');
        $baseUrl = env('GHN_BASE_URL');

        if (!$apiKey || !$baseUrl) {
            \Log::error("GHN_API_KEY or GHN_BASE_URL is missing");
            return response()->json(['data' => [], 'message' => 'GHN config missing'], 500);
        }

        try {
            $response = Http::withHeaders([
                'Token' => $apiKey,
                'Content-Type' => 'application/json',
            ])->get($baseUrl . '/master-data/province');

            $data = $response->json();

            if (empty($data['data'])) {
                \Log::warning("GHN API response missing 'data': " . json_encode($data));
                return response()->json(['data' => []]);
            }

            return response()->json(['status' => 1, 'data' => $data['data']]);
        } catch (\Exception $e) {
            \Log::error("Failed to fetch GHN provinces: " . $e->getMessage());
            return response()->json(['data' => [], 'message' => 'Failed to fetch GHN provinces'], 500);
        }
    }

    public function getDistricts($provinceId)
    {
        $apiKey = env('GHN_API_KEY');
        $baseUrl = env('GHN_BASE_URL');

        if (!$provinceId) {
            \Log::warning("getDistricts called with empty provinceId");
            return response()->json(['data' => []]);
        }

        if (!$apiKey || !$baseUrl) {
            \Log::error("GHN_API_KEY or GHN_BASE_URL is missing");
            return response()->json(['data' => [], 'message' => 'GHN config missing'], 500);
        }

        try {
            $response = Http::withHeaders([
                'Token' => $apiKey,
                'Content-Type' => 'application/json',
            ])->get($baseUrl . '/master-data/district', [
                        'province_id' => $provinceId,
                    ]);

            $data = $response->json();

            if (empty($data['data'])) {
                \Log::warning("GHN API response missing 'data' or empty for provinceId {$provinceId}: " . json_encode($data));
                return response()->json(['data' => []]);
            }

            return response()->json(['status' => 1, 'data' => $data['data']]);
        } catch (\Exception $e) {
            \Log::error("Failed to fetch districts for provinceId {$provinceId}: " . $e->getMessage());
            return response()->json(['data' => [], 'message' => 'Failed to fetch districts'], 500);
        }
    }

    public function getWards($districtId)
    {
        $apiKey = env('GHN_API_KEY');
        $baseUrl = env('GHN_BASE_URL');

        if (is_array($districtId)) {
            if (isset($districtId['DistrictID'])) {
                $districtId = $districtId['DistrictID'];
            } else {
                $districtId = reset($districtId);
            }
        }

        if (!$districtId) {
            \Log::warning("getWards called with empty districtId");
            return response()->json(['data' => []]);
        }

        if (!$apiKey || !$baseUrl) {
            \Log::error("GHN_API_KEY or GHN_BASE_URL is missing");
            return response()->json(['data' => [], 'message' => 'GHN config missing'], 500);
        }

        try {
            $response = Http::withHeaders([
                'Token' => $apiKey,
                'Content-Type' => 'application/json',
            ])->get($baseUrl . '/master-data/ward', [
                        'district_id' => $districtId,
                    ]);

            $data = $response->json();

            if (empty($data['data'])) {
                \Log::warning("GHN API response missing 'data' or empty for districtId {$districtId}: " . json_encode($data));
                return response()->json(['data' => []]);
            }

            return response()->json(['status' => 1, 'data' => $data['data']]);
        } catch (\Exception $e) {
            \Log::error("Failed to fetch wards for districtId {$districtId}: " . $e->getMessage());
            return response()->json(['data' => [], 'message' => 'Failed to fetch wards'], 500);
        }
    }

    private function normalizeName($name)
    {
        $name = trim(mb_strtolower($name));
        $name = preg_replace('/(tỉnh|thành phố|quận|huyện|thị xã|xã|phường|thị trấn)\s*/iu', '', $name);
        $name = preg_replace('/\s+/', ' ', $name);
        return $name;
    }

    public function findProvinceById($provinceId)
    {
        if (empty($provinceId)) {
            \Log::warning("findProvinceById called with empty provinceId");
            return null;
        }

        $data = $this->getProvinces();

        if (empty($data['data'])) {
            \Log::warning("No provinces found");
            return null;
        }

        foreach ($data['data'] as $province) {
            if ($province['ProvinceID'] == $provinceId) {
                return $province ?? [];
            }
        }

        \Log::warning("Province not found by ID: $provinceId");
        return null;
    }


    public function findDistrictById($provinceId, $districtId)
    {
        if (empty($provinceId)) {
            \Log::warning("findDistrictById called with empty provinceId");
            return null;
        }
        if (empty($districtId)) {
            \Log::warning("findDistrictById called with empty districtId");
            return null;
        }

        $data = $this->getDistricts($provinceId);

        if (empty($data['data'])) {
            \Log::warning("No districts found for provinceId: $provinceId");
            return null;
        }

        foreach ($data['data'] as $district) {
            if ($district['DistrictID'] == $districtId) {
                return $district ?? [];
            }
        }

        \Log::warning("District not found by ID: $districtId");
        return null;
    }

    public function findWardCodeById($districtId, $subdistrictId)
    {
        if (empty($districtId)) {
            \Log::warning("findWardCodeById called with empty districtId");
            return null;
        }
        if (empty($subdistrictId)) {
            \Log::warning("findWardCodeById called with empty wardId");
            return null;
        }

        $data = $this->getWards($districtId);

        if (empty($data['data']) || !is_array($data['data'])) {
            \Log::warning("No wards data or data format invalid for districtId: $districtId");
            return null;
        }

        foreach ($data['data'] as $ward) {
            if (array_key_exists('WardCode', $ward) && $ward['WardCode'] == $subdistrictId) {
                return $ward['WardCode'] ?? null;
            }
        }

        \Log::warning("Ward not found by ID: $subdistrictId");
        return null;
    }

}
