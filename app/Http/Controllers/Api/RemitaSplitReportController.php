<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RemitaSplitReportController
{
    public function summary(Request $request)
    {
        $from = $this->date($request->query('date_from'));
        $to = $this->date($request->query('date_to'));
        if ($from && $to && $from->gt($to)) [$from, $to] = [$to, $from];
        $status = strtolower(trim((string) $request->query('status', '')));
        $query = DB::table('payments')->where('payments.split_enabled', 1);
        if ($from) $query->where('payments.created_at', '>=', $from->startOfDay());
        if ($to) $query->where('payments.created_at', '<', $to->copy()->addDay()->startOfDay());
        if ($status !== '') $query->whereIn('payments.status', $this->statuses($status));
        if (($rrr = trim((string) $request->query('rrr', ''))) !== '') $query->where(function ($nested) use ($rrr) { $nested->where('payments.rrr', 'like', '%' . $rrr . '%')->orWhere('payments.reference', 'like', '%' . $rrr . '%')->orWhere('payments.order_id', 'like', '%' . $rrr . '%'); });

        $gross = 'COALESCE(CAST(payments.total_amount AS DECIMAL(15,2)), COALESCE(CAST(payments.base_amount AS DECIMAL(15,2)), CAST(payments.amount AS DECIMAL(15,2))) + COALESCE(CAST(payments.surcharge_amount AS DECIMAL(15,2)), 0), 0)';
        $base = 'COALESCE(CAST(payments.base_amount AS DECIMAL(15,2)), GREATEST(CAST(payments.amount AS DECIMAL(15,2)) - COALESCE(CAST(payments.surcharge_amount AS DECIMAL(15,2)), 0), 0))';
        $surcharge = 'COALESCE(CAST(payments.surcharge_amount AS DECIMAL(15,2)), 0)';
        $summary = (clone $query)->selectRaw('COUNT(*) AS invoice_count, COALESCE(SUM(' . $gross . '), 0) AS gross_amount, COALESCE(SUM(' . $base . '), 0) AS base_amount, COALESCE(SUM(' . $surcharge . '), 0) AS surcharge_amount')->first();
        $statusSummary = (clone $query)->select('payments.status')->selectRaw('COUNT(*) AS invoice_count, COALESCE(SUM(' . $gross . '), 0) AS gross_amount')->groupBy('payments.status')->orderByDesc('gross_amount')->get();
        $serviceSummary = (clone $query)->select('payments.payment_type')->selectRaw('COUNT(*) AS invoice_count, COALESCE(SUM(' . $gross . '), 0) AS gross_amount, COALESCE(SUM(' . $surcharge . '), 0) AS surcharge_amount')->groupBy('payments.payment_type')->orderByDesc('gross_amount')->limit(100)->get();
        $allocationQuery = DB::table('payment_split_items')->join('payments', 'payments.id', '=', 'payment_split_items.payment_id')->where('payments.split_enabled', 1);
        if ($from) $allocationQuery->where('payments.created_at', '>=', $from->startOfDay());
        if ($to) $allocationQuery->where('payments.created_at', '<', $to->copy()->addDay()->startOfDay());
        if ($status !== '') $allocationQuery->whereIn('payments.status', $this->statuses($status));
        if ($rrr !== '') $allocationQuery->where(function ($nested) use ($rrr) {
            $nested->where('payments.rrr', 'like', '%' . $rrr . '%')
                ->orWhere('payments.order_id', 'like', '%' . $rrr . '%');
        });
        $allocationSummary = $allocationQuery->select('payment_split_items.beneficiary_name')->selectRaw('COUNT(DISTINCT payment_split_items.payment_id) AS invoice_count, COALESCE(SUM(payment_split_items.beneficiary_amount), 0) AS allocated_amount')->groupBy('payment_split_items.beneficiary_name')->orderByDesc('allocated_amount')->get();
        $trend = (clone $query)->selectRaw("DATE_FORMAT(payments.created_at, '%Y-%m') AS bucket")->selectRaw('COUNT(*) AS invoice_count, COALESCE(SUM(' . $gross . '), 0) AS gross_amount, COALESCE(SUM(' . $surcharge . '), 0) AS surcharge_amount')->groupBy('bucket')->orderBy('bucket')->get();
        $limit = min(250, max(1, (int) $request->query('limit', 100)));
        $rows = (clone $query)->select(['payments.id', 'payments.rrr', 'payments.reference', 'payments.order_id', 'payments.payment_type', 'payments.description', 'payments.status', 'payments.created_at', 'payments.payable_id'])->selectRaw($gross . ' AS gross_amount')->selectRaw($base . ' AS base_amount_normalized')->selectRaw($surcharge . ' AS surcharge_amount_normalized')->orderByDesc('payments.created_at')->orderByDesc('payments.id')->limit($limit)->get()->map(fn ($row) => ['id' => (string) $row->id, 'reference' => (string) ($row->rrr ?: $row->reference ?: $row->order_id ?: ''), 'order_reference' => (string) ($row->order_id ?: ''), 'student_id' => (string) ($row->payable_id ?: ''), 'description' => (string) ($row->description ?: $row->payment_type ?: 'Payment'), 'status' => (string) ($row->status ?: 'unknown'), 'gross_amount' => (float) $row->gross_amount, 'base_amount' => (float) $row->base_amount_normalized, 'surcharge_amount' => (float) $row->surcharge_amount_normalized, 'created_at' => $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null])->values();

        return response()->json(['success' => true, 'source' => (string) config('services.remita_split_report.source', 'sbrs'), 'label' => 'SBRS', 'generated_at' => now()->toIso8601String(), 'summary' => ['invoice_count' => (int) ($summary->invoice_count ?? 0), 'gross_amount' => (float) ($summary->gross_amount ?? 0), 'base_amount' => (float) ($summary->base_amount ?? 0), 'surcharge_amount' => (float) ($summary->surcharge_amount ?? 0)], 'status_summary' => $statusSummary->map(fn ($row) => ['status' => (string) ($row->status ?: 'unknown'), 'invoice_count' => (int) $row->invoice_count, 'gross_amount' => (float) $row->gross_amount])->values(), 'service_summary' => $serviceSummary->map(fn ($row) => ['label' => (string) ($row->payment_type ?: 'Payment'), 'invoice_count' => (int) $row->invoice_count, 'gross_amount' => (float) $row->gross_amount, 'surcharge_amount' => (float) $row->surcharge_amount])->values(), 'allocation_summary' => $allocationSummary->map(fn ($row) => ['beneficiary_name' => (string) ($row->beneficiary_name ?: 'Beneficiary'), 'invoice_count' => (int) $row->invoice_count, 'allocated_amount' => (float) $row->allocated_amount])->values(), 'trend' => $trend->map(fn ($row) => ['bucket' => (string) $row->bucket, 'invoice_count' => (int) $row->invoice_count, 'gross_amount' => (float) $row->gross_amount, 'surcharge_amount' => (float) $row->surcharge_amount])->values(), 'rows' => $rows]);
    }

    private function date($value): ?Carbon { try { return $value ? Carbon::parse($value)->startOfDay() : null; } catch (\Throwable) { return null; } }
    private function statuses(string $status): array { return match ($status) { 'paid', 'successful', 'success' => ['paid', 'successful', 'success'], 'pending', 'processing' => ['pending', 'processing'], 'cancelled', 'canceled', 'failed', 'rejected' => ['cancelled', 'canceled', 'failed', 'rejected'], default => [$status] }; }
}
