<?php
$zipFile = '../vendor.zip';
$extractTo = '../';

if (!file_exists($zipFile)) {
    die("❌ No se encontró el archivo vendor.zip");
}

$zip = new ZipArchive;
if ($zip->open($zipFile) === TRUE) {
    $zip->extractTo($extractTo);
    $zip->close();
    echo "✅ Dependencias instaladas en /vendor";
} else {
    echo "❌ Error al abrir el archivo ZIP";
}
