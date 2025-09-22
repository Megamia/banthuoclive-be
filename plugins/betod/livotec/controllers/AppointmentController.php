<?php
namespace Betod\Livotec\Controllers;

use Backend;
use BackendMenu;
use Backend\Classes\Controller;
use Betod\Livotec\Models\Appointment;
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


}
