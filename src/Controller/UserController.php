<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Form\UserFullype;
use App\Form\ChangePasswordType;
use Symfony\Component\Form\FormError;
use App\Form\EditProfileType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/user')]
class UserController extends AbstractController
{
    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    
    #[Route('/profile', name: 'app_user_profile', methods: ['GET'])]
    public function profile(): Response
    {

        return $this->render('user/profile.html.twig');
    }

    #[Route('/profilePlyaer', name: 'app_user_profile_player', methods: ['GET'])]
    public function profilePlayer(): Response
    {

        return $this->render('user/profilePlayer.html.twig');
    }


    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager,UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserFullype::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->get('image')->getData();
             // If a file was uploaded
             if ($file) {
                $filename = uniqid() . '.' . $file->guessExtension();

                // Move the file to the directory where brochures are stored
                $file->move(
                    'userImages',
                    $filename
                );
                 // Update the 'image' property to store the image file name
                // instead of its contents
                $user->setImage($filename);
            }


            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
       

            $entityManager->persist($user);
            $entityManager->flush();
            return $this->redirectToRoute('dashUsers', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, UserRepository $userRepo): Response
    {
        //$joueur = $joueurRepository->find($joueur->getId());
        $form = $this->createForm(EditProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('image')->getData()) {
                $file = $form->get('image')->getData();

                // If a file was uploaded
                if ($file) {
                    $filename = uniqid() . '.' . $file->guessExtension();

                    // Move the file to the directory where brochures are stored
                    $file->move(
                        'userImages',
                        $filename
                    );

                    // Update the 'image' property to store the image file name
                    // instead of its contents
                    $user->setImage($filename);
                }
            } else {
                // Keep the old profile picture
                $user->setImage($user->getImage());
            }
            $userRepo->save($user, true);

            return $this->redirectToRoute('app_user_profile', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/admin/edit', name: 'app_users_edit', methods: ['GET', 'POST'])]
    public function editt(Request $request, User $user, UserRepository $userRepo): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('newImage')->getData()) {
                $file = $form->get('newImage')->getData();
                if ($file) {
                    $filename = uniqid() . '.' . $file->guessExtension();
                    $file->move('userImages', $filename);
                    $user->setImage($filename);
                }
            } else {
                $user->setImage($user->getImage());
            }
            $userRepo->save($user, true);
            return $this->redirectToRoute('dashUsers', [], Response::HTTP_SEE_OTHER);
        }
    // Debugging statement
    dump('Rendering form');
        return $this->renderForm('user/editfromback.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }
    
   /* user deletes his own profile and gets redirected to registration page */ 
    #[Route('/{id}', name: 'user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user): Response
    {
        // Check if the request is a POST request
        
            // Get the entity manager
            $entityManager = $this->getDoctrine()->getManager();
            
            // Remove the user from the database
            $entityManager->remove($user);
            $entityManager->flush();

            // Redirect to a success page or perform any other action
            return $this->redirectToRoute('app_register'); // Replace 'dashboard' with your desired route
        

        // If the request is not a POST request, return a method not allowed response
       
    }

    #[Route('/{id}/delete', name: 'app_user_del',  methods: ['GET', 'POST'])]
    public function deleteUser(Request $request, User $user): Response
    {
        // Check if the request is a POST request
        
            // Get the entity manager
            $entityManager = $this->getDoctrine()->getManager();
            
            // Remove the user from the database
            $entityManager->remove($user);
            $entityManager->flush();

            // Redirect to a success page or perform any other action
            return $this->redirectToRoute('dashUsers'); // Replace 'dashboard' with your desired route
        

        // If the request is not a POST request, return a method not allowed response
       
    }

    #[Route('/changepassword/{id}', name: 'app_user_passchange', methods: ['GET', 'POST'])]
    public function changePassword(Request $request, UserPasswordHasherInterface $userPasswordHasher,  UserRepository $userRepo, user $user)
    {
        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $oldPassword = $form->get('oldPassword')->getData();

            if ($userPasswordHasher->isPasswordValid($user, $oldPassword)) {
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('newPassword')->getData()
                    )
                );
                $userRepo->save($user, true);
                $this->addFlash('success', 'Your password has been changed.');
                 // Redirect to a success page or perform any other action
            return $this->redirectToRoute('app_user_profile'); // Replace 'dashboard' with your desired route
               
            } else {
                $form->get('oldPassword')->addError(new FormError('invalid password , re-enter '));
            }
        }
        return $this->renderForm('user/passmodify.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

}
