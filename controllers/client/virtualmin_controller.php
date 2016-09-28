<?php

class VirtualminController extends ClientController
{
    public function manageHosting($id)
    {
        $ProductPurchase = ProductPurchase::find($id);

        if ($this->logged_in && $this->client->id === $ProductPurchase->client_id) {
            $Hosting = $ProductPurchase->Hosting()->first();
            $Server = $Hosting->Server()->first();

            $this->ServerHelper = \App::factory('\App\Libraries\ServerHelper');
            $this->ServerHelper->initAddon(
                $Server->id
            );

            $Account = $this->ServerHelper->library->getService($Hosting->domain);

            $this->view->set('service', $ProductPurchase);

            if (is_object($Account)) {
                $this->view->set('account', $Account);

                list($ip, $blurb) = explode(' ', $Account->ip_address, 2);
                $panelUrl = 'https://' . $ip . ':10000/virtualmin';
                $this->view->set('panelUrl', $panelUrl);

                $this->view->display('virtualmin::client/manage-account.php');
            } else {
                // Account does not exist.
                $this->view->display('virtualmin::client/no-account.php');
            }
        } else {
            $this->redirect('client-home');
        }
    }
}
