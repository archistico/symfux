<?php

namespace App\Controller;

use App\Form\ContactType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Turbo\Stream\TurboStreamResponse;

class AppController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function home(): Response
    {
        return $this->render('pages/home.html.twig');
    }

    #[Route('/about', name: 'app_about')]
    public function about(): Response
    {
        return $this->render('pages/about.html.twig');
    }

    #[Route('/contact', name: 'app_contact')]
    public function contact(Request $request): Response
    {
        $form = $this->createForm(ContactType::class);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            dump('Sending mail ...');

            if(TurboStreamResponse::STREAM_FORMAT === $request->getPreferredFormat()) {
                $name = $form['name']->getData();
                return new Response(
                    $this->renderView('streams/contact.html.twig', ['name' => $name]),
                    200,
                    [
                        'Content-Type' => 'text/vnd.turbo-stream.html'
                    ]
                );
            }

            $this->addFlash('success', 'Mail sent '.$form['name']->getData());
            return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
        }

        if($form->isSubmitted() && !$form->isValid()) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);

            return $this->render('pages/contact.html.twig', [
                'form' => $form->createView()
            ], $response);
        }

         return $this->renderForm('pages/contact.html.twig', [
            'form' => $form
        ]);
    }
}
