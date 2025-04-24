<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/profile')]
final class ProfileController extends AbstractController
{

    #[Route('/users', name: 'app_profile_users')]
    public function index(UserRepository $repositoryUser): Response
    {
        return $this->render('profile/index.html.twig', [
            'users' => $repositoryUser->findAll(),
        ]);
    }

    #[Route('/promote/{id}', name: 'app_profile_promote')]
    public function promote(EntityManagerInterface $manager, User $user): Response
    {
        if(!in_array('ROLE_ADMIN',$user->getRoles()))
        {
            $user->setRoles(['ROLE_ADMIN']);
            $manager->persist($user);
            $manager->flush();
        }
        return $this->redirectToRoute('app_profile_users');
    }

    #[Route('/demote/{id}', name: 'app_profile_demote')]
    public function demote(EntityManagerInterface $manager, User $user): Response
    {
        if(in_array('ROLE_ADMIN',$user->getRoles()))
        {
            $user->setRoles([]);
            $manager->persist($user);
            $manager->flush();
        }
        return $this->redirectToRoute('app_profile_users');
    }

}
