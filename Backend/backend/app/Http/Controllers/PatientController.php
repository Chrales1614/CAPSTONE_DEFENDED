<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PatientController extends Controller
{
    public function index()
    {
        $patients = Patient::with(['appointments', 'medicalRecords'])->paginate(10);
        return response()->json($patients);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'species' => 'required|string|max:100',
            'breed' => 'required|string|max:100',
            'age' => 'required|numeric',
            'gender' => 'required|string|in:male,female',
            'owner_name' => 'required|string|max:255',
            'owner_contact' => 'required|string|max:20',
            'owner_address' => 'required|string',
            'medical_history' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $patient = Patient::create($request->all());
        return response()->json($patient, 201);
    }

    public function show(Patient $patient)
    {
        return response()->json($patient->load(['appointments', 'medicalRecords']));
    }

    public function update(Request $request, Patient $patient)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'species' => 'string|max:100',
            'breed' => 'string|max:100',
            'age' => 'numeric',
            'gender' => 'string|in:male,female',
            'owner_name' => 'string|max:255',
            'owner_contact' => 'string|max:20',
            'owner_address' => 'string',
            'medical_history' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $patient->update($request->all());
        return response()->json($patient);
    }

    public function destroy(Patient $patient)
    {
        $patient->delete();
        return response()->json(null, 204);
    }

    public function search(Request $request)
    {
        $query = $request->get('query');
        $patients = Patient::where('name', 'like', "%{$query}%")
            ->orWhere('owner_name', 'like', "%{$query}%")
            ->orWhere('owner_contact', 'like', "%{$query}%")
            ->paginate(10);
        return response()->json($patients);
    }
} 