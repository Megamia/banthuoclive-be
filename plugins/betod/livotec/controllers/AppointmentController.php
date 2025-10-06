<?php
namespace Betod\Livotec\Controllers;

use Backend;
use BackendMenu;
use Backend\Classes\Controller;
use Betod\Livotec\Models\Appointment;
use Betod\Livotec\Models\Doctor;
use Betod\Livotec\Models\Schedules;
use Betod\Livotec\Models\Specialties;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class AppointmentController extends Controller
{
    public $implement = [
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\ListController::class
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Betod.Livotec', 'doctor', 'appointment');
    }

    public function getDataAllDoctor(Request $request)
    {
        $allDataDoctor = Doctor::with('image', 'specialties')->get();

        if ($allDataDoctor->isNotEmpty()) {
            $allDataDoctor = $allDataDoctor->map(function ($doctor) {
                return attachCloudinaryUrl($doctor);
            });

            return response()->json([
                'status' => 1,
                'message' => "Lây thông tin của các bác sĩ thành công",
                'data' => $allDataDoctor
            ]);
        }

        return response()->json([
            'status' => 0,
            'message' => 'Không có dữ liệu của bác sĩ nào trong cơ sở dữ liệu'
        ]);
    }


    public function getDataAllDoctorById(Request $request, $doctorId)
    {
        $dataDoctor = Doctor::with(['image'])
            ->where('id', $doctorId)
            ->find($doctorId);

        if ($dataDoctor) {
            $dataDoctor = attachCloudinaryUrl($dataDoctor);

            return response()->json([
                'status' => 1,
                'message' => 'Lấy thông tin của bác sĩ thành công',
                'data' => $dataDoctor
            ]);
        }

        return response()->json([
            'status' => 0,
            'message' => 'Lấy thông tin của bác sĩ thất bại'
        ]);
    }


    public function createAppointment(Request $request)
    {
        $data = $request->input('data');

        $validated = validator($data, [
            'user_id' => 'required|integer',
            'doctor_id' => 'required|integer',
            'meeting_time' => 'required|date',
        ])->validate();

        try {
            $appointment = DB::transaction(function () use ($validated) {
                $timestamp = strtotime($validated['meeting_time']);
                $minutes = date('i', $timestamp);
                $roundedMinutes = floor($minutes / 30) * 30;

                $slot = date(
                    'Y-m-d H:' . str_pad($roundedMinutes, 2, '0', STR_PAD_LEFT) . ':00',
                    $timestamp
                );

                $exists = Appointment::where('user_id', $validated['user_id'])
                    ->where('doctor_id', $validated['doctor_id'])
                    ->where('meeting_time', $slot)
                    ->lockForUpdate()
                    ->exists();

                if ($exists) {
                    throw new \Exception("APPOINTMENT_ALREADY_EXISTS");
                }

                $doctor = Doctor::find($validated['doctor_id']);
                $capacityPerSlot = $doctor?->capacity_per_slot ?? 5;

                $countInSlot = Appointment::where('doctor_id', $validated['doctor_id'])
                    ->where('meeting_time', $slot)
                    ->lockForUpdate()
                    ->count();

                if ($countInSlot >= $capacityPerSlot) {
                    throw new \Exception("SLOT_FULL");
                }

                $queueNumber = $countInSlot + 1;

                return Appointment::create([
                    'user_id' => $validated['user_id'],
                    'doctor_id' => $validated['doctor_id'],
                    'meeting_time' => $slot,
                    'queue_number' => $queueNumber,
                ]);
            });

            $user = $appointment->user;
            $doctor = $appointment->doctor;
            $clinic = \Betod\Livotec\Models\Clinics::where('doctor_id', $doctor->id)->first();

            if ($user?->email) {

                Mail::send('betod.livotec::mail.appointment_confirm', [
                    'user_name' => trim(($user?->first_name ?? '') . ' ' . ($user?->last_name ?? '')),
                    'doctor_name' => $doctor?->name ?? 'Bác sĩ',
                    'meeting_time' => $appointment->meeting_time,
                    'queue_number' => $appointment->queue_number,
                    'clinic_name' => $clinic?->name ?? '',
                    'clinic_location' => $clinic?->location ?? '',
                ], function ($message) use ($user) {
                    $message->to($user->email)
                        ->subject('Xác nhận lịch hẹn khám');
                });
            }


            return response()->json([
                'status' => 1,
                'message' => 'Tạo lịch hẹn thành công!',
                'code' => 200,
                'data' => $appointment,
            ], 200);

        } catch (\Exception $e) {
            switch ($e->getMessage()) {
                case "APPOINTMENT_ALREADY_EXISTS":
                    return response()->json([
                        'status' => 0,
                        'error_code' => 'APPOINTMENT_ALREADY_EXISTS',
                        'message' => 'Bạn đã đặt lịch hẹn với bác sĩ này trong ca đó!',
                        'data' => null,
                    ], 409);

                case "SLOT_FULL":
                    return response()->json([
                        'status' => 0,
                        'error_code' => 'SLOT_FULL',
                        'message' => 'Ca này đã đầy, vui lòng chọn ca khác!',
                        'data' => null,
                    ], 422);

                default:
                    return response()->json([
                        'status' => 0,
                        'error_code' => 'UNKNOWN_ERROR',
                        'message' => $e->getMessage(),
                        'data' => null,
                    ], 400);
            }
        }
    }


    public function getDataAllSpecialties(Request $request)
    {
        $specialties = Specialties::all();

        if ($specialties->isEmpty()) {
            return response()->json([
                'status' => 0,
                'specialties' => 'No data',
            ]);
        }

        return response()->json([
            'status' => 1,
            'specialties' => $specialties,
        ]);
    }
    public function getDoctorsBySpecialty(Request $request, $specialtyId)
    {
        $doctors = Doctor::where('specialties_id', $specialtyId)->get();
        if ($doctors->isEmpty()) {
            return response()->json([
                'status' => 0,
                'doctors' => 'No data',
            ]);
        }
        return response()->json([
            'status' => 1,
            'doctors' => $doctors,
        ]);
    }
    public function getSchedulesByDoctorId(Request $request, $doctorId)
    {
        try {
            $schedules = Schedules::where('doctor_id', $doctorId)
                ->orderBy('day_of_week', 'asc')
                ->orderBy('start_time', 'asc')
                ->get();

            if ($schedules->isEmpty()) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Không tìm thấy lịch làm việc',
                ]);
            }

            return response()->json([
                'status' => 1,
                'message' => 'Lấy lịch làm việc thành công',
                'data' => $schedules,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Lỗi server: ' . $e->getMessage(),
            ], 500);
        }

    }

    public function getDataAppointmentByUserid(Request $request, $userId)
    {
        try {
            $dataAppointment = Appointment::with(['doctor', 'user', 'clinic'])
                ->where('user_id', $userId)
                ->get();

            if ($dataAppointment->isEmpty()) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Không tìm thấy lịch khám nào cho người dùng này',
                    'code' => 404
                ]);
            }

            $result = $dataAppointment->map(function ($item) {
                return [
                    'appointment_id' => $item->id,
                    'meeting_time' => $item->meeting_time,
                    'queue_number' => $item->queue_number,

                    'user_id' => $item->user->id ?? null,
                    'user_name' => $item->user
                        ? trim(($item->user->first_name ?? '') . ' ' . ($item->user->last_name ?? ''))
                        : null,

                    'user_email' => $item->user->email ?? null,

                    'doctor_id' => $item->doctor->id ?? null,
                    'doctor_name' => $item->doctor->name ?? null,
                    'doctor_phone' => $item->doctor->phone ?? null,

                    'clinic_id' => $item->clinic->id ?? null,
                    'clinic_name' => $item->clinic->name ?? null,
                    'clinic_location' => $item->clinic->location ?? null,
                ];
            });

            return response()->json([
                'status' => 1,
                'data' => $result,
                'message' => 'Lấy thông tin lịch khám thành công',
                'code' => 200
            ]);

        } catch (\Exception $e) {
            // \Log::error("getDataAppointmentByUserid error: " . $e->getMessage());
            return response()->json([
                'status' => 0,
                'message' => $e->getMessage(),
                'code' => 500
            ]);
        }
    }
    public function testapi(Request $request)
    {
        return response()->json(['data' => "ok"]);
    }
}
