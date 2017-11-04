<?php

namespace NightsWatch\Controller;

use NightsWatch\Entity\User;
use NightsWatch\Mvc\Controller\ActionController;
use Zend\Http\Response;

class ModController extends ActionController
{
    public function capeAction()
    {
        $user = $this->params()->fromQuery('user');
        $this->filterOutStyleCodes($user);
        $this->filterOutDotPng($user);

        /** @var User $user */
        $user = $this->getEntityManager()
            ->getRepository('NightsWatch\Entity\User')
            ->findOneBy(['username' => $user]);

        if (!$user || $user->rank < User::RANK_RECRUIT) {
            $this->getResponse()->setStatusCode(404);

            return $this->getResponse();
        }

        if ($user->deserter) {
            $this->getResponse()->setStatusCode(501);

            return $this->getResponse();
        }

        $response = new Response();
        $response->getHeaders()->addHeaders(['Content-Type' => 'image/png']);

        $cacheName = sha1(strtolower($user->username) . "-{$user->rank}-{$user->deserter}-{$user->order}");

        $fileLoc = "data/cache/cape-{$cacheName}.png";
        if (file_exists($fileLoc)) {
            $imageContents = file_get_contents($fileLoc);
        } else {
            $images = [];
            $base = imagecreatefromstring(file_get_contents('data/capes/base.png'));
            switch ($user->rank) {
                default:
                    $iconType = null;
                    break;
                case User::RANK_ADMIN:
                case User::RANK_COMMANDER:
                    $iconType = 'commander';
                    break;
                case User::RANK_GENERAL:
                    $iconType = 'general';
                    break;
                case User::RANK_LIEUTENANT:
                    $iconType = 'lieutenant';
                    break;
                case User::RANK_CAPTAIN:
                    $iconType = 'captain';
                    break;
                case User::RANK_CORPORAL:
                    $iconType = 'corporal';
                    break;
            }
            if ($user->rank == User::RANK_RECRUIT) {
                $backingType = 'recruit';
            }
            if ($user->rank == User::RANK_COMMANDER) {
                $backingType = 'commander';
            }
            $backing = imagecreatefromstring(file_get_contents("data/capes/backing-{$backingType}.png"));
            $raven = imagecreatefromstring(file_get_contents('data/capes/raven-logo.png'));
            $images = [$base, $backing, $raven];
            if ($user->rank == User::RANK_RECRUIT) {
                $recruit = imagecreatefromstring(file_get_contents('data/capes/recruit.png'));
                $images[] = $recruit;
            }
            if (!is_null($iconType)) {
                $icon = imagecreatefromstring(file_get_contents("data/capes/icon-{$iconType}.png"));
                $images[] = $icon;
            }

            foreach ($images as $image) {
                imagesavealpha($image, true);
            }

            $w = imagesx($base);
            $h = imagesy($base);

            array_shift($images); // remove $base from $images

            foreach ($images as $image) {
                imagecopyresampled($base, $image, 0, 0, 0, 0, $w, $h, $w, $h);
            }

            ob_start();
            imagepng($base);
            $imageContents = ob_get_contents();
            ob_end_clean();
            file_put_contents($fileLoc, $imageContents);
        }

        $response->setContent($imageContents);

        return $response;
    }

    /**
     * @param string $text
     */
    private function filterOutStyleCodes(&$text)
    {
        $text = preg_replace('/§[a-f0-9]/iu', '', $text);
    }

    private function filterOutDotPng(&$text)
    {
        if (strtolower(substr($text, -4)) == '.png') {
            $text = substr($text, 0, -4);
        }
    }
}
