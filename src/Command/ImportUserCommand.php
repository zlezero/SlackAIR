<?php
namespace App\Command;

use App\Entity\User;
use App\Entity\Statut;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Filesystem\Filesystem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class ImportUserCommand extends Command
{

    private $entityManager;
    private $passwordEncoder;
    private $mailer;
    private $validator;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder, MailerInterface $mailer, ValidatorInterface $validator)
    {
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->mailer = $mailer;
        $this->validator = $validator;

        parent::__construct();
    }

    /**
     * @author VATHONNE Thomas
     * Configure la commande qui permet d'importer un fichier CSV
     */
    protected function configure() {

        $this
            ->setName('run:import-csv')
            ->setDescription('Importe les utilisateurs depuis un fichier CSV')
            ->addArgument('csv', InputArgument::REQUIRED, 'Chemin du fichier CSV')
        ;

    }
 
    /**
     * @author VATHONNE Thomas
     * Execute la commande qui permet d'importer un fichier CSV et ajoute chaque utilisateur dans la base de données
     * Envoie un email de bienvenue à chaque nouvel utilisateur
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        
        $csv = $input->getArgument("csv");
        $filesystem = new Filesystem();

        if (!$filesystem->exists($csv)) {
            $output->writeln("Le fichier CSV passé en paramètre est inexistant");
            return Command::FAILURE;
        }

        $fichier = fopen($csv, "r");

        if ($fichier == FALSE) {
            $output->writeln("Une erreur est survenue lors de l'ouverture du fichier CSV passé en paramètre");
            return Command::FAILURE;
        }

        $now = new \DateTime();
        $output->writeln('<comment>Début : ' . $now->format('d-m-Y G:i:s') . ' ---</comment>');

        while(($ligne = fgetcsv($fichier, 0, ";")) !== FALSE) { //Pour chaque utilisateur dans le csv

            //On construit un nouvel utilisateur
            $user = new User();

            $plainPassword = base64_encode(random_bytes(10));

            $user->setEmail($ligne[0]);
            $user->setPrenom($ligne[1]);
            $user->setNom($ligne[2]);
            $user->setPseudo(explode("@", $ligne[0])[0]);
            $user->setPassword($this->passwordEncoder->encodePassword($user, $plainPassword));
            $user->setRoles(["ROLE_USER"]);
            $user->setFirstConnection(true);
            $user->setStatut($this->entityManager->getRepository(Statut::class)->findOneBy( array('id' => 2)));

            if (count($this->validator->validate($user)) > 0) { //Si l'utilisateur est déjà présent dans la base de données
                $output->writeln("L'utilisateur " . $ligne[1] . " " . $ligne[2] . " existe déjà");
            } else {

                $output->writeln("Ajout de " . $ligne[1] . " " . $ligne[2]);

                $this->entityManager->persist($user);
                $this->entityManager->flush();
                
                //On envoi l'email de bienvenue
                $this->sendEmail($ligne[0], $user, $plainPassword);

            }

        }

        fclose($fichier);
        
        $now = new \DateTime();
        $output->writeln('<comment>Fin : ' . $now->format('d-m-Y G:i:s') . ' ---</comment>');

        return Command::SUCCESS;

    }

    /**
     * @author VATHONNE Thomas
     * Envoie un email de bienvenue à un utilisateur
     */
    private function sendEmail(string $email, User $user, string $password) {
        
        $email = (new TemplatedEmail())
            ->from('noreply@slackair.com')
            ->to($email)
            ->priority(Email::PRIORITY_HIGH)
            ->subject('Inscription à SlackAIR - Identifiants')
            ->htmlTemplate('emails/inscription.html.twig')
            ->context([
                'user' => $user,
                'password' => $password,
                'email_address' => $email
            ])
            ;

        $this->mailer->send($email);

    }

}