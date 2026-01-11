<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;

class TimeService
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function getNow(): \DateTime
    {
        $session = $this->requestStack->getSession();

        // Verificar si el modo debug está activo y hay una fecha simulada
        if ($session->get('debug_mode') && $session->get('simulated_date')) {
            try {
                return new \DateTime($session->get('simulated_date'));
            } catch (\Exception $e) {
                // Si la fecha es inválida, volver a ahora
            }
        }

        return new \DateTime();
    }
}
