<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Image;
use App\Entity\Post;
use App\Form\CommentType;
use App\Form\ImageType;
use App\Form\PostType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PostController extends AbstractController
{
    #[Route('/posts', name: 'app_post')]
    public function index(PostRepository $postRepository): Response
    {
        return $this->render('post/index.html.twig', [
            'posts' => $postRepository->findAll(),
        ]);
    }

    #[Route('/post/{id}', name: 'app_post_show', priority: -1)]
    public function show(Post $post): Response
    {

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        return $this->render('post/show.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
            'comment' => $comment,
        ]);

    }

    #[Route('/post/create', name: 'app_post_create')]
    public function create(EntityManagerInterface $manager, Request $request): Response
    {

        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $post->setAuthor($this->getUser());
            $manager->persist($post);
            $manager->flush();
            return $this->redirectToRoute('app_post_show', ['id' => $post->getId()]);
        }

        return $this->render('post/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/post/{id}/edit', name: 'app_post_edit', priority: -1)]
    public function edit(Post $post, EntityManagerInterface $manager, Request $request): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $manager->persist($post);
            $manager->flush();
            return $this->redirectToRoute('app_post_show', ['id' => $post->getId()]);
        }
        return $this->render('post/edit.html.twig', [
            'post' => $post,
            'formEditPost' => $form->createView(),
        ]);

    }

    #[Route('/post/{id}/delete', name: 'app_post_delete',)]
    public function delete(Post $post, EntityManagerInterface $manager,): Response
    {
        $manager->remove($post);
        $manager->flush();
        return $this->redirectToRoute('app_post');
    }

    #[Route('/post/addimage/{id}', name: 'app_post_addimage')]
    public function addImage(Post $post, Request $request, EntityManagerInterface $manager) : Response
    {
        if(!$this->getUser() || !$post)
        {
            return $this->redirectToRoute('app_login');
        }
        if($post->getAuthor() !== $this->getUser())
        {
            return $this->redirectToRoute('app_post_show', ['id' => $post->getId()]);
        }
        $image = new Image();
        $formImage = $this->createForm(ImageType::class, $image);
        $formImage->handleRequest($request);
        if($formImage->isSubmitted() && $formImage->isValid()){
            $image->setPost($post);
            $manager->persist($image);
            $manager->flush();
            return $this->redirectToRoute('app_post_addimage', ['id' => $post->getId()]);
        }


        return $this->render('post/image.html.twig', [
            'post' => $post,
            'formImage' => $formImage->createView(),
        ]);
    }

    #[Route('/post/removeImage/{id}', name: 'app_removeImage')]
    public function removeImage(Image $image, EntityManagerInterface $manager) : Response
    {
        if(!$this->getUser() || !$image)
        {
            return $this->redirectToRoute('app_login');
        }
        if($image->getPost()->getAuthor() !== $this->getUser())
        {
            return $this->redirectToRoute('app_post_show', ['id' => $image->getId()]);
        }
        $postId = $image->getPost()->getId();
        $manager->remove($image);
        $manager->flush();


        return $this->redirectToRoute('app_post_addimage', ['id' => $postId]);
    }

}
