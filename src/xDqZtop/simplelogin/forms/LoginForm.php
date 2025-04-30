<?php

declare(strict_types=1);

namespace xDqZtop\simplelogin\forms;

use pocketmine\player\Player;
use pocketmine\world\sound\CauldronFillLavaSound;
use pocketmine\world\sound\XpCollectSound;
use pocketmine\world\sound\XpLevelUpSound;
use xDqZtop\simplelogin\Main;
use jojoe77777\FormAPI\{SimpleForm as SF,CustomForm as CF};

class LoginForm
{
    private static LoginForm $instance;

    /**
     * @return LoginForm
     */
    public static function getInstance(): LoginForm
    {
        if (!isset(self::$instance)) {
            self::$instance = new LoginForm();
        }
        return self::$instance;
    }

    /**
     * @param Player $player
     * @return void
     */
    public function loginFormA(Player $player): void
    {
        $loginForm = new SF(function (Player $player, ?int $data) {
            if ($data === null) {
                $this->loginFormA($player);
                return;
            }
            switch ($data) {
                case 0:
                    $this->loginFormB($player);
                    break;
                case 1:
                    $this->changePassword($player);
                    break;
            }
        });
        $loginForm->setTitle("§l§aWelcome §e:)");
        $loginForm->setContent("");
        $loginForm->addButton("§l§aLogin");
        $loginForm->addButton("§l§eChange Password");
        $player->sendForm($loginForm);
    }

    /**
     * @param Player $player
     * @return void
     */
    public function loginFormB(Player $player): void
    {
        $plugin = Main::getInstance();
        $loginForm = new CF(function (Player $player, ?array $data) use ($plugin) {
            $name = strtolower($player->getName());
            $s = $plugin->getStateManager();
            $l = $plugin->getLoginManager();
            $c = $plugin->getConfigManager();
            $p = $data["password"];
            if (empty($p)) {
                $this->loginFormA($player);
                $player->broadcastSound(new CauldronFillLavaSound());
            } elseif ($l->login($name, $p)) {
                $this->loginFormA($player);
                $player->broadcastSound(new CauldronFillLavaSound());
            } else {
                if ($c->getTransfer() == "on") {
                    $player->transfer($c->getServer());
                } elseif ($c->getTransfer() == "off") {
                    $s->setTrue($name);
                    $player->setInvisible(false);
                    $player->getEffects()->clear();
                }
            }
        });
        $loginForm->setTitle("§l§aLogin");
        $loginForm->addLabel("§7Enter your password");
        $loginForm->addInput("§7Password", "...", "", "password");
        $player->sendForm($loginForm);
    }

    /**
     * @param Player $player
     * @return void
     */
    private function changePassword(Player $player): void
    {
        $plugin = Main::getInstance();
        $c = $plugin->getConfigManager();
        $min = $c->getMin();
        $max = $c->getMax();
        $changePasswordForm = new CF(function (Player $player, ?array $data) use ($plugin, $min, $max, $c) {
            if ($data === null) {
                $this->changePassword($player);
                $player->broadcastSound(new CauldronFillLavaSound());
                return;
            }
            $name = strtolower($player->getName());
            $l = $plugin->getLoginManager();
            $s = $plugin->getStateManager();
            $oldPassword = $data["old-password"];
            $newPassword = $data["new-password"];
            $confirmNewPassword = $data["confirm-new-password"];
            if ($l->login($name, $oldPassword)) {
                $this->changePassword($player);
                $player->broadcastSound(new CauldronFillLavaSound());
            } elseif ($newPassword !== $confirmNewPassword) {
                $this->changePassword($player);
                $player->broadcastSound(new CauldronFillLavaSound());
            } elseif (strlen($newPassword) < $min) {
                $this->changePassword($player);
                $player->broadcastSound(new CauldronFillLavaSound());
            } elseif (strlen($newPassword) > $max) {
                $this->changePassword($player);
                $player->broadcastSound(new CauldronFillLavaSound());
            } else {
                $player->broadcastSound(new XpLevelUpSound());
                $l->changePassword($name, $newPassword);
                if ($c->getLogin() == "A") {
                    $this->loginFormA($player);
                } elseif ($c->getLogin() == "B") {
                    $this->loginFormB($player);
                }
            }
        });
        $changePasswordForm->setTitle("§l§eChange Password");
        $changePasswordForm->addLabel("§8Tip:\n§8- Minimum length: §e$min\n§8- Maximum length: §e$max");
        $changePasswordForm->addInput("§7Old Password", "...", "", "old-password");
        $changePasswordForm->addInput("§7New Password", "...", "", "new-password");
        $changePasswordForm->addInput("§7Confirm New Password", "...", "", "confirm-new-password");
        $player->sendForm($changePasswordForm);
    }
}
