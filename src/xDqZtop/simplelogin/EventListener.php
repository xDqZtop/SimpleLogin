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
            $player->sendMessage($dataManager->getPlayerChatError());
            $event->cancel();
        }
    }

    public function onBlockBreak(BlockBreakEvent $event): void {
        $player = $event->getPlayer();
        $dataManager = Main::$instance->getDataManager();

        if(!$dataManager->isLoggedIn($player->getName())) {
            $player->sendMessage($dataManager->getPlayerBreakBlockError());
            $event->cancel();
        }
    }

    public function onBlockPlace(BlockPlaceEvent $event): void {
        $player = $event->getPlayer();
        $dataManager = Main::$instance->getDataManager();

        if(!$dataManager->isLoggedIn($player->getName())) {
            $player->sendMessage($dataManager->getPlayerPlaceBlockError());
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
                    $damager->sendMessage($dataManager->getPlayerHitError());
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
        $dataManager = Main::$instance->getDataManager();

        $form = new CustomForm(function(Player $player, ?array $data) use ($dataManager) {
            if ($data === null) {
                $player->kick($dataManager->getLoginKick());
                return;
            }

            $password = $data[1] ?? "";

            if ($dataManager->checkPassword($player->getName(), $password)) {
                $dataManager->setLoggedIn($player->getName(), true);
                $player->setInvisible(false);
                foreach(Main::$instance->getServer()->getOnlinePlayers() as $onlinePlayer) {
                    $onlinePlayer->showPlayer($player);
                }
                $player->sendMessage($dataManager->getLoginSuccessful());

                $this->sendDelayedJoinMessage("§e" . $player->getName() . " joined the game");
            } else {
                $player->sendMessage($dataManager->getLoginWrong());
                $this->sendLoginForm($player);
            }
        });

        $form->setTitle($dataManager->getLoginTitle());
        $form->addLabel($dataManager->getLoginLabel());
        $form->addInput($dataManager->getLoginInput());
        $player->sendForm($form);
    }

    private function sendRegisterForm(Player $player): void {
        $dataManager = Main::$instance->getDataManager();
        $form = new CustomForm(function(Player $player, ?array $data) use ($dataManager) {
            if ($data === null) {
                $player->kick($dataManager->getRegisterKick());
                return;
            }

            $password = $data[1] ?? "";
            $confirm = $data[2] ?? "";

            if ($password !== $confirm) {
                $player->sendMessage($dataManager->getRegisterDontMatch());
                $this->sendRegisterForm($player);
                return;
            }

            $dataManager->registerPlayer($player->getName(), $password);
            $dataManager->setLoggedIn($player->getName(), true);

            $player->setInvisible(false);
            foreach(Main::$instance->getServer()->getOnlinePlayers() as $onlinePlayer) {
                $onlinePlayer->showPlayer($player);
            }
            $player->sendMessage($dataManager->getRegisterSuccessful());

            $this->sendDelayedJoinMessage("§e" . $player->getName() . " joined the game");
        });

        $form->setTitle($dataManager->getRegisterTitle());
        $form->addLabel($dataManager->getRegisterLabel());
        $form->addInput($dataManager->getRegisterInput1());
        $form->addInput($dataManager->getRegisterInput2());
        $player->sendForm($form);
    }

    private function sendDelayedJoinMessage(string $message): void {
        foreach(Main::$instance->getServer()->getOnlinePlayers() as $onlinePlayer) {
            $onlinePlayer->sendMessage($message);
        }
        Main::$instance->getServer()->getLogger()->info($message);
    }
}
