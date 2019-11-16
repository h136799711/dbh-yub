<?php


namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EmptyController extends AbstractController
{
    /**
     *
     * @Route("/", name="index_empty_entry", methods={"POST","GET"})
     */
    public function index() {
        return new Response('DENY');
    }
}
