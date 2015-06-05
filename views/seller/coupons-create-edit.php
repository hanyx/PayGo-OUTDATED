<?php
include_once('header.php');
?>
<section class='wrapper'>
        <div class='clearfix'>
            <?php $uas->printMessages(); ?>
        </div>

    <div class='row'>
        <div class="col-sm-12">
            <section class="panel">
                <div class="panel-body">
                    <form class="bs-example form-horizontal" method="post">
                        <div class="form-group">
                            <label class="col-lg-2 control-label">Name</label>
                            <div class="col-lg-10">
                                <input name='name' type='text' class='form-control' value=''/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-2 control-label">Reduction</label>
                            <div class="col-lg-10">
                                <div class='input-group'>
                                    <input name='reduction' type='number' class='form-control' value=''>
                                    <span class='input-group-addon'>%</span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-2 control-label">Used</label>
                            <div class="col-lg-10">
                                <input name='used' type='number' class='form-control' readonly value='15'/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-2 control-label">Maximum</label>
                            <div class="col-lg-10">
                                <input name='used' type='number' class='form-control' value='30'/>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-lg-offset-2 col-lg-10">
                                <button class="btn btn-sm btn-primary" type="submit"><?php echo (count($url) == 4 && $url['2'] == 'edit') ? 'Update' : 'Create'; ?></button>
                            </div>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>
</section>

    <?php
include_once('footer.php');