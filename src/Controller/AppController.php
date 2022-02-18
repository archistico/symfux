<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

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
        $form = $this->createFormBuilder()
            ->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 2])
                ]
            ])
            ->add('message', TextareaType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 10])
                ]
            ])
            ->getForm()
        ;

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            dump('Sending mail ...');

            if(str_contains($request->headers->get('accept'), 'text/vnd.turbo-stream.html')) {
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
