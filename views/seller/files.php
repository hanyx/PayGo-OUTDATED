<?php
if (isset($_GET['getdata'])) {
    $data = array();

    $files = $uas->getUser()->getFiles();

    foreach ($files as $file) {
        $data[] = array(
            'file' => ($file->getName()),
            'delete' => '<a href=\'/seller/products/files/delete/' . $file->getId() . '\'><i class="fa fa-trash-o"></i></a>'
        );
    }

    echo json_encode(array('aaData' => $data));
    die();
}

if (count($url) == 4 && $url[3] == 'upload') {
    if (!empty($_FILES)) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        if (preg_grep('/' . pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION) . '/i' , $config['upload']['allowedFiles'])) {
            if ($_FILES['file']['size'] < 50000000) {
                $fileHandler = new File();

                $fileHandler->setOwner($uas->getUser()->getId());
                $fileHandler->setExtension(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
                $fileHandler->setName(htmlspecialchars($_FILES['file']['name']));

                $fileHandler->create();

                move_uploaded_file($_FILES['file']['tmp_name'], $config['upload']['directory'] . $fileHandler->getFile());

                die();
            } else {
                http_response_code(413);
            }
        } else {
            http_response_code(415);
        }
    }
    http_response_code(417);
}

__header();
?>
    <div class="row">
        <div class="container-fluid">
            <?php $uas->printMessages(); ?>
            <div class="col-md-12">
                <section class='panel'>
                    <table class="table pi-table" data-ride='products'>
                        <thead>
                        <tr>
                            <th>File</th>
                            <th style="width: 150px;">Delete</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </section>
            </div>
            <div class="col-md-12">
                <section class='panel'>
                    <div class="panel-body">
                        <div class='fileupload dropzone'></div>
                        <br>
                        <div style="text-align: center;"><i>Allowed files types: <?php echo implode(', ', $config['upload']['allowedFiles']); ?></i></div>
                    </div>
                </section>
            </div>
        </div>
    </div>
    <script>
        function loadData(newTable){
            if(!newTable){
                $('[data-ride=\'products\']').dataTable().fnDestroy();
            }

            $('[data-ride=\'products\']').dataTable( {
                'bProcessing': true,
                'sAjaxSource': '/seller/products/files/?getdata=true',
                'sDom': '<\'row\'<\'col-sm-6\'l><\'col-sm-6\'f>r>t<\'row\'<\'col-sm-6\'i><\'col col-sm-6\'p>>',
                'sPaginationType': 'full_numbers',
                'aoColumns': [
                    { 'mData': 'file' },
                    { 'mData': 'delete' },
                ]
            });
        }
        loadData(true);

        $('.fileupload').dropzone({
            url: '/seller/products/files/upload',
            maxFilesize: 50,
            uploadMultiple: false,
            maxFiles: 10000,
            acceptedFiles: '.<?php echo implode(',.', $config['upload']['allowedFiles']); ?>',
            init: function(){
                this.on('success', function(a, b){
                    loadData(false);
                });
            }
        });
    </script>
<?php
__footer();