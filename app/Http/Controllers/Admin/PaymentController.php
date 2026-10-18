<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\AcademicSession;
use App\Models\PaymentType;
use App\Models\Programme;
use App\Models\Student;
use App\Models\Applicant;
use App\Services\RemitaService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PaymentController extends Controller
{
    protected RemitaService $remitaService;

    public function __construct(RemitaService $remitaService)
    {
        $this->remitaService = $remitaService;
    }

    public function index(Request $request)
    {
        $query = Payment::with(['payable', 'academicSession', 'fee']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }
        if ($request->filled('academic_session_id')) {
            $query->where('academic_session_id', $request->academic_session_id);
        }
        if ($request->filled('programme_id')) {
            $programmeId = $request->input('programme_id');
            $query->whereHasMorph('payable', [Student::class, Applicant::class], function ($payer) use ($programmeId) {
                $payer->where('programme_id', $programmeId);
            });
        }
        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('rrr', 'like', "%{$search}%")
                  ->orWhere('reference', 'like', "%{$search}%")
                  ->orWhere('order_id', 'like', "%{$search}%")
                  ->orWhereHasMorph('payable', [Student::class], function ($payer) use ($search) {
                      $payer->where('registration_number', 'like', "%{$search}%")
                          ->orWhere('surname', 'like', "%{$search}%")
                          ->orWhere('first_name', 'like', "%{$search}%");
                  })
                  ->orWhereHasMorph('payable', [Applicant::class], function ($payer) use ($search) {
                      $payer->where('application_number', 'like', "%{$search}%")
                          ->orWhere('surname', 'like', "%{$search}%")
                          ->orWhere('first_name', 'like', "%{$search}%");
                  });
            });
        }

        $payments = $query->orderBy('created_at', 'desc')->paginate(25);
        $sessions = AcademicSession::orderBy('name', 'desc')->get();
        $paymentTypes = PaymentType::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
        $programmes = Programme::where('is_active', true)->orderBy('name')->get();

        return view('admin.payments.index', compact('payments', 'sessions', 'paymentTypes', 'programmes'));
    }

    public function offlineForm()
    {
        $sessions = AcademicSession::where('is_active', true)->orderByDesc('is_current')->orderByDesc('name')->get();
        $paymentTypes = PaymentType::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
        $currentSession = AcademicSession::current();

        return view('admin.payments.offline', compact('sessions', 'paymentTypes', 'currentSession'));
    }

    public function searchStudents(Request $request)
    {
        // Keep this endpoint deliberately small: the offline-payment form should
        // never load the entire student table into the browser.  Normalising the
        // term also lets staff find an ID whether they type the slashes or not.
        $term = trim(preg_replace('/\s+/', ' ', (string) $request->input('q')));
        if (mb_strlen($term) < 2) {
            return response()->json([]);
        }

        $payerType = strtolower((string) $request->input('type', 'both'));
        if (!in_array($payerType, ['student', 'applicant', 'both'], true)) {
            $payerType = 'both';
        }

        $registrationTerm = strtolower(preg_replace('/[^a-z0-9]/i', '', $term));
        $likeTerm = "%{$term}%";
        $registrationLike = "%{$registrationTerm}%";
        $payers = collect();

        if (in_array($payerType, ['student', 'both'], true)) {
            $students = Student::with(['programme', 'academicSession'])
                ->where(function ($query) use ($likeTerm, $registrationLike) {
                    $query->where('registration_number', 'like', $likeTerm)
                        ->orWhere('surname', 'like', $likeTerm)
                        ->orWhere('first_name', 'like', $likeTerm)
                        ->orWhere('middle_name', 'like', $likeTerm)
                        ->orWhere('email', 'like', $likeTerm)
                        ->orWhere('phone', 'like', $likeTerm)
                        ->orWhereRaw("TRIM(CONCAT_WS(' ', surname, first_name, middle_name)) LIKE ?", [$likeTerm]);
                    if ($registrationLike !== '%%') {
                        $query->orWhereRaw(
                            "REPLACE(REPLACE(REPLACE(LOWER(registration_number), '/', ''), '-', ''), ' ', '') LIKE ?",
                            [$registrationLike]
                        );
                    }
                })
                ->orderBy('surname')
                ->orderBy('first_name')
                ->limit(25)
                ->get()
                ->map(fn (Student $student) => [
                    'id' => $student->id,
                    'payer_type' => 'student',
                    'identifier' => $student->registration_number,
                    'registration_number' => $student->registration_number,
                    'application_number' => null,
                    'name' => $student->full_name ?: 'Unnamed student',
                    'programme' => $student->programme?->name ?: $student->programme_type,
                    'session' => $student->academicSession?->name,
                ]);
            $payers = $payers->concat($students);
        }

        if (in_array($payerType, ['applicant', 'both'], true)) {
            $applicants = Applicant::with(['programme', 'academicSession'])
                ->where(function ($query) use ($likeTerm, $registrationLike) {
                    $query->where('application_number', 'like', $likeTerm)
                        ->orWhere('surname', 'like', $likeTerm)
                        ->orWhere('first_name', 'like', $likeTerm)
                        ->orWhere('other_names', 'like', $likeTerm)
                        ->orWhere('email', 'like', $likeTerm)
                        ->orWhere('phone', 'like', $likeTerm)
                        ->orWhereRaw("TRIM(CONCAT_WS(' ', surname, first_name, other_names)) LIKE ?", [$likeTerm]);
                    if ($registrationLike !== '%%') {
                        $query->orWhereRaw(
                            "REPLACE(REPLACE(REPLACE(LOWER(application_number), '/', ''), '-', ''), ' ', '') LIKE ?",
                            [$registrationLike]
                        );
                    }
                })
                ->orderBy('surname')
                ->orderBy('first_name')
                ->limit(25)
                ->get()
                ->map(fn (Applicant $applicant) => [
                    'id' => $applicant->id,
                    'payer_type' => 'applicant',
                    'identifier' => $applicant->application_number,
                    'registration_number' => null,
                    'application_number' => $applicant->application_number,
                    'name' => $applicant->full_name ?: 'Unnamed applicant',
                    'programme' => $applicant->programme?->name ?: $applicant->programme_type,
                    'session' => $applicant->academicSession?->name,
                ]);
            $payers = $payers->concat($applicants);
        }

        return response()->json($payers->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->take(25)->values());
    }

    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Payments');
        $headers = ['payer_id', 'amount', 'reference', 'description', 'notes'];
        $sheet->fromArray([$headers], null, 'A1');
        $sheet->fromArray([['SBRS/RS/26/000001', 150000, 'BANK-REF-001', 'Registration fee', 'Offline payment']], null, 'A2');
        $sheet->getStyle('A1:E1')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A1:E1')->getFill()->setFillType('solid')->getStartColor()->setARGB('006633');
        foreach (range('A', 'E') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $instructions = $spreadsheet->createSheet();
        $instructions->setTitle('Instructions');
        $instructions->fromArray([
            ['SBRS OFFLINE PAYMENT UPLOAD'],
            ['payer_id', 'Student registration number (SBRS/...) or applicant number (application fee/admission fee)'],
            ['student_id / application_id', 'Older templates are also accepted. Use the column that matches the selected payment type.'],
            ['amount', 'Positive amount in Naira'],
            ['payment_type / session / sponsor', 'Select these once on the upload form. Do not add them to the spreadsheet.'],
            ['reference', 'Bank teller, transfer reference or official receipt number'],
            ['description / notes', 'Optional payment details'],
        ], null, 'A1');
        $instructions->getStyle('A1:B1')->getFont()->setBold(true);
        $instructions->getColumnDimension('A')->setWidth(24);
        $instructions->getColumnDimension('B')->setWidth(105);

        $writer = new Xlsx($spreadsheet);
        $filename = 'sbrs-offline-payments-template.xlsx';
        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function storeOffline(Request $request)
    {
        $validated = $request->validate([
            'payer_id' => 'nullable|string|max:120',
            // Keep accepting student_id for old bookmarked/forms, but all new
            // forms submit payer_id so applicant and student payments share one
            // safe workflow.
            'student_id' => 'nullable|string|max:120',
            'payer_type' => 'nullable|in:student,applicant',
            'payment_type' => 'required|string|max:120',
            'academic_session_id' => 'required|exists:academic_sessions,id',
            'amount' => 'required|numeric|min:0.01',
            'reference' => 'required|string|max:120',
            'sponsor' => 'required|in:Self,Scholarship',
            'description' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:2000',
            'proof_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        try {
            [$legacyType, $paymentType] = $this->resolvePaymentType($validated['payment_type']);
            $sponsor = $this->normalizeSponsor($validated['sponsor']);
            $payer = $this->resolveOfflinePayer(
                $validated['payer_id'] ?? $validated['student_id'] ?? null,
                $validated['payer_type'] ?? null,
                $legacyType,
                $paymentType
            );
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        if ($this->referenceExists($payer, $validated['reference'])) {
            return back()->withInput()->with('error', 'This reference has already been recorded for the selected payer.');
        }

        $proofPath = null;
        try {
            if ($request->hasFile('proof_file')) {
                $proofPath = $request->file('proof_file')->store('payment_proofs', 'public');
            }

            DB::transaction(function () use ($validated, $payer, $legacyType, $paymentType, $sponsor, $proofPath) {
                $this->createOfflinePayment($payer, [
                    'payment_type' => $legacyType,
                    'payment_type_id' => $paymentType?->id,
                    'academic_session_id' => $validated['academic_session_id'],
                    'amount' => $validated['amount'],
                    'reference' => trim($validated['reference']),
                    'sponsor' => $sponsor,
                    'description' => $validated['description'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                    'proof_path' => $proofPath,
                ]);
            });

            return redirect()->route('admin.payments.index')->with('success', 'Offline payment recorded successfully.');
        } catch (\Throwable $e) {
            if ($proofPath) {
                Storage::disk('public')->delete($proofPath);
            }
            report($e);
            return back()->withInput()->with('error', 'The offline payment could not be recorded. Please try again.');
        }
    }

    public function uploadOffline(Request $request)
    {
        $validated = $request->validate([
            'payment_file' => 'required|file|mimes:xlsx,xls,csv,txt|max:10240',
            'payment_type' => 'required|string|max:120',
            'academic_session_id' => 'required|exists:academic_sessions,id',
            'sponsor' => 'required|in:Self,Scholarship',
        ]);

        try {
            [$legacyType, $paymentType] = $this->resolvePaymentType($validated['payment_type']);
            $sponsor = $this->normalizeSponsor($validated['sponsor']);
            $session = AcademicSession::findOrFail($validated['academic_session_id']);
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        try {
            $file = $request->file('payment_file');
            $originalName = strtolower((string) $file->getClientOriginalName());
            $reader = str_ends_with($originalName, '.csv') || str_ends_with($originalName, '.txt')
                ? IOFactory::createReader('Csv')
                : IOFactory::createReaderForFile($originalName);
            $reader->setReadDataOnly(true);
            $sheet = $reader->load($file->getRealPath())->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);
        } catch (\Throwable $e) {
            return back()->with('error', 'The spreadsheet could not be read. Please use the supplied template.');
        }

        $header = array_shift($rows) ?: [];
        $headerMap = [];
        foreach ($header as $column => $value) {
            $key = $this->normaliseHeader($value);
            if ($key !== '') {
                $headerMap[$key] = $column;
            }
        }

        $identifierColumn = collect(['payer_id', 'student_id', 'application_id'])
            ->first(fn (string $column) => array_key_exists($column, $headerMap));
        $required = ['amount', 'reference'];
        $missing = array_values(array_diff($required, array_keys($headerMap)));
        if (!$identifierColumn) {
            $missing[] = 'payer_id (or student_id / application_id)';
        }
        if ($missing) {
            return back()->with('error', 'Missing required spreadsheet columns: ' . implode(', ', $missing));
        }

        $records = [];
        $errors = [];
        $seen = [];
        foreach ($rows as $rowNumber => $row) {
            $excelRow = $rowNumber + 2;
            $values = [];
            foreach ($headerMap as $key => $column) {
                $values[$key] = is_string($row[$column] ?? null) ? trim($row[$column]) : ($row[$column] ?? null);
            }

            if (!array_filter($values, static fn ($value) => trim((string) $value) !== '')) {
                continue;
            }

            try {
                $identifier = trim((string) ($values[$identifierColumn] ?? ''));
                $payer = $this->findPayerByIdentifier($identifier, $legacyType, $paymentType);
                if (!$payer) {
                    throw new \RuntimeException('The selected payer number was not found for this payment type.');
                }

                $reference = trim((string) ($values['reference'] ?? ''));
                if ($reference === '') {
                    throw new \RuntimeException('Payment reference is required.');
                }

                $duplicateKey = $payer::class . '|' . $payer->id . '|' . strtolower($reference);
                if (isset($seen[$duplicateKey]) || $this->referenceExists($payer, $reference)) {
                    throw new \RuntimeException('This payment reference already exists for the selected payer.');
                }
                $seen[$duplicateKey] = true;

                $amount = (float) ($values['amount'] ?? 0);
                if ($amount <= 0) {
                    throw new \RuntimeException('Amount must be greater than zero.');
                }

                $records[] = [
                    'payer' => $payer,
                    'payment_type' => $legacyType,
                    'payment_type_id' => $paymentType?->id,
                    'academic_session_id' => $session->id,
                    'amount' => $amount,
                    'reference' => $reference,
                    'sponsor' => $sponsor,
                    'description' => trim((string) ($values['description'] ?? '')) ?: null,
                    'notes' => trim((string) ($values['notes'] ?? '')) ?: null,
                ];
            } catch (\Throwable $e) {
                $errors[] = 'Row ' . $excelRow . ': ' . $e->getMessage();
            }
        }

        if ($errors) {
            return back()->with('error', 'No records were imported because the upload contains errors. Correct the listed rows and upload again.')
                ->with('import_errors', $errors);
        }

        if (!$records) {
            return back()->with('error', 'The spreadsheet does not contain any payment rows.');
        }

        try {
            DB::transaction(function () use ($records) {
                foreach ($records as $record) {
                    $this->createOfflinePayment($record['payer'], $record);
                }
            });
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'The upload could not be completed. No rows were saved.');
        }

        return redirect()->route('admin.payments.index')->with('success', count($records) . ' offline payment(s) imported successfully.');
    }

    public function editOffline(Payment $payment)
    {
        $this->ensureOffline($payment);
        $payment->load(['payable', 'academicSession', 'paymentType']);
        $sessions = AcademicSession::where('is_active', true)->orderByDesc('is_current')->orderByDesc('name')->get();
        $paymentTypes = PaymentType::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();

        return view('admin.payments.offline-edit', compact('payment', 'sessions', 'paymentTypes'));
    }

    public function updateOffline(Request $request, Payment $payment)
    {
        $this->ensureOffline($payment);
        if ($payment->status === Payment::STATUS_CANCELLED) {
            return back()->with('error', 'Cancelled payments cannot be edited.');
        }

        $validated = $request->validate([
            'payment_type' => 'required|string|max:120',
            'academic_session_id' => 'required|exists:academic_sessions,id',
            'amount' => 'required|numeric|min:0.01',
            'reference' => 'required|string|max:120',
            'sponsor' => 'required|in:Self,Scholarship',
            'description' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:2000',
            'proof_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        try {
            [$legacyType, $paymentType] = $this->resolvePaymentType($validated['payment_type']);
            $sponsor = $this->normalizeSponsor($validated['sponsor']);
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
        $payer = $payment->payable;
        if (!$payer instanceof Student && !$payer instanceof Applicant) {
            return back()->with('error', 'This payment is not linked to a valid applicant or student.');
        }

        try {
            $this->assertPayerAllowed($payer, $legacyType, $paymentType);
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $duplicate = Payment::where('id', '!=', $payment->id)
            ->where('payable_type', $payer::class)
            ->where('payable_id', $payer->id)
            ->where('reference', trim($validated['reference']))
            ->where('status', '!=', Payment::STATUS_CANCELLED)
            ->exists();
        if ($duplicate) {
            return back()->withInput()->with('error', 'This reference is already used for the selected payer.');
        }

        $oldProof = $payment->proof_path;
        $newProof = $oldProof;
        try {
            if ($request->hasFile('proof_file')) {
                $newProof = $request->file('proof_file')->store('payment_proofs', 'public');
            }

            $payment->update([
                'payment_type' => $legacyType,
                'payment_type_id' => $paymentType?->id,
                'academic_session_id' => $validated['academic_session_id'],
                'amount' => $validated['amount'],
                'reference' => trim($validated['reference']),
                'sponsor' => $sponsor,
                'description' => $validated['description'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'proof_path' => $newProof,
                'payment_method' => 'Offline',
            ]);

            if ($newProof !== $oldProof && $oldProof) {
                Storage::disk('public')->delete($oldProof);
            }

            return redirect()->route('admin.payments.show', $payment)->with('success', 'Offline payment updated successfully.');
        } catch (\Throwable $e) {
            if ($newProof !== $oldProof && $newProof) {
                Storage::disk('public')->delete($newProof);
            }
            report($e);
            return back()->withInput()->with('error', 'The offline payment could not be updated.');
        }
    }

    public function cancelOffline(Payment $payment)
    {
        $this->ensureOffline($payment);
        if ($payment->status === Payment::STATUS_CANCELLED) {
            return back()->with('info', 'Payment is already cancelled.');
        }

        $gateway = (array) ($payment->gateway_response ?? []);
        $gateway['cancelled_by'] = auth()->id();
        $gateway['cancelled_at'] = now()->toIso8601String();
        $payment->update([
            'status' => Payment::STATUS_CANCELLED,
            'gateway_response' => $gateway,
        ]);

        return back()->with('success', 'Offline payment cancelled.');
    }

    public function show(Payment $payment)
    {
        $payment->load(['payable', 'academicSession', 'fee', 'paymentType']);
        return view('admin.payments.show', compact('payment'));
    }

    public function verify(Payment $payment)
    {
        if (strtolower((string) $payment->payment_method) === 'offline' || $payment->reference) {
            return response()->json(['type' => 'error', 'message' => 'Offline payments do not need Remita verification.']);
        }

        if (!$payment->hasRrr()) {
            return response()->json(['type' => 'error', 'message' => 'No RRR found for this payment.']);
        }

        $result = $this->remitaService->verifyPayment($payment);

        if ($result['success'] && $result['status'] === 'successful') {
            return response()->json([
                'type' => 'success',
                'message' => 'Payment verified successfully!',
                'status' => 'successful',
            ]);
        } elseif ($result['success'] && $result['status'] === 'pending') {
            return response()->json([
                'type' => 'warning',
                'message' => 'Payment is still pending.',
                'status' => 'pending',
            ]);
        }

        return response()->json([
            'type' => 'error',
            'message' => 'Verification: ' . ($result['message'] ?? 'Failed'),
            'status' => 'failed',
        ]);
    }

    public function verifyByRrr(Request $request)
    {
        $rrr = trim($request->rrr);
        if (empty($rrr)) {
            return response()->json(['type' => 'error', 'message' => 'Please enter an RRR.']);
        }

        $payment = Payment::where('rrr', $rrr)->first();
        if (!$payment) {
            return response()->json(['type' => 'error', 'message' => "No payment found with RRR: {$rrr}"]);
        }

        return $this->verify($payment);
    }

    public function export(Request $request)
    {
        $query = Payment::with(['payable', 'academicSession']);

        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('payment_type')) $query->where('payment_type', $request->payment_type);
        if ($request->filled('academic_session_id')) $query->where('academic_session_id', $request->academic_session_id);
        if ($request->filled('programme_id')) {
            $programmeId = $request->input('programme_id');
            $query->whereHasMorph('payable', [Student::class, Applicant::class], function ($payer) use ($programmeId) {
                $payer->where('programme_id', $programmeId);
            });
        }

        $payments = $query->orderBy('created_at', 'desc')->get();

        $filename = 'payments_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($payments) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Reference', 'RRR', 'Order ID', 'Payer', 'Type', 'Sponsor', 'Method', 'Amount', 'Status', 'Session', 'Description']);
            foreach ($payments as $payment) {
                $payerName = $payment->payable ? ($payment->payable->surname ?? '') . ' ' . ($payment->payable->first_name ?? '') : 'N/A';
                fputcsv($file, [
                    $payment->created_at->format('Y-m-d H:i'),
                    $payment->reference ?? 'N/A',
                    $payment->rrr ?? 'N/A',
                    $payment->order_id ?? 'N/A',
                    trim($payerName),
                    ucfirst($payment->payment_type),
                    $payment->sponsor ?? 'N/A',
                    $payment->payment_method ?? 'Online',
                    number_format($payment->amount, 2),
                    ucfirst($payment->status),
                    $payment->academicSession->name ?? 'N/A',
                    $payment->description ?? '',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function destroy(Request $request, Payment $payment)
    {
        $isOffline = strtolower((string) $payment->payment_method) === 'offline' || !empty($payment->reference);
        $isPending = $payment->status === Payment::STATUS_PENDING;
        $isCancelledOffline = $isOffline && $payment->status === Payment::STATUS_CANCELLED;

        if (!$isPending && !$isCancelledOffline) {
            return back()->with('error', 'Only pending payments or cancelled offline payments can be deleted.');
        }

        $request->validate([
            'deletion_reason' => 'required|string|min:5|max:500',
        ]);

        try {
            $proofPath = DB::transaction(function () use ($payment, $request) {
                $lockedPayment = Payment::whereKey($payment->id)->lockForUpdate()->first();
                if (!$lockedPayment) {
                    throw new \RuntimeException('The payment no longer exists.');
                }

                $lockedIsOffline = strtolower((string) $lockedPayment->payment_method) === 'offline'
                    || !empty($lockedPayment->reference);
                if ($lockedPayment->status !== Payment::STATUS_PENDING
                    && !($lockedIsOffline && $lockedPayment->status === Payment::STATUS_CANCELLED)) {
                    throw new \RuntimeException('Only pending payments or cancelled offline payments can be deleted.');
                }

                $proofPath = $lockedPayment->proof_path;
                $lockedPayment->update([
                    'deleted_by' => auth()->id(),
                    'deletion_reason' => $request->input('deletion_reason'),
                ]);
                $lockedPayment->delete();

                return $proofPath;
            });
        } catch (\RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        if ($proofPath) {
            Storage::disk('public')->delete($proofPath);
        }

        return back()->with('success', $isPending ? 'Pending payment deleted successfully.' : 'Payment record deleted.');
    }

    private function ensureOffline(Payment $payment): void
    {
        abort_unless(
            strtolower((string) $payment->payment_method) === 'offline' || !empty($payment->reference),
            403,
            'Only offline payments can be changed here.'
        );
    }

    /**
     * Return the payer model expected by a payment type. Application and
     * admission belong to Applicants; registration, examination, hostel and
     * other student services belong to Students unless a custom type explicitly
     * allows applicants as well.
     */
    private function resolveOfflinePayer(?string $payerId, ?string $requestedType, string $legacyType, ?PaymentType $paymentType): Model
    {
        $payerId = trim((string) $payerId);
        if ($payerId === '') {
            throw new \RuntimeException('Select an applicant or student before saving the payment.');
        }

        $allowedType = $this->allowedPayerType($legacyType, $paymentType);
        if ($allowedType !== 'both') {
            $requestedType = $allowedType;
        } elseif (!$requestedType) {
            throw new \RuntimeException('Choose whether this payment belongs to an applicant or a student.');
        }

        if (!in_array($requestedType, ['student', 'applicant'], true)) {
            throw new \RuntimeException('The selected payer type is invalid.');
        }
        if ($allowedType !== 'both' && $requestedType !== $allowedType) {
            throw new \RuntimeException('This payment type must be recorded for an ' . $allowedType . '.');
        }

        $payer = $requestedType === 'applicant'
            ? Applicant::find($payerId)
            : Student::find($payerId);
        if (!$payer) {
            throw new \RuntimeException('The selected ' . $requestedType . ' could not be found. Search again and select a record.');
        }

        return $payer;
    }

    private function findPayerByIdentifier(string $identifier, string $legacyType, ?PaymentType $paymentType): ?Model
    {
        $identifier = trim($identifier);
        if ($identifier === '') {
            return null;
        }

        $compactIdentifier = strtolower(preg_replace('/[^a-z0-9]/i', '', $identifier));
        $allowedType = $this->allowedPayerType($legacyType, $paymentType);
        if (in_array($allowedType, ['student', 'both'], true)) {
            $student = Student::where(function ($query) use ($identifier, $compactIdentifier) {
                $query->whereRaw('LOWER(registration_number) = ?', [strtolower($identifier)])
                    ->orWhereRaw("REPLACE(REPLACE(REPLACE(LOWER(registration_number), '/', ''), '-', ''), ' ', '') = ?", [$compactIdentifier]);
            })->first();
            if ($student) {
                return $student;
            }
        }
        if (in_array($allowedType, ['applicant', 'both'], true)) {
            return Applicant::where(function ($query) use ($identifier, $compactIdentifier) {
                $query->whereRaw('LOWER(application_number) = ?', [strtolower($identifier)])
                    ->orWhereRaw("REPLACE(REPLACE(REPLACE(LOWER(application_number), '/', ''), '-', ''), ' ', '') = ?", [$compactIdentifier]);
            })->first();
        }

        return null;
    }

    private function allowedPayerType(string $legacyType, ?PaymentType $paymentType): string
    {
        // These categories are always created before a student account exists.
        // Do not let a misconfigured custom record accidentally attach an
        // application/admission payment to a student.
        if (in_array($legacyType, ['application', 'admission'], true)) {
            return 'applicant';
        }

        if ($paymentType && in_array($paymentType->payer_type, ['applicant', 'student', 'both'], true)) {
            return $paymentType->payer_type;
        }

        return 'student';
    }

    private function assertPayerAllowed(Model $payer, string $legacyType, ?PaymentType $paymentType): void
    {
        $payerType = $payer instanceof Applicant ? 'applicant' : ($payer instanceof Student ? 'student' : null);
        if (!$payerType) {
            throw new \RuntimeException('This payment is not linked to a valid applicant or student.');
        }

        $allowedType = $this->allowedPayerType($legacyType, $paymentType);
        if ($allowedType !== 'both' && $payerType !== $allowedType) {
            throw new \RuntimeException('This payment type can only be recorded for an ' . $allowedType . '.');
        }
    }

    private function referenceExists(Model $payer, string $reference, ?string $exceptId = null): bool
    {
        return $payer->payments()
            ->whereRaw('LOWER(reference) = ?', [strtolower(trim($reference))])
            ->where('status', '!=', Payment::STATUS_CANCELLED)
            ->when($exceptId, fn ($query) => $query->where('id', '!=', $exceptId))
            ->exists();
    }

    private function normaliseHeader($value): string
    {
        $value = strtolower(trim((string) $value));
        $value = preg_replace('/[^a-z0-9]+/', '_', $value);
        return trim((string) $value, '_');
    }

    private function normalizeSponsor(?string $value): string
    {
        $sponsor = strtolower(trim((string) $value));
        return match ($sponsor) {
            'self', 'self sponsor', 'self-sponsored', 'self_sponsored' => 'Self',
            'scholarship', 'scholarship sponsor', 'scholar' => 'Scholarship',
            default => throw new \RuntimeException('Sponsor must be Self or Scholarship.'),
        };
    }

    private function resolvePaymentType(?string $value): array
    {
        $value = trim((string) $value);
        if ($value === '') {
            throw new \RuntimeException('Payment type is required.');
        }

        if (str_starts_with(strtolower($value), 'custom:')) {
            $id = trim(substr($value, 7));
            $type = PaymentType::where('id', $id)->where('is_active', true)->first();
            if (!$type) {
                throw new \RuntimeException('The selected payment type is not active.');
            }
            return [$type->legacyType(), $type];
        }

        $type = PaymentType::where('is_active', true)
            ->where(fn ($query) => $query->whereRaw('LOWER(code) = ?', [strtolower($value)]))
            ->first();
        if ($type) {
            return [$type->legacyType(), $type];
        }

        $legacy = strtolower($value);
        if (in_array($legacy, ['application', 'admission', 'registration', 'examination', 'hostel', 'other'], true)) {
            return [$legacy, null];
        }

        throw new \RuntimeException('Payment type is invalid or inactive.');
    }

    private function createOfflinePayment(Model $payer, array $values): Payment
    {
        return Payment::create([
            'payable_type' => $payer::class,
            'payable_id' => $payer->id,
            'payment_type' => $values['payment_type'],
            'payment_type_id' => $values['payment_type_id'] ?? null,
            'academic_session_id' => $values['academic_session_id'],
            'amount' => $values['amount'],
            'sponsor' => $values['sponsor'],
            'currency' => 'NGN',
            'reference' => $values['reference'],
            'proof_path' => $values['proof_path'] ?? null,
            'description' => $values['description'] ?? null,
            'notes' => $values['notes'] ?? null,
            'status' => Payment::STATUS_SUCCESSFUL,
            'payment_method' => 'Offline',
            'paid_at' => now(),
            'verified_at' => now(),
            'recorded_by' => auth()->id(),
            'is_full_payment' => true,
            'full_payment_at' => now(),
            'gateway_response' => [
                'source' => 'admin_offline',
                'recorded_at' => now()->toIso8601String(),
                'recorded_by' => auth()->id(),
            ],
        ]);
    }
}
