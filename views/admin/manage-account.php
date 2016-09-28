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
            <?php if (isset($account->disabled)): ?>
                <a href="<?php echo $router->generate('admin-service-virtualmin-unsuspend', array('id' => $client->id, 'service_id' => $service->id)); ?>" class="btn btn-warning">Unsuspend Account</a>
            <?php else: ?>
                <a href="<?php echo $router->generate('admin-service-virtualmin-suspend', array('id' => $client->id, 'service_id' => $service->id)); ?>" class="btn btn-warning">Suspend Account</a>
            <?php endif; ?>

            <?php if ($service->status != $service::TERMINATED): ?>
                <a href="<?php echo $router->generate('admin-service-virtualmin-terminate', array('id' => $client->id, 'service_id' => $service->id)); ?>" class="btn btn-danger" onclick="return confirm('<?php echo $lang->get('confirm_delete'); ?>')">Terminate Account</a>
            <?php endif; ?>
        </div>
    </div>
</div>
