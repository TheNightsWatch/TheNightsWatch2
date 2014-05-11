<?php

namespace NightsWatch;

use Zend\Db\TableGateway\Exception\RuntimeException;

class TakeTheBlack
{
    private $username;

    private $keepHat = false;

    private $file = "template.png";

    public function __construct($username)
    {
        $this->username = $username;
    }

    public static function load($username)
    {
        return new self($username);
    }

    public function keepHat($boolean)
    {
        $this->keepHat = !!$boolean;
        return $this;
    }

    public function get()
    {
        $template = $this->loadTemplate();
        $player = $this->loadSkin();
        $width = $this->keepHat ? 64 : 32;
        imagecopy($template, $player, 0, 0, 0, 0, $width, 16);
        imagedestroy($player);
        ob_start();
        imagepng($template);
        imagedestroy($template);
        $image = ob_get_contents();
        ob_end_clean();
        return $image;
    }

    public function template($file)
    {
        if (file_exists($this->getFileLocation($file . ".png"))) {
            $this->file = "{$file}.png";
        }
        return $this;
    }

    private function getFileLocation($file)
    {
        return dirname(__FILE__) . '/../../files/' . $file;
    }

    private function getTemplateFile()
    {
        $location = $this->getFileLocation($this->file);
        $contents = @file_get_contents($location);
        if ($contents === false) {
            throw new RuntimeException("No such template {$this->file}");
        }
        return $contents;
    }

    private function loadTemplate()
    {
        $image = imagecreatefromstring($this->getTemplateFile());
        imagesavealpha($image, true);
        imagealphablending($image, false);
        return $image;
    }

    private function loadSkin()
    {
        $image = imagecreatefromstring(
            file_get_contents("http://s3.amazonaws.com/MinecraftSkins/{$this->username}.png")
        );
        imagesavealpha($image, true);
        imagealphablending($image, false);
        return $image;
    }
}
