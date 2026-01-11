<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;

#[Route('/debug')]
class DebugController extends AbstractController
{
    #[Route('/toggle', name: 'debug_toggle')]
    public function toggle(Request $request): RedirectResponse
    {
        $session = $request->getSession();
        $currentMode = $session->get('debug_mode', false);
        $session->set('debug_mode', !$currentMode);

        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_home'));
    }

    #[Route('/set-date', name: 'debug_set_date', methods: ['POST'])]
    public function setDate(Request $request): RedirectResponse
    {
        $dateStr = $request->request->get('simulated_date');
        $session = $request->getSession();

        if ($dateStr) {
            $session->set('simulated_date', $dateStr);
        } else {
            $session->remove('simulated_date'); // Reset to now
        }

        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_home'));
    }
}
