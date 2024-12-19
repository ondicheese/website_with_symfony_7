<?php

namespace App\Controller\Admin;

use App\Entity\Author;
use App\Form\AuthorType;
use Pagerfanta\Pagerfanta;
use App\Repository\AuthorRepository;
use Pagerfanta\Adapter\ArrayAdapter;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/author')]
class AuthorController extends AbstractController
{
    #[IsGranted('IS_AUTHENTICATED')]
    #[Route('', name: 'app_admin_author_index', methods: ['GET'])]
    public function index(Request $request, AuthorRepository $authorRepo): Response
    {
        $dates = [];

        if ($request->query->has('start')) {
            $dates['start'] = $request->query->get('start');
        }

        if ($request->query->has('end')) {
            $dates['end'] = $request->query->get('end');
        }

        $authors = Pagerfanta::createForCurrentPageWithMaxPerPage(
            new QueryAdapter($authorRepo->findByDateOfBirth($dates)),
            $request->query->get('page', 1),
            10
        );

        //$authors = $authorRepo->findAll();
        return $this->render('admin/author/index.html.twig', [
            'authors' => $authors,
        ]);
    }

    #[IsGranted('ROLE_ADD_BOOK')]
    #[Route('/new', name: 'app_admin_author_new', methods: ['GET', 'POST'])]
    #[Route('/{id}/edit', name:'app_admin_author_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'] )]
    public function newAuthor(?Author $author, Request $request, EntityManagerInterface $manager): Response
    {
        if ($author) {
            $this->denyAccessUnlessGranted('ROLE_EDIT_BOOK');
        }
        
        $author ??= new Author();
        $form = $this->createForm(AuthorType::class, $author);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($author);
            $manager->flush();

            return $this->redirectToRoute('app_admin_author_show', ['id' => $author->getId()]);
        }
        
        return $this->render('admin/author/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_author_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(?Author $author): Response
    {
        
        return $this->render('admin/author/show.html.twig', [
            'author' => $author
        ]);
    }
}
