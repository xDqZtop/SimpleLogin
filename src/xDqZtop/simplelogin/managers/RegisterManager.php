<?php

declare(strict_types=1);

namespace xDqZtop\simplelogin\managers;

use JsonException;
use pocketmine\utils\Config;
use xDqZtop\simplelogin\Main;

class RegisterManager
{

    protected Config $db;

    public function __construct()
    {
        $plugin = Main::getInstance();
        $this->db = $plugin->db;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function check(string $name): bool {
        if ($this->db->getNested($name.".password") !== null) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @throws JsonException
     * @param string $name
     * @param mixed $password
     */
    public function register(string $name, mixed $password): void
    {
        $this->db->setNested("$name.password", $password);
        $this->db->save();
        Main::getInstance()->getStateManager()->setTrue($name);
    }
}
