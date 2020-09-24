### Akeneo API Bundle

Add this to composer.json
```
"repositories": [
    {
      "type": "vcs",
      "url": "git@github.com:asgoodasnu/akeneo-api-bundle.git"
    }
  ]
```

Next install with composer
```
composer require asgoodasnu/akeneo-api-bundle:*@dev
```
(maybe you get an exception, because the configuration is missing.)

Now add a configuration file in config/packages/akeneo_api.yaml
```
asgoodasnew_akeneo_api:
  url: '%env(AKENEO_API_URL)%'
  user: '%env(AKENEO_API_USER)%'
  password: '%env(AKENEO_API_PASSWORD)%'
  token: '%env(AKENEO_AUTH_TOKEN)%'
  cached: false
```
If cached is set to true, results will be cached for 60 minutes

Add environment variables or add the values to the .env file:
```
AKENEO_API_URL=http://...
AKENEO_API_USER=user
AKENEO_API_PASSWORD=password
AKENEO_AUTH_TOKEN=veryLongToken
```

Now you can type hint the AkeneoApi, see the example below:
```
<?php

namespace App\Command;

use Asgoodasnew\AkeneoApiBundle\AkeneoApi;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AkeneoCommand extends Command
{
    protected static $defaultName = 'app:akeneo';
    private $akeneoApi;

    public function __construct(AkeneoApi $akeneoApi)
    {
        parent::__construct(self::$defaultName);
        $this->akeneoApi = $akeneoApi;
    }

    protected function configure()
    {
        $this
            ->addArgument('id', InputArgument::REQUIRED, 'AN of the product')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $product = $this->akeneoApi->getProduct($input->getArgument('id'));

        $io->success('You did it: ' . $product['values']['agan_produktname'][0]['data']);

        return Command::SUCCESS;
    }
}
```