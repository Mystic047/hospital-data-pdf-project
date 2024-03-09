<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;
use Mpdf\Config\FontVariables;
use Mpdf\Config\ConfigVariables;
class PDFController extends Controller
{
    public function generateCustomPDF()
    {
        // Fetch data from the database
        $employees = DB::select("
            SELECT
                EmployeeName,
                COUNT(*) AS 'จำนวนวันของเดือนนี้',
                SUM(CASE WHEN TimeIn IS NOT NULL AND TimeIn <> '' THEN 1 ELSE 0 END) AS 'มีเวลาเข้างาน',
                SUM(CASE WHEN TimeIn IS NULL OR TimeIn = '' THEN 1 ELSE 0 END) AS 'ไม่มีเวลาเข้างาน',
                SUM(CASE WHEN TimeOut IS NOT NULL AND TimeOut <> '' THEN 1 ELSE 0 END) AS 'มีเวลาออกงาน',
                SUM(CASE WHEN TimeOut IS NULL OR TimeOut = '' THEN 1 ELSE 0 END) AS 'ไม่มีเวลาออกงาน',
                SUM(CASE WHEN TimeIn > '08:30:00' THEN 1 ELSE 0 END) AS 'มาสายช่วงเช้าเกิน 8:30'
            FROM
                combined_file
            WHERE
                Date LIKE '2024-01-%'
            GROUP BY
                EmployeeName
        ");

        // Start building your HTML content
        $html = "<h1>Employee Data for January 2024</h1>
                 <table border='1'>
                     <thead>
                         <tr>
                             <th>Employee Name</th>
                             <th>จำนวนวันของเดือนนี้</th>
                             <th>มีเวลาเข้างาน</th>
                             <th>ไม่มีเวลาเข้างาน</th>
                             <th>มีเวลาออกงาน</th>
                             <th>ไม่มีเวลาออกงาน</th>
                             <th>มาสายช่วงเช้าเกิน 8:30</th>
                         </tr>
                     </thead>
                     <tbody>";

        // Dynamically append rows for each employee
        foreach ($employees as $employee) {
            $html .= sprintf(
                "<tr>
                    <td>%s</td>
                    <td>%s</td>
                    <td>%s</td>
                    <td>%s</td>
                    <td>%s</td>
                    <td>%s</td>
                    <td>%s</td>
                </tr>",
                htmlspecialchars($employee->EmployeeName),
                htmlspecialchars($employee->{'จำนวนวันของเดือนนี้'}),
                htmlspecialchars($employee->{'มีเวลาเข้างาน'}),
                htmlspecialchars($employee->{'ไม่มีเวลาเข้างาน'}),
                htmlspecialchars($employee->{'มีเวลาออกงาน'}),
                htmlspecialchars($employee->{'ไม่มีเวลาออกงาน'}),
                htmlspecialchars($employee->{'มาสายช่วงเช้าเกิน 8:30'})
            );
        }

        // Close the HTML string
        $html .= "</tbody></table>";

        $defaultFontConfig = (new FontVariables())->getDefaults();
        $fontDirs = $defaultFontConfig['fontDir'];
        $defaultConfig = (new ConfigVariables())->getDefaults();
        $fontData = $defaultConfig['fontdata'];

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'fontDir' => array_merge($fontDirs, [
                storage_path('app/public/fonts'), // Use the storage_path function to get the correct path
            ]),
            'fontdata' => $fontData + [
                'thsarabunnew' => [
                    'R' => 'THSarabunNew.ttf', // Assuming you have only the regular font, if you have more (bold, italic), you can add them here
                ]
            ],
            'default_font' => 'thsarabunnew' // Set the default font to THSarabunNew
        ]);

        // Initialize mPDF and write HTML to PDF
        $mpdf = new Mpdf(['mode' => 'utf-8']);
        $mpdf->WriteHTML($html);

        // Set the PDF to landscape orientation
        $mpdf->AddPage('L');

        // Output the PDF to browser
        $mpdf->Output('employee-data.pdf', 'I');
    }
}
