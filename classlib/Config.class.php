<?php
require_once __DIR__ . "/DBSettings.class.php";
require_once __DIR__ . "/User.class.php";
require_once  __DIR__ . "/NestedPdo.class.php";
require_once __DIR__ . "/NestedPdoStatement.class.php";

use PHPAuth\Auth;
use PHPAuth\Config as AuthConfig;

class Config {
    public NestedPDO $db;
    public ?PDOException $db_error = null;

    public ?User $impersonator = null;

    public function __construct(
        
        public int $environment = APP\ENVIRONMENT::PRODUCTION,
        public ?string $base_asset_version = null,
        public ?string $asset_version = null,
        public ?string $default_layout_path = null,
        public array $default_layout_options = [],
        public int $week_ending = 0,
        public string $timezone = "Australia/Brisbane",
        public string $upload_directory = "/assets/uploads",
        public string $url = "https://timesheet.asterhomecare.com.au",
        public string $base_date = "2010-12-20",
        public bool $maintenance_mode = false,
        public ?array $maintenance_whitelist = null,
        public bool $enable_entity_cache = true,
        public bool $enable_analytics = true,
    ) {
        ini_set("error_log", __DIR__ . "/../../error_log-aster.txt");
        set_error_handler("custom_warning_handler", E_WARNING);
        set_exception_handler("custom_exception_handler");

        if (!is_null($db_settings)) {
            $this->initDB();
        }

        $this->setTimezone($timezone);
    }

    public function initDB() {
        try {
            $this->db = new NestedPDO(
                $this->db_settings->getConnectionString(),
                $this->db_settings->user,
                $this->db_settings->password,
                [
                    \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_SILENT,
                    \PDO::ATTR_PERSISTENT         => false,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                ]
            );
        } catch (PDOException $e) {
            $this->db_error = $e;
            error_log("[" . date("Y-m-d H:i:s") . "][" . __FILE__ . ":" . __LINE__ . "] " . $e->getMessage());
        }

        return $this;
    }

    public function getDBSettings() {
        return $this->db_settings;
    }

    public function setDBSettings(DBSettings $new_settings) {
        $this->db_settings = $new_settings;

        $this->initDB();

        return $this;
    }

    public function getEnvironment() {
        return $this->environment;
    }

    public function setEnvironment(int $environment) {
        $this->environment = $environment;

        return $this;
    }

    public function setDefaultLayoutPath(?string $path) {
        $this->default_layout_path = $path;

        return $this;
    }

    public function setDefaultLayoutOptions(array $options = []) {
        $this->default_layout_options = $options;

        return $this;
    }

    public function setAssetVersion(?string $version) {
        $this->asset_version = $version;

        return $this;
    }

    public function setUrl(string $url) {
        $this->url = $url;

        return $this;
    }

    public function setMaintenanceMode(bool $maintenance_mode) {
        $this->maintenance_mode = $maintenance_mode;

        return $this;
    }

    public function setMaintenanceWhitelist(?array $whitelist) {
        $this->maintenance_whitelist = $whitelist;

        return $this;
    }

    public function setEntityCache(bool $value) {
        $this->enable_entity_cache = $value;

        return $this;
    }

    public function setAnalytics(bool $value) {
        $this->enable_analytics = $value;

        return $this;
    }

    protected Auth $auth;

    public function getAuth(bool $force = false) {
        $this->initAuth($force);
        return $this->auth;
    }

    protected string $auth_type = "default";
    protected bool $auth_initialised = false;
    protected User $user;

    protected function initAuth(bool $force = false) {
        if ($this->auth_initialised && !$force) {
            return $this;
        }

        $this->user = new User;

        session_start();

        $this->auth = new Auth($this->db, new AuthConfig($this->db, "auth_config"));

        if ($this->auth->isLogged() && $this->auth->getCurrentUser() !== null) {

            $s = "SELECT * FROM `user` WHERE `email` = :email LIMIT 1";

            $q = $this->db->prepare($s);
            $q->bindValue(":email", $this->auth->getCurrentUser()["email"]);

            $c = $q->execute();
            if (!$c) {
                error_log("[" . date("Y-m-d H:i:s") . "][" . __FILE__ . ":" . __LINE__ . "] " . join(" - ", $q->errorInfo()));
            }

            if ($q->rowCount() === 1) {
                $this->user = User::FromArray($q->fetch());

                if (isset($_SESSION["impersonate_id"]) && $this->user->hasPermission(APP\PERMISSION::DEVELOPER)) {
                    $user = User::New($_SESSION["impersonate_id"]);
                    if ($user->isValid()) {
                        $this->impersonator = $this->user;
                        $this->user = $user;
                    }
                }
            }
        }

        if (!$this->user->isValid()) {
            $this->initTokenAuth();
        }

        session_write_close();

        $this->auth_initialised = true;

        return $this;
    }

    protected function initTokenAuth() {
        $headers = getallheaders();

        if (!isset($headers["Authorization"])) {
            return;
        }

        $auth_str = trim($headers["Authorization"]);
        if (strpos($auth_str, "Bearer") !== 0) {
            return;
        }

        /** @var Config $config */
        global $config;

        $token = trim(substr($auth_str, 6));

        $s = "SELECT * FROM `auth_token` WHERE `token` = :token AND `expires` > :expires LIMIT 1";

        $q = $config->db->prepare($s);
        $q->bindValue(":token", $token);
        $q->bindValue(":expires", date("Y-m-d H:i:s"));

        $c = $q->execute();
        if (!$c) {
            error_log("[" . date("Y-m-d H:i:s") . "][" . __FILE__ . ":" . __LINE__ . "] " . join(" - ", $q->errorInfo()));
        }

        if ($q->rowCount() === 0) {
            return;
        }

        $result     = $q->fetch();
        $user_id    = intval($result["user_id"]);
        $single_use = intval($result["single_use"]) === 1;

        if ($result["route"] !== null) {
            $current_route = "/" . trim(characters: "/", string: $_GET["route"]);
            if ($current_route !== $result["route"]) {
                return;
            }
        }

        $this->user = User::New($user_id);
        $this->auth_type = "token";

        if ($single_use) {
            $s = "DELETE FROM `auth_token` WHERE `token` = :token";

            $q = $config->db->prepare($s);
            $q->bindValue(":token", $token);

            $c = $q->execute();
            if (!$c) {
                error_log("[" . date("Y-m-d H:i:s") . "][" . __FILE__ . ":" . __LINE__ . "] " . join(" - ", $q->errorInfo()));
            }
        }

        return $this;
    }

    public function getAuthType(bool $force = false) {
        $this->initAuth($force);
        return $this->auth_type;
    }

    public function isLoggedIn(bool $force = false) {
        $this->initAuth($force);
        return $this->user->isValid() && $this->user->status === APP\STATUS::ACTIVE;
    }

    public function getUser(bool $force = false) {
        $this->initAuth($force);
        return $this->user;
    }

    public function setTimezone(string $timezone) {
        $this->timezone = $timezone;
        if (!date_default_timezone_set($timezone)) {
            echo "bad timezone";
            die;
        }
    }
}
