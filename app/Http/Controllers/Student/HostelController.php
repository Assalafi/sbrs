<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HostelController extends Controller
{
    private function getApiUrl()
    {
        return config('app.usp_hostel_api_url', env('USP_HOSTEL_API_URL', 'https://umstad.online/api/v1/hostel'));
    }

    private function getApiKey()
    {
        return config('app.usp_hostel_api_key', env('USP_HOSTEL_API_KEY'));
    }

    private function apiCall(string $method, string $endpoint, array $data = [])
    {
        $url = $this->getApiUrl() . '/' . $endpoint;
        $apiKey = $this->getApiKey();

        $data['api_key'] = $apiKey;

        try {
            $response = Http::withHeaders([
                'X-API-Key' => $apiKey,
                'Accept' => 'application/json',
            ])->withoutVerifying()->timeout(30)->$method($url, $data);

            return $response->json();
        } catch (\Exception $e) {
            Log::error('USP Hostel API error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Hostel service is temporarily unavailable. Please try again later.',
            ];
        }
    }

    /**
     * All SBRS students (IJMB and Remedial) can apply for hostel via this portal.
     */
    private function authStudentOrFail()
    {
        $student = Auth::guard('student')->user();

        if (!$student) {
            abort(401, 'Unauthorized');
        }

        return $student;
    }

    public function index()
    {
        $student = $this->authStudentOrFail();
        $student->load(['programme', 'academicSession']);

        $gender = strtoupper($student->gender === 'Female' ? 'FEMALE' : 'MALE');

        $overview = $this->apiCall('get', 'overview', ['gender' => $gender]);
        $reservation = $this->apiCall('get', 'status', ['registration_number' => $student->registration_number]);

        return view('student.hostel.index', [
            'student' => $student,
            'gender' => $gender,
            'overview' => $overview['data'] ?? [],
            'overviewError' => isset($overview['success']) && !$overview['success'] ? ($overview['message'] ?? 'Unable to reach hostel service.') : null,
            'reservation' => $reservation['data'] ?? null,
            'reservationError' => isset($reservation['success']) && !$reservation['success'] ? ($reservation['message'] ?? '') : null,
        ]);
    }

    public function overview(Request $request)
    {
        $student = $this->authStudentOrFail();

        $gender = strtoupper($student->gender === 'Female' ? 'FEMALE' : 'MALE');
        return response()->json($this->apiCall('get', 'overview', ['gender' => $gender]));
    }

    public function blocks(Request $request)
    {
        $student = $this->authStudentOrFail();

        $gender = strtoupper($student->gender === 'Female' ? 'FEMALE' : 'MALE');

        return response()->json($this->apiCall('get', 'blocks', [
            'hall' => $request->input('hall'),
            'gender' => $gender,
        ]));
    }

    public function rooms(Request $request)
    {
        $student = $this->authStudentOrFail();

        $gender = strtoupper($student->gender === 'Female' ? 'FEMALE' : 'MALE');

        return response()->json($this->apiCall('get', 'rooms', [
            'hall' => $request->input('hall'),
            'block' => $request->input('block'),
            'gender' => $gender,
        ]));
    }

    public function beds(Request $request)
    {
        $student = $this->authStudentOrFail();

        $gender = strtoupper($student->gender === 'Female' ? 'FEMALE' : 'MALE');

        return response()->json($this->apiCall('get', 'beds', [
            'hall' => $request->input('hall'),
            'block' => $request->input('block'),
            'room' => $request->input('room'),
            'gender' => $gender,
        ]));
    }

    public function reserve(Request $request)
    {
        $student = $this->authStudentOrFail();

        $request->validate([
            'hall' => 'required|string',
            'block' => 'required|string',
            'room' => 'required|integer',
            'bed' => 'required|integer',
        ]);

        $gender = strtoupper($student->gender === 'Female' ? 'FEMALE' : 'MALE');

        return response()->json($this->apiCall('post', 'reserve', [
            'registration_number' => $student->registration_number,
            'hall' => $request->input('hall'),
            'block' => $request->input('block'),
            'room' => $request->input('room'),
            'bed' => $request->input('bed'),
            'gender' => $gender,
        ]));
    }

    public function status()
    {
        $student = $this->authStudentOrFail();

        return response()->json($this->apiCall('get', 'status', [
            'registration_number' => $student->registration_number,
        ]));
    }

    public function release()
    {
        $student = $this->authStudentOrFail();

        return response()->json($this->apiCall('post', 'release', [
            'registration_number' => $student->registration_number,
        ]));
    }
}
