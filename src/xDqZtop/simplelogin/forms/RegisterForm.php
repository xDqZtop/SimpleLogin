<?php

declare(strict_types=1);

namespace xDqZtop\simplelogin\forms;

use pocketmine\world\sound\CauldronFillLavaSound;
use xDqZtop\simplelogin\Main;
use jojoe77777\FormAPI\{SimpleForm as SF,CustomForm as CF};
use pocketmine\player\Player;

class RegisterForm
{
    private static RegisterForm $instance;

    /**
     * @return RegisterForm
     */
    public static function getInstance(): RegisterForm
    {
        if (!isset(self::$instance)) {
            self::$instance = new RegisterForm();
        }
        return self::$instance;
    }

    /**
     * @param Player $player
     * @return void
     */
    public function registerFormA(Player $player): void
    {
        $registerForm = new SF(function (Player $player, ?int $data): void {
            if ($data === null) {
                $this->registerFormA($player);
                return;
            } elseif ($data === 0) {
                $this->registerFormB($player);
            }
        });
        $registerForm->setTitle("§l§aWelcome §e:)");
        $registerForm->setContent("§7Please register to secure your account");
        $registerForm->addButton("§l§aRegister");
        $player->sendForm($registerForm);
    }

    /**
     * @param Player $player
     * @return void
     */
    public function registerFormB(Player $player): void
    {
        $plugin = Main::getInstance();
        $c = $plugin->getConfigManager();
        $min = $c->getMin();
        $max = $c->getMax();
        $registerForm = new CF(function (Player $player, ?array $data) use ($plugin, $min, $max): void {
            if ($data === null) {
                $this->registerFormB($player);
                return;
            }
            $password = $data["password"];
            $confirm_password = $data["confirm-password"];

            if ($password !== $confirm_password) {
                $this->registerFormB($player);
                $player->broadcastSound(new CauldronFillLavaSound());
            } elseif (strlen($password) < $min) {
                $this->registerFormB($player);
                $player->broadcastSound(new CauldronFillLavaSound());
            } elseif (strlen($password) > $max) {
                $this->registerFormB($player);
                $player->broadcastSound(new CauldronFillLavaSound());
            }
            $r = $plugin->getRegisterManager();
            $r->register(strtolower($player->getName()), strtolower($password));
            $s = $plugin->getStateManager();
            $s->setTrue(strtolower($player->getName()));
            $player->getEffects()->clear();
        });
        $registerForm->setTitle("§l§aRegister Form");
        $registerForm->addLabel("§7Tip:\n §8Minimum length: §e$min\n §8Maximum length: §e$max");
        $registerForm->addInput("§7Password", "...", "", "password");
        $registerForm->addInput("§7Confirm Password", "...", "", "confirm-password");
        $player->sendForm($registerForm);
    }
}
