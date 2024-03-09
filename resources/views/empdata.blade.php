{{-- resources/views/empdata.blade.php --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Data</title>
    <style>
        @font-face {
            font-family: 'THSarabunNew';

            src: url('{{ public_path('fonts/THSarabunNew.ttf') }}') format('truetype');
        }

        body,
        th,
        td {
            font-family: 'THSarabunNew';
            /* Reference the font family name here */
            src: url('{{ public_path('fonts/THSarabunNew.ttf') }}') format('truetype');
        }

        table {
            width: 100%;
            border-collapse: collapse;

        }

        th,
        td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            font-weight: normal;
            /* This makes the font weight the same as <p> which is usually 'normal' */
        }
        h1,h2,h3{
            font-weight: normal;
        }
    </style>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.3.2/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
</head>

<body>

    <div class="outline" id="content">
        <div class="container">
            <!--  <button id="download">Download PDF</button>  -->
            <!-- Begin of text to copy above the table -->
            <div class="header-text">
                <!-- Placeholder for your text. Replace this with your actual content -->
                <center>
                    <h3>บันทึกข้อความ</h3>
                </center>

                <h3>ส่วนราชการ__________________________________________________</h3>
                <h3>ที่______________________________วันที่________________________</h3>
                <h3>เรื่อง________________________________________________________</h3>
                <p>เรียน ผู้อำนวยการโรงพยาบาลบุรีรัมย์</p>
                <p> ตามที่กลุ่มงานพัสดุ ได้กําหนดเวลาปฏิบัติงาน เริ่มเวลา ๐๘.๓๐ น. มาสายได้ไม่เกิน ๔.๓๕ น.</p>
                <p>และลงเวลาเลิกปฏิบัติงาน ๑๖.๓๐ น. โดยการสแกนลายนิ้วมือ นั้น</p>
                <p> ในการนี้ จึงขอรายงานการลงเวลาปฏิบัติงานของเจ้าหน้าที่กลุ่มงานพัสดุ ประจําเดือน พฤศจิกายน ๒๕๖๔</p>


                <!-- End of placeholder -->
            </div>
            <!-- End of text to copy above the table -->


            <table border="1">
                <thead>
                    <tr>
                        <th>ลำดับ</th>
                        <th>รายชื่อ</th>
                        <th>จำนวนวัน</th>
                        <th>สายช่วงเช้าเกิน 8:30</th>
                        <th>สายช่วงบ่ายเกิน 13:05</th>
                        <th>วันที่มาทำงานทั้งหมด</th>
                        <th>วันลา</th>
                        <th>จำนวนวันที่มาทำงาน</th>
                        <th>ลายมือชื่อ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($employees as $employee)
                        <tr>
                            <td>{{ $employee->Order }}</td>
                            <td>{{ $employee->EmployeeName }}</td>
                            <td>{{ $employee->{'จำนวนวัน'} }}</td>
                            <td>{{ $employee->{'มาสายช่วงเช้าเกิน 8:30'} }}</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>





    <script>
        document.getElementById('download').addEventListener('click', function() {
            // Get the element.
            var element = document.getElementById('content');

            // Options for html2canvas. Adjust the scale as necessary for your content.
            var canvasOptions = {
                scale: 2, // Adjust scaling to fit content on A4. You might need to try different values depending on your content.
                useCORS: true // Use this if you have images that are hosted on different domains.
            };

            // Generate the canvas.
            html2canvas(element, canvasOptions).then(function(canvas) {
                // Create a blob from the canvas
                canvas.toBlob(function(blob) {
                    // Create a new FileReader
                    var reader = new FileReader();
                    reader.readAsDataURL(blob);
                    reader.onloadend = function() {
                        var base64data = reader.result;

                        // Generate PDF using jsPDF
                        const {
                            jsPDF
                        } = window.jspdf;
                        var pdf = new jsPDF('p', 'mm',
                            'a4'); // Set the PDF to portrait mode with A4 dimensions

                        // Calculate the number of pages.
                        var pageHeight = pdf.internal.pageSize.height;
                        var imgHeight = canvas.height * 210 / canvas.width;
                        var heightLeft = imgHeight;

                        var position = 0;

                        // Add content to the first page.
                        pdf.addImage(base64data, 'PNG', 0, position, 210, imgHeight);
                        heightLeft -= pageHeight;

                        // Add new pages if the content overflows.
                        while (heightLeft >= 0) {
                            position = heightLeft - imgHeight;
                            pdf.addPage();
                            pdf.addImage(base64data, 'PNG', 0, position, 210, imgHeight);
                            heightLeft -= pageHeight;
                        }

                        // Save the PDF
                        pdf.save('content.pdf');
                    };
                });
            });
        });
    </script>

</body>

</html>
