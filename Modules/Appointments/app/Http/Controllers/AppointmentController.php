<?php

namespace Modules\Appointments\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\NotifyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Modules\Appointments\DataTables\AppointmentsDataTable;
use Modules\Appointments\Http\Requests\SaveAppointmentRequest;
use Modules\Appointments\Models\Appointment;
use Modules\Appointments\Services\AppointmentService;

class AppointmentController extends Controller
{
    public function index(): View
    {
        $this->authorize('appointments.view');

        return view('appointments::index', ['appointments' => Appointment::query()->latest('starts_at')->paginate(20)]);
    }

    public function create(): View
    {
        $this->authorize('appointments.create');

        return view('appointments::create', ['appointment' => null]);
    }

    public function data(AppointmentsDataTable $dataTable): JsonResponse
    {
        $this->authorize('appointments.view');

        return $dataTable->handle();
    }

    public function store(SaveAppointmentRequest $request, AppointmentService $service, NotifyService $notifications): RedirectResponse
    {
        $this->authorize('appointments.create');
        $appointment = $service->save($request->validated());
        $notifications->sendToAdminsForActor($request->user('admin'), 'Randevu eklendi', 'Yeni bir randevu kaydı eklendi.', route('admin.appointments.edit', $appointment, false));

        return redirect()->route('admin.appointments.index')->with('notification', ['type' => 'success', 'message' => 'Randevu başarıyla eklendi.']);
    }

    public function edit(Appointment $appointment): View
    {
        $this->authorize('appointments.update');

        return view('appointments::edit', compact('appointment'));
    }

    public function update(SaveAppointmentRequest $request, Appointment $appointment, AppointmentService $service, NotifyService $notifications): RedirectResponse
    {
        $this->authorize('appointments.update');
        $appointment = $service->save($request->validated(), $appointment);
        $notifications->sendToAdminsForActor($request->user('admin'), 'Randevu güncellendi', 'Bir randevu kaydı güncellendi.', route('admin.appointments.edit', $appointment, false));

        return redirect()->route('admin.appointments.index')->with('notification', ['type' => 'success', 'message' => 'Randevu başarıyla güncellendi.']);
    }

    public function destroy(Appointment $appointment, AppointmentService $service, NotifyService $notifications): RedirectResponse
    {
        $this->authorize('appointments.delete');
        $service->delete($appointment);
        $notifications->sendToAdminsForActor(request()->user('admin'), 'Randevu silindi', 'Bir randevu kaydı silindi.', route('admin.appointments.index', [], false));

        return redirect()->route('admin.appointments.index')->with('notification', ['type' => 'success', 'message' => 'Randevu başarıyla silindi.']);
    }
}
