<?php

declare(strict_types=1);

namespace xDqZtop\simplelogin;

use pocketmine\plugin\PluginBase;
use Throwable;

class Main extends PluginBase {

    public static ?Main $instance = null;
    public DataManager $dataManager;

    public function onLoad(): void {
        self::$instance = $this;
    }

    public function onEnable(): void {
        self::$instance = $this;
        $logger = $this->getLogger();

        try {
            $this->dataManager = new DataManager();
            $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
            $logger->notice("Plugin enabled successfully!");
        } catch (Throwable $e) {
            $logger->error("Enable error: " . $e->getMessage());
            $this->getServer()->getPluginManager()->disablePlugin($this);
        }
    }

    public function onDisable(): void {
        $logger = $this->getLogger();
        $logger->notice("Disabled!");
    }

    public function getDataManager(): DataManager {
        return $this->dataManager;
    }
}
