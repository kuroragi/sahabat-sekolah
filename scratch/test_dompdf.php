<?php

require __DIR__.'/../vendor/autoload.php';

use Dompdf\Dompdf;

$dompdf = new Dompdf;
$dompdf->loadHtml('<h1>Test PDF Sahabat Sekolah</h1>');
$dompdf->render();
file_put_contents(__DIR__.'/test.pdf', $dompdf->output());
echo 'PDF_SUCCESS: '.filesize(__DIR__.'/test.pdf')." bytes\n";
