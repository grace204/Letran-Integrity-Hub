<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit();
}

require_once('../tcpdf/tcpdf.php');
include "../includes/connect.php";

// Create a new PDF document
$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Colegio de San Juan de Letran');
$pdf->SetTitle('Student Violations Report');
$pdf->SetMargins(15, 25, 15); // Set margins (left, top, right)
$pdf->SetHeaderMargin(15); // Header margin
$pdf->setPrintFooter(false);

// Add a page
$pdf->AddPage();

// Add logo
$pdf->Image('../assets/52889902_2488780057802287_7045192790166208512_n.png', 35, 27, 20, 0, 'PNG');

// Set the header with institution details
$pdf->SetFont('times', 'B', 14);
$pdf->Cell(0, 10, 'Colegio de San Juan de Letran – Manaoag', 0, 1, 'C');
$pdf->SetFont('times', '', 10);
$pdf->Cell(0, 5, 'Castro St. Poblacion, Manaoag, Pangasinan, Philippines', 0, 1, 'C');
$pdf->Cell(0, 5, '+63(075)-5822976', 0, 1, 'C');
$pdf->Ln(5); // Add a space

// Fetch violations data
$query = "SELECT s.student_id, s.student_name, s.middlename, s.lastname, v.violation_type, v.violation_description, v.violation_date
          FROM students s
          JOIN violations v ON s.student_id = v.student_id";
$result = $conn->query($query);

// Define column headers and calculate column widths based on page width
$header = ['Name', 'Middle Name', 'Last Name', 'Violation Type', 'Violation Date'];
$pageWidth = $pdf->getPageWidth() - 30; // Total width minus margins
$colWidths = [0.18 * $pageWidth, 0.18 * $pageWidth, 0.18 * $pageWidth, 0.25 * $pageWidth, 0.21 * $pageWidth];

// Output table header
$pdf->SetFont('times', 'B', 10);
foreach ($header as $i => $heading) {
    $pdf->Cell($colWidths[$i], 8, $heading, 1, 0, 'C');
}
$pdf->Ln();

// Output data rows
$pdf->SetFont('times', '', 10);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Format the violation date to include AM/PM
        $formattedDate = date('Y-m-d h:i A', strtotime($row['violation_date']));
        
        $pdf->Cell($colWidths[0], 8, $row['student_name'], 1, 0, 'C');
        $pdf->Cell($colWidths[1], 8, $row['middlename'], 1, 0, 'C');
        $pdf->Cell($colWidths[2], 8, $row['lastname'], 1, 0, 'C');
        $pdf->Cell($colWidths[3], 8, $row['violation_type'], 1, 0, 'C');
        $pdf->Cell($colWidths[4], 8, $formattedDate, 1, 0, 'C');
        $pdf->Ln();
    }
} else {
    $pdf->Cell(0, 8, 'No violations found.', 1, 1, 'C');
}

// Output the PDF document
$pdf->Output('violations_report.pdf', 'D'); // Download the file
exit();
?>
