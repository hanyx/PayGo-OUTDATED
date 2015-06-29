<?php
if (count($url) == 5) {
    if ($url[3] == "view") {
        $currentMessage = new Message();

        if ($currentMessage->read($url[4]) && $currentMessage->getFolder() == MessageFolder::SENT&& $currentMessage->isOwner($uas->getUser()->getId())) {
            $currentMessage->setRead();

            $currentMessage->update();

            $response = array();

            $response['message'] = $currentMessage->getMessage();
            $response['date'] = formatDate(strtotime($currentMessage->getDate()));
            $response['sender'] = $currentMessage->getRecipient();


            die(json_encode($response));
        }
    }
}

$messages = $uas->getUser()->getMessages(MessageFolder::SENT);

if (count($messages) == 0) {
    $uas->addMessage(new ErrorSuccessMessage("No messages found"));
}

__header();

if (count($messages) != 0) {
    ?>
    <table class="table table-hover table-vcenter pi-table bigger-but-not-huge-table table-messages">
        <tbody>
        <?php
        foreach ($messages as $message) {
            ?>
            <tr data-id="<?php echo $message->getId(); ?>" class="message-modal-launcher">
                <td class="hidden-xs" style="width: 140px;"><?php echo $message->getRecipient(); ?></td>
                <td><?php if (!$message->getRead()) { ?><strong><?php } ?><?php echo $message->getExcerpt(); ?><?php if (!$message->getRead()) { ?></strong><?php } ?></td>
                <td class="visible-lg text-muted" style="width: 80px;"></td>
                <td class="visible-lg text-muted" style="width: 120px;"><span class="muted" style="font-size:90%;"><?php echo formatTime(strtotime($message->getDate())); ?> ago</span></td>
            </tr>
        <?php
        }
        ?>
        </tbody>
    </table>
    <script>
        $(function() {
            $('.message-modal-launcher').click(function() {
                $.post('/seller/messages/sent/view/' + $(this).attr('data-id'), {}, function(data) {
                    var data = $.parseJSON(data);

                    $('#message-modal').modal();

                    $('#modal-title').html(data.sender + ' - ' + data.date);
                    $('#message-body').html(data.message);
                });
            });
        });
    </script>

    <div class="modal fade" id="message-modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal-title"></h4>
                </div>
                <div class="modal-body" id="message-body"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Close</button>
                </div>
            </div>
        </div>
    </div>
<?php
}
__footer();