<?php

namespace App\Controller\Admin;

use App\Entity\Book;
use App\Form\BookType;
use Pagerfanta\Pagerfanta;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/book')]
class BookController extends AbstractController
{
    #[Route('', name: 'app_admin_book_index', methods: ['GET'])]
    public function index(BookRepository $repo, Request $request): Response
    {
        $books = Pagerfanta::createForCurrentPageWithMaxPerPage(
            new QueryAdapter($repo->createQueryBuilder('b')),
            $request->query->get('page', 1),
            20
        );

        return $this->render('admin/book/index.html.twig', [
            'books' => $books
        ]);
    }
    
    #[IsGranted('ROLE_ADD_BOOK')]
    #[Route('/new', name: 'app_admin_book_new', methods: ['GET', 'POST'])]
    #[Route('/{id}/edit', name: 'app_admin_book_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function newBook(?Book $book, Request $request, EntityManagerInterface $manager): Response
    {
        if ($book) {
            $this->denyAccessUnlessGranted('ROLE_EDIT_BOOK');
        }
        
        $book ??= new Book();
        $form = $this->createForm(BookType::class, $book);
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($book);
            $manager->flush();

            return $this->redirectToRoute('app_admin_book_show', ['id' => $book->getId()]);
        }

        return $this->render('admin/book/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_book_show', methods: ['GET'])]
    public function showBook(?Book $book): Response
    {
        return $this->render('admin/book/show.html.twig', [
            'book' => $book,
        ]);
    }
}
