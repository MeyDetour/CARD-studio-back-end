<?php

namespace App\Controller;
 
use App\Entity\Profile;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository; 
use Doctrine\ORM\EntityManagerInterface; 
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        ValidatorInterface $validator,
        SerializerInterface $serializer,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ): Response {
        // Récupérer les données JSON sous forme de tableau
        $data = json_decode($request->getContent(), true);

        // Désérialiser le JSON en objet User
        $user = $serializer->deserialize($request->getContent(), User::class, "json");

        // Vérifier si l'email est valide
//        if (!filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL)) {
//            return $this->json(['message' => "Adresse email invalide"], 400);
//        }

        // Vérifier si l'email est déjà utilisé
//        if ($userRepository->findOneBy(["email" => $user->getEmail()])) {
//            return $this->json(["message" => "Email déjà utilisé"], 409);
//        }

        // Vérifier que le username est fourni et valide (non vide, pas d'espaces inutiles)
        if (!$user->getUsername() || trim($user->getUsername()) === '' || preg_match('/\s/', $user->getUsername())) {
            return $this->json(['message' => "Please enter valid username."], 400);
        }
        if ($userRepository->findOneBy(["username" => $user->getUsername()])) {
            return $this->json(["message" => "Username already used."], 409);
        }
     

        // Valider l'objet User avec les contraintes Symfony
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            return $this->json(['message' => (string) $errors], 400);
        }

        // Hasher le mot de passe
        $plainPassword = $user->getPassword();
        $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
        $user->setDisplayErrors(true);
        $user->setLang("en");
        $entityManager->persist($user);
        $entityManager->flush();


  


        // Créer et associer le profil

        // Envoyer l'email de confirmation
//        $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
//            (new TemplatedEmail())
//                ->from(new Address('noreply@md-genos.com', 'Genos Center'))
//                ->to($user->getEmail())
//                ->subject('Please Confirm your Email')
//                ->htmlTemplate('email/confirmation.html.twig')
//                ->context([
//                    'username' => $user->getUsername()
//                ])
//        );



        return $this->json([
            "message" => "ok"
        ], 201);
    }


//
//    #[Route('/verify/email', name: 'app_verify_email')]
//    public function verifyUserEmail(Request $request, TranslatorInterface $translator, UserRepository $userRepository): Response
//    {
//        $id = $request->query->get('id');
//
//        if (null === $id) {
//            return $this->redirectToRoute('app_register');
//        }
//
//        $user = $userRepository->find($id);
//
//        if (null === $user) {
//            return $this->redirectToRoute('app_register');
//        }
//
//        // validate email confirmation link, sets User::isVerified=true and persists
//        try {
//            $this->emailVerifier->handleEmailConfirmation($request, $user);
//        } catch (VerifyEmailExceptionInterface $exception) {
//            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));
//
//            return $this->redirectToRoute('app_register');
//        }
//
//        // @TODO Change the redirect on success and handle or remove the flash message in your templates
//        $this->addFlash('success', 'Your email address has been verified.');
//
//        return $this->redirectToRoute('app_register');
//    }
}
