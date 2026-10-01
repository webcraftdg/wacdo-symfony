<?php

namespace App\Command;

use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:create-user',
    // this short description is shown when running "php bin/console list"
    description: 'Creates a new user.',
    // this is shown when running the command with the "--help" option
    help: 'This command allows you to create a user...',
    usages: ['app:create-user <email> <password> <firstname> <lastname> <role>']
)]
class CreateUserCommand extends Command
{

    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasher,
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
        ?string $name = null,
        ?callable $code = null)
    {
        return parent::__construct($name, $code);
    }

    public function __invoke(
    #[Argument('The email of the user.')] string $email,
    #[Argument('The password of the user.')] string $password,
    #[Argument('The firstname of the user.')] string $firstname,
    #[Argument('The lastname of the user.')] string $lastname,
    #[Argument('The role of the user.')] string $role,
    OutputInterface $output): int
    {
        $success = Command::SUCCESS;
        $output->writeln([
            'Créer un utilisateur',
            '============',
            '',
        ]);

        $output->writeln('Email : '.$email);
        $output->writeln('Password : '.$password);
        $output->writeln('Firstname : '.$firstname);
        $output->writeln('Lastname : '.$lastname);
        $output->writeln('Roles "ROLE_ADMIN", "ROLE_COLLAB", "ROLE_RESTAURANT_OWNER" : '.$role);
        $roles = [];
        $rolesAvailables = User::getAvailableRoles();
        $rolesAvailables['Administrateur'] = User::ROLE_ADMIN;

        if (in_array($role, array_values($rolesAvailables)) === false) {
            $output->writeln([
                'Les roles autorisés sont "ROLE_ADMIN", "ROLE_COLLAB", "ROLE_RESTAURANT_OWNER" '
            ]);
            $success = Command::FAILURE;
        } else {
            $roles[] = $role;
        }
        try {
            $user = new User();
            $user->setEmail($email)
                ->setFirstname($firstname)
                ->setLastname($lastname)
                ->setDateRecruitment(new DateTime('now'))
                ->setRoles($roles);
            $user->setPassword($this->userPasswordHasher->hashPassword($user, $password));
            $errors = $this->validator->validate($user);
            if ($errors->count() === 0) {
                $this->entityManager->persist($user);
                $this->entityManager->flush();
                $output->writeln([
                    'UTILISATEUR CREER'
                ]);
            } else {
                $output->writeln([
                    'Des erreurs ont été détectées ==> '
                ]);
                foreach($errors as $error) {
                    $output->writeln([
                        $error
                    ]);
                }
                $success = Command::FAILURE;
            }
        } catch (Exception $e ){
            $output->writeln([
                'Erreur de création',
                $e->getMessage()
            ]);
            $success = Command::FAILURE;
        }

        return $success;
    }
}
