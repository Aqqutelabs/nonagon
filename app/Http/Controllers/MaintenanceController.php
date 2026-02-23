<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\MaintenanceWorkOrder;
use App\Models\MaintenanceWorkOrderAttachment;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    public function createWorkOrder(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'equipment_id' => 'required|uuid|exists:equipment,id',
            'title' => 'nullable|string',
            'type' => 'nullable|string|in:Preventive, Corrective, Breakdown, Inspection',
            'status' => 'nullable|string|in:Open, In Progress, Completed, Cancelled',
            'priority' => 'nullable|string|in:Low, Medium, High, Critical',
            'description' => 'nullable|string',
            'requested_by' => 'uuid|exists:users',
            'target_start_at' => 'nullable|date',
            'target_due_at' => 'nullable|date',
            'completed_at' => 'nullable|date',
            'maintenance_schedule_id' => 'uuid|exists:maintenance_schedule',
            'breakdown_incident_id' => 'uuid|exists:breakdown_incident',
        ]);

        $validated['organization_id'] = $user->organization_id;

        if (!isset($validated['requested_by'])) {
            $validated['requested_by'] = $user->id;
        }

        $workOrder = MaintenanceWorkOrder::create($validated);

        if ($request->has('file_url')) {

            $attachmentData = $request->validate([
                'file_url' => 'required|string',
                'caption' => 'nullable|string',
            ]);

            $attachmentData['maintenance_work_order_id'] = $workOrder->id;
            $attachmentData['uploaded_by'] = $user->id;

            $workOrder->attachments()->create($attachmentData);
        }

        return redirect()->back()
            ->with('success', 'Maintenance work order created successfully');
    }

    public function getWorkOrderByEquipment(Equipment $equipment)
    {
        $user = auth()->user();
        $org = $user->organization;
        $equipment = Equipment::where('organization_id', $org->id)->firstOrFail();

        return $equipment;
    }

    public function updateWorkOrder(Request $request)
    {
        $user = auth()->user();

        $task = $request->validate([
            'maintenance_work_id' => 'uuid|exists:maintenance_work_order',
            'description' => 'nullable|string',
            'sequence' => 'nullable|integer',
            'is_completed' => 'required|boolean',
            'completed_at' => 'nullable|date'
        ]);

        $task['completed_by'] = $user->id;
    }

    public function breakdownIncident(Request $request, Equipment $equipment)
    {
        $user = auth()->user();

        $bI = $request->validate([
            'occured_at' => 'nullable|date',
            'location_snapshot' => 'nullable|string',
            'summary' => 'nullable|string',
            'probable_cause' => 'nullable|string',
            'severity' => 'in:low, medium, high, critical'
        ]);

        $validated['organization_id'] = $user->organization_id;

        // Create via relationship (auto fills equipment_id)
        $breakdown = $equipment->breakdownIncidents()->create($validated);

        return back()->with('success', 'Breakdown incident recorded successfully');
    }

    public function maintenanceSchedule(Request $request, Equipment $equipment)
    {
        $user = auth()->user();

        $mS = $request->validate([
            'name' => 'nullable|string',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'trigger_type' => 'in:time_based, usage_based, hybrid',
            'time_frequency_value' => 'nullable|integer',
            'time_frequency_unit' => 'in:day, week, month, year',
            'usage_meter_id' => 'nullable|exists:equipment_meters',
            'usage_threshold' => 'nullabe|decimal',
            'last_triggered_at' => 'nullable|date',
            'next_due_date' => 'nullable|date',
        ]);

        $validated['organization_id'] = $user->organization_id;
        $breakdown = $equipment->maintenanceSchedule()->create($validated);

        return back()->with('success', 'Maintenance schedule created successfully');
    }
}
