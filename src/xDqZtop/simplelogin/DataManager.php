<?php
declare(strict_types=1);

namespace xDqZtop\simplelogin;

use JsonException;
use pocketmine\utils\Config;

class DataManager {

    private Config $players;
    private Config $status;
    private Config $config;

    public function __construct() {
        $main = Main::$instance;
        $main->saveResource("config.json");
        $this->config = new Config($main->getDataFolder() . "config.json", Config::JSON);
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

    public function getPlayerChatError(): string {
        return $this->config->get("player-chat-error");
    }

    public function getPlayerBreakBlockError(): string {
        return $this->config->get("player-kandan-block-error");
    }

    public function getPlayerPlaceBlockError(): string{
        return $this->config->get("player-gozashtan-block-error");
    }

    public function getPlayerHitError(): string{
        return $this->config->get("player-zadan-error");
    }

    public function getLoginKick(): string {
        return $this->config->get("login-biroonandakhtan");
    }

    public function getLoginSuccessful(): string {
        return $this->config->get("login-taid");
    }

    public function getLoginWrong(): string {
        return $this->config->get("login-ramz-ghalat");
    }

    public function getLoginTitle(): string {
        return $this->config->get("login-title");
    }

    public function getLoginLabel(): string {
        return $this->config->get("login-label");
    }

    public function getLoginInput(): string {
        return $this->config->get("login-input");
    }

    public function getRegisterKick(): string {
        return $this->config->get("register-biroonandakhtan");
    }

    public function getRegisterSuccessful(): string {
        return $this->config->get("register-taid");
    }

    public function getRegisterDontMatch(): string {
        return $this->config->get("register-mach-nist-error");
    }

    public function getRegisterTitle(): string {
       return $this->config->get("register-title");
    }

    public function getRegisterLabel(): string {
        return $this->config->get("register-label");
    }

    public function getRegisterInput1(): string {
        return $this->config->get("register-input1");
    }

    public function getRegisterInput2(): string {
        return $this->config->get("register-input2");
    }
}
