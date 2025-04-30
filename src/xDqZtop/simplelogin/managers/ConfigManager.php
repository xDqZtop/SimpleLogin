<?php

declare(strict_types=1);

namespace xDqZtop\simplelogin\managers;

use pocketmine\utils\Config;
use xDqZtop\simplelogin\Main;

class ConfigManager {

    protected Config $config;

    public function __construct()
    {
        $plugin = Main::getInstance();
        $this->config = $plugin->config;
    }

    /**
     * @return string
     */
    public function getServer(): string {
        return $this->config->get("server");
    }

    /**
     * @return string
     */
    public function getRegister(): string {
        return $this->config->get("register");
    }

    /**
     * @return string
     */
    public function getLogin(): string {
        return $this->config->get("login");
    }

    /**
     * @return int
     */
    public function getMin(): int {
        return $this->config->get("min");
    }

    /**
     * @return int
     */
    public function getMax(): int {
        return $this->config->get("max");
    }

    /**
     * @return string
     */
    public function getTransfer(): string {
        return $this->config->get("transfer");
    }

    /**
     * @param string $error
     * @return string
     */
    public function getError(string $error): string {
        switch ($error) {
            case "place":
                $error = $this->config->get("place");
                break;
            case "break":
                $error = $this->config->get("break");
                break;
            case "chat":
                $error = $this->config->get("chat");
                break;
            case "damage":
                $error = $this->config->get("damage");
                break;
            case "drop":
                $error = $this->config->get("drop");
                break;
        }
        return $error;
    }
}
