<?php
declare(strict_types=1);

namespace xDqZtop\simplelogin;

//use JsonException;
//use pocketmine\event\block\BlockBreakEvent;
//use pocketmine\event\block\BlockPlaceEvent;
//use pocketmine\event\entity\EntityDamageByEntityEvent;
//use pocketmine\event\entity\EntityDamageEvent;
//use pocketmine\event\player\PlayerChatEvent;
//use pocketmine\event\player\PlayerJoinEvent;
//use pocketmine\event\player\PlayerMoveEvent;
//use pocketmine\event\player\PlayerQuitEvent;
//use pocketmine\player\Player;
use JsonException;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\player\Player;
use xDqZtop\simplelogin\forms\LoginForm;
use xDqZtop\simplelogin\forms\RegisterForm;

class EventListener implements Listener
{

    /**
     * @param PlayerQuitEvent $event
     * @return void
     * @throws JsonException
     */
    public function onQuit(PlayerQuitEvent $event): void
    {
        $plugin = Main::getInstance();
        $name = strtolower($event->getPlayer()->getName());
        $s = $plugin->getStateManager();
        $s->setFalse($name);
    }

    /**
     * @param PlayerJoinEvent $event
     * @return void
     * @throws JsonException
     */
    public function onJoin(PlayerJoinEvent $event): void
    {
        $plugin = Main::getInstance();
        $player = $event->getPlayer();
        $name = strtolower($player->getName());
        $r = $plugin->getRegisterManager();
        $c = $plugin->getConfigManager();
        $s = $plugin->getStateManager();
        $s->setFalse($name);
        if ($r->check($name)) {
            if ($c->getRegister() === "A") {
                RegisterForm::getInstance()->registerFormA($player);
            } elseif ($c->getRegister() === "B") {
                RegisterForm::getInstance()->registerFormB($player);
            }
        } else {
            if ($c->getLogin() === "A") {
                LoginForm::getInstance()->loginFormA($player);
            } elseif ($c->getLogin() === "B") {
                LoginForm::getInstance()->loginFormB($player);
            }
        }
        if ($c->getTransfer() == "on") {
            return;
        } elseif ($c->getTransfer() == "off") {
            $player->setInvisible();
            $player->getEffects()->add(new EffectInstance(VanillaEffects::BLINDNESS(), 20 * 120, 2, false, false));
        }
    }

    /**
     * @param PlayerMoveEvent $event
     * @return void
     */
    public function onMove(PlayerMoveEvent $event): void
    {
        $player = $event->getPlayer();
        $name = strtolower($player->getName());
        $plugin = Main::getInstance();
        $s = $plugin->getStateManager();
        if ($s->getState($name) === false) {
            $event->cancel();
        }
    }

    /**
     * @param PlayerChatEvent $event
     * @return void
     */
    public function onChat(PlayerChatEvent $event): void
    {
        $player = $event->getPlayer();
        $name = strtolower($player->getName());
        $plugin = Main::getInstance();
        $c = $plugin->getConfigManager();
        $s = $plugin->getStateManager();
        if ($s->getState($name) === false) {
            $player->sendMessage($c->getError("chat"));
            $event->cancel();
        }
    }

    /**
     * @param BlockBreakEvent $event
     * @return void
     */
    public function onBlockBreak(BlockBreakEvent $event): void
    {
        $player = $event->getPlayer();
        $name = strtolower($player->getName());
        $plugin = Main::getInstance();
        $c = $plugin->getConfigManager();
        $s = $plugin->getStateManager();
        if ($s->getState($name) === false) {
            $player->sendMessage($c->getError("break"));
            $event->cancel();
        }
    }

    /**
     * @param BlockPlaceEvent $event
     * @return void
     */
    public function onBlockPlace(BlockPlaceEvent $event): void
    {
        $player = $event->getPlayer();
        $name = strtolower($player->getName());
        $plugin = Main::getInstance();
        $c = $plugin->getConfigManager();
        $s = $plugin->getStateManager();
        if ($s->getState($name) === false) {
            $player->sendMessage($c->getError("place"));
            $event->cancel();
        }
    }

    /**
     * @param EntityDamageEvent $event
     * @return void
     */
    public function onEntityDamage(EntityDamageEvent $event): void
    {
        $plugin = Main::getInstance();
        $c = $plugin->getConfigManager();
        $s = $plugin->getStateManager();
        if ($event instanceof EntityDamageByEntityEvent) {
            $damager = $event->getDamager();
            if ($damager instanceof Player) {
                $name = strtolower($damager->getName());
                if ($s->getState($name) === false) {
                    $damager->sendMessage($c->getError("damage"));
                    $event->cancel();
                }
            }
        }
    }

    /**
     * @param PlayerDropItemEvent $event
     * @return void
     */
    public function onDrop(PlayerDropItemEvent $event): void
    {
        $player = $event->getPlayer();
        $name = strtolower($player->getName());
        $plugin = Main::getInstance();
        $c = $plugin->getConfigManager();
        $s = $plugin->getStateManager();
        if ($s->getState($name) === false) {
            $player->sendMessage($c->getError("drop"));
            $event->cancel();
        }
    }
}
