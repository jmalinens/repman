<?php

declare(strict_types=1);

namespace Buddy\Repman\Command;

use Buddy\Repman\Message\CreateUser;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Messenger\MessageBusInterface;

class CreateAdminCommand extends Command
{
    private MessageBusInterface $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('repman:create:admin')
            ->setDescription('Create admin user')
            ->addArgument('email', InputArgument::REQUIRED, 'e-mail used to log in')
            ->addArgument('password', InputArgument::OPTIONAL, 'plain password, if you don\'t provide it, you\'ll be asked for it')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $email */
        $email = $input->getArgument('email');
        /** @var string $plainPassword */
        $plainPassword = $input->getArgument('password') ?? $this->getHelper('question')
            ->ask($input, $output, (new Question('User password:'))->setHidden(true));
        $id = Uuid::uuid4()->toString();

        $this->bus->dispatch(new CreateUser(
            $id,
            $email,
            $plainPassword,
            ['ROLE_ADMIN']
        ));

        $output->writeln(sprintf('Created user with id: %s', $id));

        return 0;
    }
}
