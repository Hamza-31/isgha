<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\CityRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use App\Security\EmailVerifier;

class RegistrationController extends AbstractController
{
    /*
    private $verifyEmailHelper;
    private $mailer;
    public function __construct(VerifyEmailHelperInterface $helper, MailerInterface $mailer)
    {
        $this->verifyEmailHelper = $helper;
        $this->mailer = $mailer;
    }
*/
    private $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, CityRepository $cityRepository,): Response
    {
        $user = new User();
        $cities = $cityRepository->findAll();
        $form = $this->createForm(RegistrationFormType::class, $user, [
            'cities' => $cities
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $city = $form->get("city")->getData();
            $user->setCity($city->getName());
            $user->setActive(false);
            // encode the plain password
            $user->setPassword(
            $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $user->setRoles(['ROLE_USER']);
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success','Votre demande a été traité avec succès. Vous recevrez un e-mail, ouvrez-le et vérifiez votre compte.');
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('support@isgha.co', 'ISGHA'))
                    ->to($user->getEmail())
                    ->subject('Bienvenue chez nous !')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );

            // generate a signed url and email it to the user
            /*
            $signatureComponents = $this->verifyEmailHelper->generateSignature(
                'registration_confirmation_route',
                $user->getId(),
                $user->getEmail(),
                ['id' => $user->getId()]
            );

            $email = new TemplatedEmail();
            $email->from('send@example.com');
            $email->to($user->getEmail());
            $email->htmlTemplate('registration/confirmation_email.html.twig');
            $email->context(['signedUrl' => $signatureComponents->getSignedUrl()]);

            $this->mailer->send($email);
*/
            // generate and return a response for the browser

            return $this->redirectToRoute('home');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verify', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, UserRepository $userRepository): Response
    {
        /*
        $id = $request->get('id'); // retrieve the user id from the url

              // Verify the user id exists and is not null
              if (null === $id) {
                  return $this->redirectToRoute('home');
       }

       $user = $userRepository->find($id);

       // Ensure the user exists in persistence
       if (null === $user) {
                   return $this->redirectToRoute('app_home');
       }
*/
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        try {
            $this->emailVerifier->handleEmailConfirmation($request,$this->getUser());
        } catch (VerifyEmailExceptionInterface $e) {
            $this->addFlash('verify_email_error', $e->getReason());

            return $this->redirectToRoute('app_register');
        }

        // Mark your user as verified. e.g. switch a User::verified property to true

        $this->addFlash('success', 'Félicitations! Votre compte a été vérifié.');

        return $this->redirectToRoute('home');
    }
}
