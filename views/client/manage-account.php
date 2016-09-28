<div class="row">
    <div class="col-md-8">
        <h3 class="nomargin"><?php echo $account->name; ?></h3>
    </div>
    <div class="col-md-4">
        <b><?php echo $lang->get('package'); ?>: </b> <?php echo $account->plan; ?><br>
        <b><?php echo $lang->get('ip_address'); ?>: </b> <?php echo $account->ip_address; ?>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="well text-center">
            <a href="<?=$panelUrl?>" class="btn btn-primary" target="_blank">
                <?php echo $lang->get('access_control_panel'); ?>
            </a>
        </div>
    </div>
</div>
