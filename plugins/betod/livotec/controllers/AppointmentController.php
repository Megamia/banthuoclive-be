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

    public function getAllDoctor(Request $request)
    {
        $allDataDoctor = Doctor::with('image')->get();

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


    public function getAllDataDoctorById(Request $request, $doctorId)
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

        $exists = Appointment::where('user_id', $validated['user_id'])
            ->where('doctor_id', $validated['doctor_id'])
            ->where('meeting_time', $validated['meeting_time'])
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => 0,
                'message' => 'Bạn đã đặt lịch hẹn với bác sĩ này vào thời gian đó rồi!'
            ], );
        }

        $queueNumber = Appointment::where('doctor_id', $validated["doctor_id"])
            ->where('meeting_time', $validated["meeting_time"])
            ->count() + 1;

        $appointment = Appointment::create([
            'user_id' => $validated["user_id"],
            'doctor_id' => $validated["doctor_id"],
            'meeting_time' => $validated["meeting_time"],
            'queue_number' => $queueNumber
        ]);

        return response()->json([
            'status' => 1,
            'message' => 'Tạo lịch hẹn thành công!',
            'data' => $appointment
        ]);
    }
    public function specialties(Request $request)
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
        $schedules = Schedules::where('doctor_id', $doctorId)
            ->orderBy('day_of_week', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();
        if ($schedules->isEmpty()) {
            return response()->json([
                'status' => 0,
                'message' => 'Lấy lịch làm việc thất bại',
            ]);
        }
        return response()->json([
            'status' => 1,
            'message' => 'Lấy lịch làm việc thành công',
            'data' => $schedules,
        ]);
    }
}
