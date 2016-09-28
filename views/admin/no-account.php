<div class="alert alert-danger"><?php echo $lang->get('account_does_not_exist_on_server'); ?></div>
<p class="text-center"><a href="<?php echo $router->generate('admin-service-virtualmin-create', array('id' => $client->id, 'service_id' => $service->id)); ?>" class="btn btn-primary btn-large"><?php echo $lang->get('create_account'); ?></a></p>
