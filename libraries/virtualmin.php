<?php
namespace Addon\Virtualmin\Libraries;

use Snscripts\Virtualmin\Virtualmin as VirtualminConnection;
use GuzzleHttp\Client;
use App\Libraries\Interfaces\Hosting\Shared as SharedHostingInterface;

class Virtualmin implements SharedHostingInterface
{
    public $server;
    public $server_group;
    public $server_module;
    public $server_data;

    public $conn;

    /**
     * once added - the main initServer method to kick start the connection
     *
     * @param object $server
     * @param object $server_group
     * @param object $server_module
     */
    public function initServer($server, $server_group, $server_module)
    {
        $this->server = $server;
        $this->server_group = $server_group;
        $this->server_module = $server_module;

        $this->server_data = \App::factory('\Whsuite\CustomFields\CustomFields')
            ->getGroup('serverdata_virtualmin', $this->server->id, false);

        $Security = \App::get('security');

        // get the host and add port if exists
        $host = $this->server->hostname;
        $port = $Security->decrypt(
            $this->server_data['fields']['virtualmin_server_port']['value']['value']
        );
        if (! empty($port)) {
            $host .= ':' . ltrim($port, ':');
        }

        $verifySsl = $Security->decrypt(
            $this->server_data['fields']['virtualmin_server_disable_verify']['value']['value']
        );

        // set the connection
        $this->setConnection(
            $host,
            $Security->decrypt(
                $this->server->username
            ),
            $Security->decrypt(
                $this->server->password
            ),
            (bool) $this->server->ssl_connection,
            (bool) ! $verifySsl
        );
    }

    /**
     * return array, currently only of hostname for manage server tab
     * to be amended soon
     *
     * @return array
     */
    public function serverDetails()
    {
        $OtherManager = new \Snscripts\Virtualmin\Other\Manager($this->conn);

        try {
            $ServerInfo = $OtherManager->GetInfo()->run();

            $server_details = array();
            $server_details['hostname'] = $ServerInfo->host['hostname'];

            return $server_details;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * test the connection to the server when adding.
     * if it fails to connect, don't add it!
     *
     * @param array $serverData array of form data
     * @return bool
     */
    public function testConnection($serverData)
    {
        $host = $serverData['Server']['hostname'];
        if (! empty($serverData['CustomFields']['virtualmin_server_port'])) {
            $host .= ':' . ltrim($serverData['CustomFields']['virtualmin_server_port'], ':');
        }

        $this->setConnection(
            $host,
            $serverData['Server']['username'],
            $serverData['Server']['password'],
            (bool) $serverData['Server']['ssl_connection'],
            (bool) ! $serverData['CustomFields']['virtualmin_server_disable_verify']
        );

        $OtherManager = new \Snscripts\Virtualmin\Other\Manager($this->conn);
        try {
            $ServerInfo = $OtherManager->GetInfo()->run();

            if (! empty($ServerInfo->host['hostname'])) {
                return true;
            }

            throw new \Exception('No data returned');
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * return any extra form fields needed for the virtualmin product
     *
     * @return string
     */
    public function productFields()
    {
        $fields = '';
        $forms = \App::factory('\Whsuite\Forms\Forms');

        $PlansManager = new \Snscripts\Virtualmin\Plans\Manager($this->conn);

        try {
            $plans = $PlansManager->ListPlans()->run();

            if ($plans->count() === 0) {
                throw new \Exception('No plans in Virtualmin');
            }
        } catch (\Exception $e) {
            $error = '';
            if (DEV_MODE) {
                $error .= ' Error Message: ' . $e->getMessage();
            }

            return '<div class="alert alert-danger">No remote Virtualmin Plans found.' . $error . '</div>';
        }

        $plansSelect = array();
        foreach ($plans as $Plan) {
            $plansSelect[$Plan->id] = $Plan->name . ' (Disk Space: ' . $Plan->disk_space
                . ' // Bandwidth: ' . $Plan->bandwidth . ')';
        }

        $fields .= $forms->select(
            'PackageMeta.virtualmin_plan',
            \App::get('translation')->get('package'),
            array('options' => $plansSelect)
        );

        return $fields;
    }

    public function createService($purchase, $hosting)
    {
        $product = $purchase->Product()->first();
        $product_data = $product->ProductData()->get();

        $service_fields = array();

        foreach ($product_data as $p_data) {
            $service_fields[$p_data->slug] = $p_data->value;
        }

        $security = \App::get('security');

        if ($product->included_ips != '0') {
            $ip = $product->included_ips = '1';
        } else {
            $ip = '0';
        }

        if ($hosting->username == '') {
            // No username was set - create one and update the record.
            $hosting->username = $this->generateUsername($hosting->domain);
            $hosting->save();
        }

        $HostingManager = new \Snscripts\Virtualmin\Hosting\Manager($this->conn);
        try {
            $Result = $HostingManager->CreateService()
                ->setPlan($service_fields['virtualmin_plan'])
                ->setLimitsFromPlan()
                ->setFeaturesFromPlan()
                ->setDomain($hosting->domain)
                ->setUser($hosting->username)
                ->setPass($security->decrypt($hosting->password))
                ->run();

            if ($Result->getStatus() !== true) {
                throw new \Exception($Result->getMessage());
            }
        } catch (\Exception $e) {
            return false;
        }

        $hostingData = array(
            'domain' => $hosting->domain,
            'nameservers' => $this->server->nameservers,
            'diskspace_limit' => '0',
            'diskspace_usage' => '0',
            'bandwidth_limit' => '0',
            'bandwidth_usage' => '0',
            'status' => '1',
            'username' => $hosting->username,
            'password' => $security->decrypt($hosting->password)
        );

        return $hostingData;
    }

    /**
     * suspend the service
     *
     * @param Object $ProductPurchase
     * @param Object $Hosting
     *
     * @return bool
     */
    public function suspendService($ProductPurchase, $Hosting)
    {
        $HostingManager = new \Snscripts\Virtualmin\Hosting\Manager($this->conn);
        try {
            $Result = $HostingManager->DisableService()
                ->setDomain($Hosting->domain)
                ->run();

            if ($Result->getStatus() !== true) {
                throw new \Exception($Result->getMessage());
            }
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * unsuspend the service
     *
     * @param Object $ProductPurchase
     * @param Object $Hosting
     *
     * @return bool
     */
    public function unsuspendService($ProductPurchase, $Hosting)
    {
        $HostingManager = new \Snscripts\Virtualmin\Hosting\Manager($this->conn);
        try {
            $Result = $HostingManager->EnableService()
                ->setDomain($Hosting->domain)
                ->run();

            if ($Result->getStatus() !== true) {
                throw new \Exception($Result->getMessage());
            }
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * terminate the service
     *
     * @param Object $ProductPurchase
     * @param Object $Hosting
     *
     * @return bool
     */
    public function terminateService($ProductPurchase, $Hosting)
    {
        $HostingManager = new \Snscripts\Virtualmin\Hosting\Manager($this->conn);
        try {
            $Result = $HostingManager->DeleteService()
                ->setDomain($Hosting->domain)
                ->run();

            if ($Result->getStatus() !== true) {
                throw new \Exception($Result->getMessage());
            }
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * given the domain name, get the service
     *
     * @param string $domain The domain to try and find
     *
     * @return Bool|\Snscripts\Virtualmin\Hosting\Domain
     */
    public function getService($domain)
    {
        $HostingManager = new \Snscripts\Virtualmin\Hosting\Manager($this->conn);
        try {
            $Domain = $HostingManager->ListServices()
                ->setDomain($domain)
                ->run();

            if (! is_a($Domain, '\Snscripts\Virtualmin\Hosting\Domain')) {
                throw new \Exception('No domain found');
            }
        } catch (\Exception $e) {
            return false;
        }

        return $Domain;
    }

    /**
     * setup the connection to virtualmin
     *
     * @param string $host
     * @param string $user
     * @param string $pass
     * @param bool $ssl
     * @param bool $verifySsl
     */
    protected function setConnection($host, $user, $pass, $ssl, $verifySsl)
    {
        // start setting up the virtualmin connection
        $this->conn = new VirtualminConnection(
            new Client
        );

        // set the connection
        $this->conn->setConnection(
            $host,
            $user,
            $pass,
            ($ssl ? VirtualminConnection::SECURE : VirtualminConnection::NOSECURE)
        );

        if (! $verifySsl) {
            $this->conn->setVerify(VirtualminConnection::NOVERIFY);
        }
    }

    private function generateUsername($domain)
    {
        // Strip special chars
        $username = preg_replace("/[^A-Za-z0-9 ]/", '', $domain);

        // Shorten to 6 chars
        $username = substr($username, 0, 6);

        // Check that the username does not contain reserved/unwanted phrases
        $reserved = array(
            'cpanel' => 'cp4n31',
            'whm' => 'w4m',
            'admin' => 'a6dm1n',
            'root' => 'r007',
            'administrator' => 'a6m1nistrator'
        );

        if (array_key_exists($username, $reserved)) {
            // Username contains one or more reserved words. To get around this, we can simply
            // use the replacement option provided by the reserved words list.
            $username = str_replace(array_flip($reserved), $reserved, $username);
        }

        // Add a random 2 digit number.
        $username .= mt_rand(10, 99);

        // Check the length to ensure we're at 8 chars.
        // If we're not, we'll add some random chars.
        if (strlen($username) < 8) {
            $chars = 'abcdefghijklmnopqrstuvwxyz';

            $length_required = 8 - strlen($username);

            $random_chars = substr(str_shuffle(str_repeat($chars), 10), 0, $length_required);

            $username = $username . $random_chars;
        }

        return $username;
    }
}
