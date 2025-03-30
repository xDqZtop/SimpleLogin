<?php
declare(strict_types=1);

namespace xDqZtop\simplelogin;

use JsonException;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as TF;
use jojoe77777\FormAPI\CustomForm;

class EventListener implements Listener {

    public function onJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();
        $dataManager = Main::$instance->getDataManager();

        $player->setInvisible();
        foreach(Main::$instance->getServer()->getOnlinePlayers() as $onlinePlayer) {
            $onlinePlayer->hidePlayer($player);
        }

        $event->setJoinMessage("");

        if ($dataManager->isRegistered($player->getName())) {
            $this->sendLoginForm($player);
        } else {
            $this->sendRegisterForm($player);
        }
    }

    public function onMove(PlayerMoveEvent $event): void {
        $player = $event->getPlayer();
        $dataManager = Main::$instance->getDataManager();

        if(!$dataManager->isLoggedIn($player->getName())) {
            $event->cancel();
        }
    }

    public function onChat(PlayerChatEvent $event): void {
        $player = $event->getPlayer();
        $dataManager = Main::$instance->getDataManager();

        if(!$dataManager->isLoggedIn($player->getName())) {
            $player->sendMessage(TF::RED."You must login to chat!");
            $event->cancel();
        }
    }

    public function onBlockBreak(BlockBreakEvent $event): void {
        $player = $event->getPlayer();
        $dataManager = Main::$instance->getDataManager();

        if(!$dataManager->isLoggedIn($player->getName())) {
            $player->sendMessage(TF::RED."You must login to break blocks!");
            $event->cancel();
        }
    }

    public function onBlockPlace(BlockPlaceEvent $event): void {
        $player = $event->getPlayer();
        $dataManager = Main::$instance->getDataManager();

        if(!$dataManager->isLoggedIn($player->getName())) {
            $player->sendMessage(TF::RED."You must login to place blocks!");
            $event->cancel();
        }
    }

    public function onEntityDamage(EntityDamageEvent $event): void {
        $entity = $event->getEntity();

        if($event instanceof EntityDamageByEntityEvent) {
            $damager = $event->getDamager();
            if($damager instanceof Player) {
                $dataManager = Main::$instance->getDataManager();

                if(!$dataManager->isLoggedIn($damager->getName())) {
                    $damager->sendMessage(TF::RED."You must login to attack!");
                    $event->cancel();
                }
            }
        }

        if($entity instanceof Player) {
            $dataManager = Main::$instance->getDataManager();

            if(!$dataManager->isLoggedIn($entity->getName())) {
                $event->cancel();
            }
        }
    }

    /**
     * @throws JsonException
     */
    public function onQuit(PlayerQuitEvent $event): void {
        $player = $event->getPlayer();
        $dataManager = Main::$instance->getDataManager();
        $dataManager->setLoggedIn($player->getName(), false);
    }

    private function sendLoginForm(Player $player): void {
        $form = new CustomForm(function(Player $player, ?array $data) {
            if ($data === null) {
                $player->kick("§cPlease login to play!");
                return;
            }

            $password = $data[1] ?? "";
            $dataManager = Main::$instance->getDataManager();

            if ($dataManager->checkPassword($player->getName(), $password)) {
                $dataManager->setLoggedIn($player->getName(), true);
                $player->setInvisible(false);
                foreach(Main::$instance->getServer()->getOnlinePlayers() as $onlinePlayer) {
                    $onlinePlayer->showPlayer($player);
                }
                $player->sendMessage("§aLogin successful!");

                $this->sendDelayedJoinMessage("§e" . $player->getName() . " joined the game");
            } else {
                $player->sendMessage("§cWrong password!");
                $this->sendLoginForm($player);
            }
        });

        $form->setTitle("Login");
        $form->addLabel("Enter your password:");
        $form->addInput("Password:");
        $player->sendForm($form);
    }

    private function sendRegisterForm(Player $player): void {
        $form = new CustomForm(function(Player $player, ?array $data) {
            if ($data === null) {
                $player->kick("§cPlease register to play!");
                return;
            }

            $password = $data[1] ?? "";
            $confirm = $data[2] ?? "";

            if ($password !== $confirm) {
                $player->sendMessage("§cPasswords don't match!");
                $this->sendRegisterForm($player);
                return;
            }

            $dataManager = Main::$instance->getDataManager();
            $dataManager->registerPlayer($player->getName(), $password);
            $dataManager->setLoggedIn($player->getName(), true);

            $player->setInvisible(false);
            foreach(Main::$instance->getServer()->getOnlinePlayers() as $onlinePlayer) {
                $onlinePlayer->showPlayer($player);
            }
            $player->sendMessage("§aRegistration successful!");

            $this->sendDelayedJoinMessage("§e" . $player->getName() . " joined the game");
        });

        $form->setTitle("Register");
        $form->addLabel("Choose a password:");
        $form->addInput("Password:");
        $form->addInput("Confirm Password:");
        $player->sendForm($form);
    }

    private function sendDelayedJoinMessage(string $message): void {
        foreach(Main::$instance->getServer()->getOnlinePlayers() as $onlinePlayer) {
            $onlinePlayer->sendMessage($message);
        }
        Main::$instance->getServer()->getLogger()->info($message);
    }
}
