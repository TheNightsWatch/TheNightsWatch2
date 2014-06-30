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

        if (!$user || $user->rank == User::RANK_CIVILIAN) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        if ($user->rank == User::RANK_RECRUIT || $user->deserter) {
            $this->getResponse()->setStatusCode(501);
            return;
        }

        $response = new Response();
        $response->getHeaders()->addHeaders(['Content-Type' => 'image/png']);
        $response->setContent(file_get_contents('data/capes/base.png'));
        return $response;
    }

    /**
     * @param string $text
     */
    private function filterOutStyleCodes(&$text)
    {
        $text = preg_replace("/§[a-f0-9]/iu", '', $text);
    }

    private function filterOutDotPng(&$text)
    {
        if (strtolower(substr($text, -4)) == '.png') {
            $text = substr($text, 0, -4);
        }
    }
}
