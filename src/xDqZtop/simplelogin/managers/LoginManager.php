<?php

declare(strict_types=1);

namespace xDqZtop\simplelogin\managers;

use pocketmine\utils\Config;
use xDqZtop\simplelogin\Main;

class LoginManager {

    protected Config $db;

    public function __construct()
    {
        $plugin = Main::getInstance();
        $this->db = $plugin->db;
    }

    /**
     * @param string $name
     * @param string $password
     * @return bool
     */
    public function login(string $name, string $password): bool {
        $hashedPassword = $this->getPassword($name);
        return password_verify($password, $hashedPassword);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getPassword(string $name): mixed {
        return $this->db->getNested("$name.password");
    }

    /**
     * @param string $name
     * @param string $password
     * @return bool
     */
    public function changePassword(string $name, string $password): bool {
        $this->db->setNested("$name.password", $password);
        return true;
    }
}
