<?php

declare(strict_types=1);

namespace xDqZtop\simplelogin;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use xDqZtop\simplelogin\managers\ConfigManager;
use xDqZtop\simplelogin\managers\LoginManager;
use xDqZtop\simplelogin\managers\RegisterManager;
use xDqZtop\simplelogin\managers\StateManager;

class Main extends PluginBase
{

    private static ?Main $instance = null;

    /**
     * @return Main
     */
    public static function getInstance(): Main
    {
        return self::$instance;
    }

    /**
     * @return void
     */
    public function onEnable(): void
    {
        self::$instance = $this;
        $this->getLogger()->notice("Loading...");
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
        $this->registerConfigs();
        $this->registerManagers();
        $this->getLogger()->notice("Enabled!");
    }

    public Config $config;
    public Config $db;

    /**
     * @return void
     */
    private function registerConfigs(): void
    {
        $this->saveResource("config.yml");
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        $this->saveResource("db.json");
        $this->db = new Config($this->getDataFolder() . "db.json", Config::JSON);
    }

    private RegisterManager $registerManager;
    private StateManager $stateManager;
    private LoginManager $loginManager;
    private ConfigManager $configManager;

    /**
     * @return void
     */
    private function registerManagers(): void
    {
        $this->registerManager = new RegisterManager();
        $this->stateManager = new StateManager();
        $this->loginManager = new LoginManager();
        $this->configManager = new ConfigManager();
    }

    /**
     * @return RegisterManager
     */
    public function getRegisterManager(): RegisterManager
    {
        return $this->registerManager;
    }

    /**
     * @return StateManager
     */
    public function getStateManager(): StateManager
    {
        return $this->stateManager;
    }

    /**
     * @return LoginManager
     */
    public function getLoginManager(): LoginManager
    {
        return $this->loginManager;
    }

    /**
     * @return ConfigManager
     */
    public function getConfigManager(): ConfigManager
    {
        return $this->configManager;
    }

    /**
     * @return void
     */
    public function onDisable(): void
    {
        $logger = $this->getLogger();
        $logger->notice("Disabled!");
    }
}
