<?php

declare(strict_types=1);

namespace xDqZtop\simplelogin\managers;

use JsonException;
use pocketmine\utils\Config;
use xDqZtop\simplelogin\Main;

class StateManager
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
    public function getState(string $name): bool {
        return $this->db->getNested("$name.state");
    }

    /**
     * @throws JsonException
     * @param string $name
     */
    public function setTrue(string $name): void
    {
        $this->db->setNested("$name.state", true);
        $this->db->save();
    }

    /**
     * @throws JsonException
     * @param string $name
     */
    public function setFalse(string $name): void
    {
        $this->db->setNested("$name.state", false);
        $this->db->save();
    }
}
