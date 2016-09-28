<?php

class VirtualminController extends AdminController
{
    public function manageHosting($client_id, $purchase_id)
    {
        $Client = Client::find($client_id);
        $ProductPurchase = ProductPurchase::find($purchase_id);
        $Hosting = $ProductPurchase->Hosting()->first();

        $Server = $Hosting->Server()->first();
        $this->loadApi($Server);

        $Account = $this->ServerHelper->library->getService($Hosting->domain);

        $this->view->set('client', $Client);
        $this->view->set('service', $ProductPurchase);

        if (is_object($Account)) {
            $this->view->set('account', $Account);
            $this->view->display('virtualmin::admin/manage-account.php');
        } else {
            // Account does not exist.
            $this->view->display('virtualmin::admin/no-account.php');
        }
    }

    public function createAccount($client_id, $purchase_id)
    {
        $Client = Client::find($client_id);
        $ProductPurchase = ProductPurchase::find($purchase_id);
        $Hosting = $ProductPurchase->Hosting()->first();

        $Server = $Hosting->Server()->first();
        $this->loadApi($Server);

        if ($this->ServerHelper->createService($ProductPurchase, $Hosting)) {
            App::get('session')->setFlash('success', $this->lang->get('account_created'));
        } else {
            App::get('session')->setFlash('error', $this->lang->get('error_creating_account'));
        }

        $this->redirect(
            'admin-client-service',
            array(
                'id' => $Client->id,
                'service_id' => $ProductPurchase->id
            )
        );
    }

    public function suspendAccount($client_id, $purchase_id)
    {
        $Client = Client::find($client_id);
        $ProductPurchase = ProductPurchase::find($purchase_id);
        $Hosting = $ProductPurchase->Hosting()->first();

        $Server = $Hosting->Server()->first();
        $this->loadApi($Server);

        if ($this->ServerHelper->suspendService($ProductPurchase, $Hosting)) {
            App::get('session')->setFlash('success', $this->lang->get('account_suspended'));
        } else {
            App::get('session')->setFlash('error', $this->lang->get('error_suspending_account'));
        }

        $this->redirect(
            'admin-client-service',
            array(
                'id' => $Client->id,
                'service_id' => $ProductPurchase->id
            )
        );
    }

    public function unsuspendAccount($client_id, $purchase_id)
    {
        $Client = Client::find($client_id);
        $ProductPurchase = ProductPurchase::find($purchase_id);
        $Hosting = $ProductPurchase->Hosting()->first();

        $Server = $Hosting->Server()->first();
        $this->loadApi($Server);

        if ($this->ServerHelper->unsuspendService($ProductPurchase, $Hosting)) {
            App::get('session')->setFlash('success', $this->lang->get('account_unsuspended'));
        } else {
            App::get('session')->setFlash('error', $this->lang->get('error_unsuspending_account'));
        }

        $this->redirect(
            'admin-client-service',
            array(
                'id' => $Client->id,
                'service_id' => $ProductPurchase->id
            )
        );
    }

    public function terminateAccount($client_id, $purchase_id)
    {
        $Client = Client::find($client_id);
        $ProductPurchase = ProductPurchase::find($purchase_id);
        $Hosting = $ProductPurchase->Hosting()->first();

        $Server = $Hosting->Server()->first();
        $this->loadApi($Server);

        if ($this->ServerHelper->terminateService($ProductPurchase, $Hosting)) {
            App::get('session')->setFlash('success', $this->lang->get('account_terminated'));
        } else {
            App::get('session')->setFlash('error', $this->lang->get('error_terminating_account'));
        }

        $this->redirect(
            'admin-client-service',
            array(
                'id' => $Client->id,
                'service_id' => $ProductPurchase->id
            )
        );
    }

    public function manageServer()
    {

    }

    /**
     * load the server helper library
     *
     * @param object $Server Server model
     */
    protected function loadApi($Server)
    {
        $this->ServerHelper = \App::factory('\App\Libraries\ServerHelper');
        $this->ServerHelper->initAddon(
            $Server->id
        );
    }
}
