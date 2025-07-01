<?php
require_once __DIR__ . '/vendor/autoload.php';

$mpdf = new \Mpdf\Mpdf();

$mpdf->WriteHTML('
    <h1 style="color:#5409DA;">✅ PDF Generated Successfully!</h1>
    <p>This PDF is generated from SmartMobileApp using mPDF library.</p>
');

$mpdf->Output('TestInvoice.pdf', 'I');  // 🔥 'I' = inline browser view
?>
