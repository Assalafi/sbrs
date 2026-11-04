<?php

use Illuminate\Support\Facades\DB;
use App\Models\Student;

include 'exfpdf.php';

date_default_timezone_set('Africa/Lagos');

$asset = static function (string $relative): string {
    $map = [
        'uploads/logo-transparent.png' => 'assets/images/unimaid-logo.png',
        'uploads/sign2.png' => 'assets/images/hostel-signature.png',
        'card/default.jpg' => 'assets/images/favicon.png',
    ];
    $relative = $map[$relative] ?? $relative;
    $absolute = function_exists('public_path') ? public_path($relative) : $relative;
    return is_string($absolute) && file_exists($absolute) ? $absolute : $relative;
};

$fitFont = static function ($pdf, string $text, float $width, float $maximum = 13, float $minimum = 8): float {
    $size = $maximum;
    while ($size > $minimum) {
        $pdf->SetFont('helvetica', 'B', $size);
        if ($pdf->GetStringWidth($text) <= $width) {
            break;
        }
        $size -= 0.5;
    }
    return $size;
};

$labelValue = static function ($pdf, float $x, float $y, float $width, string $label, string $value, float $size = 9.5): void {
    $pdf->SetXY($x, $y);
    $pdf->SetFont('helvetica', '', 7.2);
    $pdf->SetTextColor(103, 111, 118);
    $pdf->Cell($width, 4, strtoupper($label), 0, 0, 'L');
    $pdf->SetXY($x, $y + 4);
    $pdf->SetFont('helvetica', 'B', $size);
    $pdf->SetTextColor(35, 48, 59);
    $pdf->MultiCell($width, 4.3, $value !== '' ? $value : 'Not provided', 0, 'L');
};

$users = isset($student)
    ? [(object) [
        'id' => data_get($reservation ?? [], 'id', 0),
        'occupant' => data_get($student, 'registration_number')
            ?: data_get($student, 'id_no')
            ?: data_get($student, 'student_id', ''),
        'hall' => data_get($reservation ?? [], 'hall')
            ?: data_get($reservation ?? [], 'hall_name')
            ?: data_get($reservation ?? [], 'hostel'),
        'block' => data_get($reservation ?? [], 'block')
            ?: data_get($reservation ?? [], 'block_name'),
        'room' => data_get($reservation ?? [], 'room')
            ?: data_get($reservation ?? [], 'room_number'),
        'bed' => data_get($reservation ?? [], 'bed')
            ?: data_get($reservation ?? [], 'bed_number'),
        'gender' => data_get($student, 'gender', ''),
    ]]
    : DB::table('hostel')->where('id', $id)->get();

// The FPDF template is shared with UG.  Supply the SBRS record in the same
// shape the UG renderer expects so the actual drawing/layout code remains
// identical.
if (isset($student)) {
    $photoFile = null;
    $photoPath = data_get($student, 'passport_photo')
        ?: data_get($student, 'photo')
        ?: data_get($student, 'picture');
    if ($photoPath) {
        $candidate = function_exists('storage_path')
            ? storage_path('app/public/' . ltrim((string) $photoPath, '/'))
            : $photoPath;
        if (is_file($candidate)) {
            $photoFile = $candidate;
        }
    }

    $photoBytes = $photoFile ? @file_get_contents($photoFile) : false;
    $photoMime = $photoFile && function_exists('mime_content_type')
        ? (mime_content_type($photoFile) ?: 'image/jpeg')
        : 'image/jpeg';
    $fullName = trim((string) data_get($student, 'full_name'));
    if ($fullName === '') {
        $fullName = trim(implode(' ', array_filter([
            data_get($student, 'first_name'),
            data_get($student, 'middle_name'),
            data_get($student, 'last_name'),
        ])));
    }
    $programme = data_get($student, 'programme.name')
        ?: data_get($student, 'programme_title')
        ?: data_get($student, 'programme_type')
        ?: 'School of Basic and Remedial Studies';
    $permitStudent = [
        'name' => $fullName,
        'programme_name' => $programme,
        'gender' => data_get($student, 'gender', ''),
        'photo_base64' => $photoBytes ? base64_encode($photoBytes) : null,
        'photo_mime' => $photoMime,
    ];
}
$pdf = new exFPDF('P', 'mm', 'A4');
$pdf->SetMargins(14, 14, 14);
$pdf->SetAutoPageBreak(false);
$pdf->AddPage();
$temporaryPhotoPaths = [];

foreach ($users as $row) {
    $external = is_array($permitStudent ?? null) ? $permitStudent : null;
    $student = null;
    $name = '';
    $programme = '';
    $gender = trim((string) ($row->gender ?? ''));

    if ($external) {
        $name = trim((string) ($external['name'] ?? $row->occupant));
        $programme = trim((string) ($external['programme_name'] ?? 'School of Basic and Remedial Studies'));
        $gender = trim((string) ($external['gender'] ?? $gender));
    } else {
        $student = Student::where('username', $row->occupant)->first();
        if ($student) {
            $name = trim(implode(' ', array_filter([$student->first_name, $student->last_name, $student->other_name])));
            $programme = trim((string) (optional($student->programs)->title ?: 'Programme unavailable'));
            $gender = trim((string) ($student->gender ?: $gender));
        }
    }

    $isSbrs = $external !== null;
    $name = $name !== '' ? $name : trim((string) $row->occupant);
    $programme = $programme !== '' ? $programme : ($isSbrs ? 'School of Basic and Remedial Studies' : 'Programme unavailable');
    $category = $isSbrs ? 'SBRS / Remedial' : 'Undergraduate';
    $genderKey = strtoupper(trim($gender));
    $genderLabel = in_array($genderKey, ['M', 'MALE', '1'], true)
        ? 'Male'
        : (in_array($genderKey, ['F', 'FEMALE', '2'], true) ? 'Female' : ($gender !== '' ? ucfirst(strtolower($gender)) : 'Not provided'));

    $permitNumber = sprintf('HP-%06d', (int) $row->id);
    $issuedDate = date('d F Y');
    try {
        $session = (string) \App\Http\Controllers\SystemSettingsController::getPostUtmeSession();
    } catch (\Throwable $e) {
        $session = '';
    }
    $session = $session !== '' ? $session : 'Current academic session';

    $photoPath = $asset('card/default.jpg');
    $photoType = 'JPG';
    if ($external && !empty($external['photo_base64']) && !empty($external['photo_mime'])) {
        $photoBytes = base64_decode((string) $external['photo_base64'], true);
        $mime = strtolower(trim((string) $external['photo_mime']));
        if ($photoBytes !== false && strlen($photoBytes) <= 1024 * 1024 && in_array($mime, ['image/jpeg', 'image/png'], true)) {
            $temporaryPhoto = tempnam(sys_get_temp_dir(), 'hostel_permit_');
            if ($temporaryPhoto !== false && file_put_contents($temporaryPhoto, $photoBytes) !== false && @getimagesize($temporaryPhoto)) {
                $photoPath = $temporaryPhoto;
                $photoType = $mime === 'image/png' ? 'PNG' : 'JPG';
                $temporaryPhotoPaths[] = $temporaryPhoto;
            }
        }
    } elseif ($student && trim((string) $student->picture) !== '') {
        $candidate = $asset('storage/picture/' . ltrim((string) $student->picture, '/'));
        $imageInfo = @getimagesize($candidate);
        if ($imageInfo && in_array($imageInfo['mime'] ?? '', ['image/jpeg', 'image/png'], true)) {
            $photoPath = $candidate;
            $photoType = ($imageInfo['mime'] ?? '') === 'image/png' ? 'PNG' : 'JPG';
        }
    }

    // Formal page frame with a minimal institutional accent.
    $pdf->SetFillColor(245, 246, 247);
    $pdf->Rect(0, 0, 210, 297, 'F');
    $pdf->SetFillColor(255, 255, 255);
    $pdf->Rect(10, 10, 190, 277, 'F');
    $pdf->SetDrawColor(205, 210, 214);
    $pdf->SetLineWidth(0.3);
    $pdf->Rect(10, 10, 190, 277, 'D');
    $pdf->SetFillColor(188, 145, 61);
    $pdf->Rect(10, 10, 190, 2, 'F');

    // Compact institutional header.
    $logo = $asset('uploads/logo-transparent.png');
    $pdf->Image($logo, 18, 18, 20, 21, 'PNG');
    $pdf->SetXY(44, 18);
    $pdf->SetFont('helvetica', 'B', 15.5);
    $pdf->SetTextColor(35, 48, 59);
    $pdf->Cell(100, 6.5, 'UNIVERSITY OF MAIDUGURI', 0, 1, 'L');
    $pdf->SetX(44);
    $pdf->SetFont('helvetica', 'I', 9);
    $pdf->SetTextColor(174, 67, 65);
    $pdf->Cell(100, 5, '...Centre of Excellence', 0, 1, 'L');
    $pdf->SetX(44);
    $pdf->SetFont('helvetica', '', 7.8);
    $pdf->SetTextColor(103, 111, 118);
    $pdf->Cell(100, 5, 'STUDENT AFFAIRS DIVISION', 0, 0, 'L');

    $pdf->SetFillColor(248, 245, 237);
    $pdf->SetDrawColor(221, 204, 166);
    $pdf->Rect(150, 18, 41, 23, 'DF');
    $pdf->SetXY(154, 21);
    $pdf->SetFont('helvetica', 'B', 7);
    $pdf->SetTextColor(145, 107, 40);
    $pdf->Cell(33, 4, 'PERMIT REFERENCE', 0, 1, 'C');
    $pdf->SetX(154);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetTextColor(57, 54, 47);
    $pdf->Cell(33, 6, $permitNumber, 0, 1, 'C');
    $pdf->SetX(154);
    $pdf->SetFont('helvetica', '', 6.8);
    $pdf->SetTextColor(112, 105, 91);
    $pdf->Cell(33, 4, $session, 0, 0, 'C');

    $pdf->SetDrawColor(218, 221, 223);
    $pdf->Line(18, 48, 192, 48);

    // Permit title and confirmation.
    $pdf->SetXY(18, 55);
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(35, 48, 59);
    $pdf->Cell(125, 7, 'HOSTEL ACCOMMODATION PERMIT', 0, 1, 'L');
    $pdf->SetXY(18, 63);
    $pdf->SetFont('helvetica', '', 8.2);
    $pdf->SetTextColor(103, 111, 118);
    $pdf->Cell(125, 4, 'Official authorisation for residence in a University hostel', 0, 0, 'L');
    $pdf->SetFillColor(42, 112, 79);
    $pdf->Rect(151, 55, 40, 11, 'F');
    $pdf->SetXY(154, 58);
    $pdf->SetFont('helvetica', 'B', 7.5);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(34, 5, 'PAID & CONFIRMED', 0, 0, 'C');
    $pdf->SetFillColor(40, 72, 98);
    $pdf->Rect(18, 72, 174, 1.2, 'F');

    // Student details on the left, photograph on the right.
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetDrawColor(218, 221, 223);
    $pdf->Rect(18, 80, 174, 68, 'DF');
    $pdf->SetFillColor(248, 248, 246);
    $pdf->Rect(18, 80, 174, 9, 'F');
    $pdf->SetXY(24, 83);
    $pdf->SetFont('helvetica', 'B', 8.5);
    $pdf->SetTextColor(35, 48, 59);
    $pdf->Cell(162, 4, 'STUDENT IDENTIFICATION', 0, 0, 'L');

    $nameSize = $fitFont($pdf, strtoupper($name), 116, 14, 9);
    $pdf->SetXY(24, 95);
    $pdf->SetFont('helvetica', 'B', $nameSize);
    $pdf->SetTextColor(35, 48, 59);
    $pdf->Cell(116, 7, strtoupper($name), 0, 1, 'L');
    $pdf->SetXY(24, 103);
    $pdf->SetFont('helvetica', 'B', 9.5);
    $pdf->SetTextColor(151, 111, 41);
    $pdf->Cell(116, 5, (string) $row->occupant, 0, 1, 'L');
    $pdf->SetDrawColor(228, 230, 231);
    $pdf->Line(24, 112, 140, 112);
    $labelValue($pdf, 24, 116, 76, 'Programme / school', $programme, 8.7);
    $labelValue($pdf, 104, 116, 36, 'Category', $category, 8.1);
    $labelValue($pdf, 24, 133, 50, 'Gender', $genderLabel, 8.7);
    $labelValue($pdf, 78, 133, 62, 'Issued', $issuedDate, 8.7);

    $photoX = 150;
    $photoY = 94;
    $photoW = 34;
    $photoH = 44;
    $pdf->SetFillColor(244, 244, 242);
    $pdf->SetDrawColor(182, 188, 193);
    $pdf->Rect($photoX, $photoY, $photoW, $photoH, 'DF');
    $imageInfo = @getimagesize($photoPath);
    if ($imageInfo && ($imageInfo[0] ?? 0) > 0 && ($imageInfo[1] ?? 0) > 0) {
        $scale = min(($photoW - 2) / $imageInfo[0], ($photoH - 2) / $imageInfo[1]);
        $drawW = $imageInfo[0] * $scale;
        $drawH = $imageInfo[1] * $scale;
        $drawX = $photoX + (($photoW - $drawW) / 2);
        $drawY = $photoY + (($photoH - $drawH) / 2);
        try {
            $pdf->Image($photoPath, $drawX, $drawY, $drawW, $drawH, $photoType);
        } catch (\Throwable $e) {
            // The framed space remains visible if an invalid legacy image is encountered.
        }
    }
    $pdf->SetXY(150, 140);
    $pdf->SetFont('helvetica', '', 6.8);
    $pdf->SetTextColor(103, 111, 118);
    $pdf->Cell(34, 4, 'STUDENT PHOTOGRAPH', 0, 0, 'C');

    // Light allocation panel with more room for long hall names.
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetDrawColor(216, 220, 223);
    $pdf->Rect(18, 157, 174, 42, 'DF');
    $pdf->SetFillColor(247, 247, 244);
    $pdf->Rect(18, 157, 174, 10, 'F');
    $pdf->SetFillColor(188, 145, 61);
    $pdf->Rect(18, 157, 3, 10, 'F');
    $pdf->SetXY(25, 160);
    $pdf->SetFont('helvetica', 'B', 8.5);
    $pdf->SetTextColor(35, 48, 59);
    $pdf->Cell(100, 4, 'ALLOCATED BED SPACE', 0, 0, 'L');
    $pdf->SetXY(144, 160);
    $pdf->SetFont('helvetica', '', 7);
    $pdf->SetTextColor(103, 111, 118);
    $pdf->Cell(42, 4, 'HOSTEL LOCATION', 0, 0, 'R');

    $allocationColumns = [
        ['x' => 18, 'width' => 72, 'label' => 'Hall', 'value' => trim((string) $row->hall), 'align' => 'L'],
        ['x' => 90, 'width' => 34, 'label' => 'Block', 'value' => trim((string) $row->block), 'align' => 'C'],
        ['x' => 124, 'width' => 34, 'label' => 'Room', 'value' => trim((string) $row->room), 'align' => 'C'],
        ['x' => 158, 'width' => 34, 'label' => 'Bed', 'value' => trim((string) $row->bed), 'align' => 'C'],
    ];
    foreach ($allocationColumns as $index => $allocationColumn) {
        if ($index > 0) {
            $pdf->SetDrawColor(226, 228, 229);
            $pdf->Line($allocationColumn['x'], 171, $allocationColumn['x'], 194);
        }
        $value = $allocationColumn['value'] !== '' ? $allocationColumn['value'] : '-';
        $innerX = $allocationColumn['x'] + ($allocationColumn['align'] === 'L' ? 7 : 3);
        $innerWidth = $allocationColumn['width'] - ($allocationColumn['align'] === 'L' ? 12 : 6);
        $pdf->SetXY($innerX, 173);
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetTextColor(103, 111, 118);
        $pdf->Cell($innerWidth, 4, strtoupper($allocationColumn['label']), 0, 1, $allocationColumn['align']);
        $valueSize = strlen($value) > 22 ? 8.2 : (strlen($value) > 14 ? 9.2 : 11);
        $pdf->SetXY($innerX, 180);
        $pdf->SetFont('helvetica', 'B', $valueSize);
        $pdf->SetTextColor(35, 48, 59);
        $pdf->MultiCell($innerWidth, 4.5, $value, 0, $allocationColumn['align']);
    }

    // Supporting instruction and approval in two balanced columns.
    $pdf->SetFillColor(249, 249, 247);
    $pdf->SetDrawColor(222, 224, 226);
    $pdf->Rect(18, 207, 102, 28, 'DF');
    $pdf->SetXY(24, 211);
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->SetTextColor(35, 48, 59);
    $pdf->Cell(90, 4, 'IMPORTANT', 0, 1, 'L');
    $pdf->SetXY(24, 218);
    $pdf->SetFont('helvetica', '', 7.7);
    $pdf->SetTextColor(83, 94, 102);
    $pdf->MultiCell(90, 3.8, 'This permit is personal and cannot be sold, transferred, altered, or used by another person. Hostel rules remain applicable.', 0, 'L');

    $pdf->SetFillColor(255, 255, 255);
    $pdf->Rect(126, 207, 66, 28, 'DF');
    $pdf->SetXY(130, 209);
    $pdf->SetFont('helvetica', '', 7);
    $pdf->SetTextColor(103, 111, 118);
    $pdf->Cell(58, 4, 'AUTHORISED BY', 0, 1, 'C');
    $signature = $asset('uploads/sign2.png');
    $pdf->Image($signature, 138, 212, 42, 9, 'PNG');
    $pdf->SetDrawColor(92, 101, 108);
    $pdf->Line(136, 222, 182, 222);
    $pdf->SetXY(130, 224);
    $pdf->SetFont('helvetica', 'B', 7.8);
    $pdf->SetTextColor(35, 48, 59);
    $pdf->Cell(58, 4, 'Prof. Amadu M. Brono', 0, 1, 'C');
    $pdf->SetX(130);
    $pdf->SetFont('helvetica', '', 6.6);
    $pdf->SetTextColor(103, 111, 118);
    $pdf->Cell(58, 4, 'Dean, Student Affairs', 0, 0, 'C');

    // Minimal verification footer.
    $pdf->SetDrawColor(218, 221, 223);
    $pdf->Line(18, 274, 192, 274);
    $pdf->SetXY(18, 278);
    $pdf->SetFont('helvetica', '', 7.1);
    $pdf->SetTextColor(103, 111, 118);
    $pdf->Cell(174, 4, 'Verify the student identity and allocated bed before admission into the hostel.', 0, 1, 'C');
    $pdf->SetX(18);
    $pdf->SetFont('helvetica', 'I', 6.7);
    $pdf->Cell(174, 4, 'Generated from the University of Maiduguri Student Portal', 0, 0, 'C');
}

if (isset($outputPath) && is_string($outputPath)) {
    $pdf->Output('F', $outputPath);
} else {
    $pdf->Output();
}

foreach ($temporaryPhotoPaths as $temporaryPhotoPath) {
    if (is_string($temporaryPhotoPath) && is_file($temporaryPhotoPath)) {
        @unlink($temporaryPhotoPath);
    }
}
exit;
?>
