<?php
declare(strict_types=1);

namespace xDqZtop\simplelogin;

use JsonException;
use pocketmine\utils\Config;

class DataManager {

    private Config $players;
    private Config $status;

    public function __construct() {
        $main = Main::$instance;
        $this->players = new Config($main->getDataFolder() . "players.json", Config::JSON);
        $this->status = new Config($main->getDataFolder() . "status.json", Config::JSON);
    }

    public function isRegistered(string $name): bool {
        $name = strtolower($name);
        return $this->players->exists($name);
    }

    /**
     * @throws JsonException
     */
    public function registerPlayer(string $name, string $password): void {
        $name = strtolower($name);
        $password = strtolower($password);
        $this->players->set($name, password_hash($password, PASSWORD_BCRYPT));
        $this->players->save();
    }

    public function checkPassword(string $name, string $password): bool {
        $name = strtolower($name);
        $password = strtolower($password);
        $hash = $this->players->get($name);
        return password_verify($password, $hash);
    }

    /**
     * @throws JsonException
     */
    public function setLoggedIn(string $name, bool $status): void {
        $name = strtolower($name);
        $this->status->set($name, $status);
        $this->status->save();
    }

    public function isLoggedIn(string $name): bool {
        $name = strtolower($name);
        return (bool)$this->status->get($name, "false");
    }
}
