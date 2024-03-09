<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class FetchDataController extends Controller
{
    public function index()
    {
        $query = "
        SELECT
        ROW_NUMBER() OVER (ORDER BY EmployeeName) AS `Order`,
        EmployeeName,
        COUNT(*) AS `จำนวนวัน`,
        SUM(CASE WHEN TimeIn > '08:30:00' THEN 1 ELSE 0 END) AS `มาสายช่วงเช้าเกิน 8:30`,
        SUM(CASE WHEN TimeOut > '16:30:00' THEN 1 ELSE 0 END) AS `ออกงานหลัง 16:30`
    FROM
        combined_file
    WHERE
        Date LIKE '2024-01-%'
    GROUP BY
        EmployeeName
    ORDER BY
        EmployeeName;
        ";

        $results = DB::select($query);

        $html = view('empdata', ['employees' => $results])->render();

        // Call the external PDF generation API with this HTML
        $pdf = $this->sendHTMLToPDFService($html);

        // Assuming the PDF is returned as a binary stream,
        // you can directly return it as a response for the user to download
        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="employee-data.pdf"');

    }

    protected function sendHTMLToPDFService($html)
    {
        // Initialize GuzzleHttp Client
        $client = new \GuzzleHttp\Client();

        // Api2Pdf endpoint for HTML to PDF conversion
        $endpoint = 'https://v2018.api2pdf.com/chrome/html';

        try {
            // Send a POST request to Api2Pdf
            $response = $client->post($endpoint, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    // Use your Api2Pdf API key here
                    'Authorization' => '28e330a4-7cba-44a4-a676-33f273ff5f40',
                ],
                'json' => [
                    // Include HTML content for conversion
                    'html' => $html,
                    // Additional options can be included here as needed
                ],
            ]);

            // Check if the response status code is 200 (OK)
            if ($response->getStatusCode() === 200) {
                // Decode the JSON response to get the PDF file URL
                $data = json_decode($response->getBody(), true);
                $pdfUrl = $data['pdf'];

                // Download the PDF file using Guzzle and return its contents
                $pdfResponse = $client->get($pdfUrl);
                return $pdfResponse->getBody();
            }
        } catch (\Exception $e) {
            // Handle exceptions or errors here
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function index2()
    {
        $query = "
        SELECT
        ROW_NUMBER() OVER (ORDER BY EmployeeName) AS `Order`,
        EmployeeName,
        COUNT(*) AS `จำนวนวัน`,
        SUM(CASE WHEN TimeIn > '08:30:00' THEN 1 ELSE 0 END) AS `มาสายช่วงเช้าเกิน 8:30`,
        SUM(CASE WHEN TimeOut > '16:30:00' THEN 1 ELSE 0 END) AS `ออกงานหลัง 16:30`
    FROM
        combined_file
    WHERE
        Date LIKE '2024-01-%'
    GROUP BY
        EmployeeName
    ORDER BY
        EmployeeName;
        ";

        $results = DB::select($query);

        return view('empdata', ['employees' => $results]);


    }
}
