<?php
$download = new Download();

if (!$download->readByLink($url['1'])) {
    die();
}

if ($download->getIp() != '' && $download->getIp() != getRealIp()) {
    die();
}

if ($download->getIp() == '') {
    $download->setIp(getRealIp());

    $download->update();
}

$file = new File();

$file->read($download->getFileId());

$filepath = $config['upload']['directory'] . $file->getFile();

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename=' . $file->getName());
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . filesize($filepath));
ob_clean();
flush();
readfile($filepath);
exit;